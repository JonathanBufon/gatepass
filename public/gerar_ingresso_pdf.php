<?php
// public/gerar_ingresso_pdf.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use GatePass\Models\Compra;
use GatePass\Models\Produto;
use GatePass\Models\Cliente;

if (!isset($_SESSION['id_cliente_logado'])) {
    $_SESSION['mensagem_publica'] = 'Você precisa estar logado como cliente para gerar ingressos.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: login_cliente.php');
    exit();
}

$idClienteLogado = $_SESSION['id_cliente_logado'];
$idCompra = $_GET['id_compra'] ?? null;

if (!filter_var($idCompra, FILTER_VALIDATE_INT)) {
    $_SESSION['mensagem_publica'] = 'ID da compra inválido para gerar ingresso.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: minhas_compras.php');
    exit();
}

$idCompra = (int)$idCompra;

$compra = Compra::buscarPorId($idCompra);

if (!$compra) {
    $_SESSION['mensagem_publica'] = 'Compra não encontrada.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: minhas_compras.php');
    exit();
}

if ($compra->obterIdCliente() !== $idClienteLogado) {
    $_SESSION['mensagem_publica'] = 'Você não tem permissão para gerar este ingresso.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: minhas_compras.php');
    exit();
}

$produto = Produto::buscarPorId($compra->obterIdProduto());
$cliente = Cliente::buscarPorId($compra->obterIdCliente());

if (!$produto || !$cliente) {
    $_SESSION['mensagem_publica'] = 'Dados do produto ou cliente incompletos para gerar ingresso.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: minhas_compras.php');
    exit();
}

// --- Monta o Conteúdo HTML do Ingresso ---
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ingresso - ' . htmlspecialchars($produto->obterNome()) . '</title>
    <style>
        body { 
            font-family: sans-serif; 
            margin: 0 !important; /* Garante margem zero no corpo */
            padding: 0 !important; /* Garante padding zero no corpo */
        }
        .ingresso-container {
            width: 100%;
            max-width: 780px; /* Largura máxima para caber na A4 */
            margin: 0 auto !important; /* Margem externa reduzida ao mínimo absoluto */
            border: 2px solid #333;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
            page-break-inside: avoid; /* CRÍTICO: Tenta manter o contêiner em uma única página */
        }
        .ingresso-logo-pdf {
            text-align: center;
            padding: 8px 0; /* Padding reduzido */
            border-bottom: 1px solid #eee;
            margin-bottom: 8px; /* Margem reduzida */
        }
        .ingresso-logo-pdf img {
            height: 45px; /* Altura da logo ligeiramente reduzida */
            width: auto;
        }
        .ingresso-header-bg {
            width: 100%;
            height: 130px; /* Altura do banner mais reduzida */
            background-image: url("' . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/' . $produto->obterUrlFotoFundo()) . '");
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8em; /* Fonte do banner reduzida */
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.7);
            text-align: center;
        }
        .ingresso-header-bg.sem-bg { background-color: #6c757d; }
        .ingresso-content {
            padding: 10px; /* Padding interno ainda mais reduzido */
            display: flex;
            flex-wrap: wrap;
            gap: 8px; /* Espaçamento reduzido */
        }
        .ingresso-info-left, .ingresso-info-right {
            flex: 1;
            min-width: 200px; 
        }
        .ingresso-info-right { text-align: right; }
        .ingresso-info-right .qr-code {
            width: 80px; /* Tamanho do QR Code ainda mais reduzido */
            height: 80px;
            background-color: #eee;
            border: 1px solid #ccc;
            margin-left: auto;
            margin-bottom: 5px; /* Margem reduzida */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em; /* Fonte reduzida */
            color: #555;
        }
        .ingresso-info-right .foto-perfil {
            width: 80px; /* Tamanho da foto de perfil ainda mais reduzido */
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: auto;
            margin-bottom: 5px; /* Margem reduzida */
            border: 2px solid #007bff;
        }
        .ingresso-info-right .foto-perfil.sem-foto {
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            color: #555;
        }

        .ingresso-titulo {
            font-size: 1.5em; /* Tamanho do título principal reduzido */
            margin-top: 10px; /* Adicionado um pouco de margem acima, para não ficar colado */
            margin-bottom: 10px;
            text-align: center;
            width: 100%;
        }
        .ingresso-item {
            margin-bottom: 5px; /* Margem inferior dos itens ainda mais reduzida */
            border-bottom: 1px dashed #ccc;
            padding-bottom: 3px; /* Padding inferior dos itens ainda mais reduzido */
        }
        .ingresso-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .ingresso-item strong {
            font-size: 0.75em; /* Fonte reduzida */
        }
        .ingresso-item span {
            font-size: 0.95em; /* Fonte reduzida */
        }
        .ingresso-rodape {
            text-align: center;
            margin-top: 10px; /* Margem superior reduzida */
            padding-top: 8px; /* Padding superior reduzido */
            border-top: 1px solid #eee;
            font-size: 0.55em; /* Fonte bem reduzida */
            color: #888;
        }
    </style>
</head>
<body>
    <div class="ingresso-container">
        <div class="ingresso-logo-pdf">
            <img src="' . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/images/gate-pass-logo.png') . '" alt="Logo GatePass">
        </div>
        <div class="ingresso-header-bg ' . (empty($produto->obterUrlFotoFundo()) ? 'sem-bg' : '') . '" ' . (empty($produto->obterUrlFotoFundo()) ? '' : 'style="background-image: url(\'' . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/' . $produto->obterUrlFotoFundo()) . '\');"') . '>
            ' . htmlspecialchars($produto->obterNome()) . '
        </div>
        <h1 class="ingresso-titulo">Ingresso</h1>
        <div class="ingresso-content">
            <div class="ingresso-info-left">
                <div class="ingresso-item">
                    <strong>Comprador:</strong>
                    <span>' . htmlspecialchars($cliente->obterNome()) . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>Email:</strong>
                    <span>' . htmlspecialchars($cliente->obterEmail()) . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>CPF:</strong>
                    <span>' . htmlspecialchars($cliente->obterCpf() ?? 'Não informado') . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>Produto/Evento:</strong>
                    <span>' . htmlspecialchars($produto->obterNome()) . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>Descrição:</strong>
                    <span>' . htmlspecialchars($produto->obterDescricao() ?? 'N/A') . '</span>
                </div>
            </div>
            <div class="ingresso-info-right">
                ' . (empty($produto->obterUrlFotoPerfil()) ? '<div class="foto-perfil sem-foto">Sem Foto</div>' : '<img class="foto-perfil" src="' . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/public/' . $produto->obterUrlFotoPerfil()) . '" alt="Foto do Produto">') . '
                <div class="ingresso-item">
                    <strong>Data da Compra:</strong>
                    <span>' . htmlspecialchars($compra->obterDataCompra()) . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>Quantidade:</strong>
                    <span>' . htmlspecialchars($compra->obterQuantidadeComprada()) . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>Valor Total:</strong>
                    <span>R$ ' . number_format($compra->obterValorTotal(), 2, ',', '.') . '</span>
                </div>
                <div class="ingresso-item">
                    <strong>ID da Compra:</strong>
                    <span>' . htmlspecialchars($compra->obterIdCompra()) . '</span>
                </div>
                <div class="qr-code">QR Code (simulado)</div>
            </div>
        </div>
        <div class="ingresso-rodape">
            Apresente este ingresso na entrada do evento. <br>
            GatePass - Seu acesso fácil à diversão.
        </div>
    </div>
</body>
</html>
';

// --- Geração do PDF ---
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("ingresso_" . $compra->obterIdCompra() . ".pdf", array("Attachment" => false));
exit();