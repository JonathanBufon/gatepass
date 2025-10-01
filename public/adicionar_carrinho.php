<?php
// public/adicionar_carrinho.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Produto;

$mensagem = '';
$tipoMensagem = '';
$idProduto = null;
$quantidade = null;
$redirectUrl = 'listar_produtos_publico.php'; // Redireciona para a vitrine por padrão

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduto = $_POST['id_produto'] ?? null;
    $quantidade = $_POST['quantidade'] ?? 1; // Padrão 1 se não especificado

    // Validação básica do input
    if (!filter_var($idProduto, FILTER_VALIDATE_INT) || (int)$idProduto <= 0) {
        $mensagem = 'Produto inválido.';
        $tipoMensagem = 'erro';
    } elseif (!filter_var($quantidade, FILTER_VALIDATE_INT) || (int)$quantidade <= 0) {
        $mensagem = 'Quantidade inválida.';
        $tipoMensagem = 'erro';
    } else {
        $idProduto = (int)$idProduto;
        $quantidade = (int)$quantidade;

        $produto = Produto::buscarPorId($idProduto);

        if (!$produto) {
            $mensagem = 'Produto não encontrado.';
            $tipoMensagem = 'erro';
        } elseif ($quantidade > $produto->obterQuantidadeDisponivel()) {
            $mensagem = 'Não há estoque suficiente para adicionar ao carrinho.';
            $tipoMensagem = 'erro';
        } else {
            // Produto e quantidade válidos, adiciona/atualiza no carrinho

            // Inicializa o carrinho na sessão se não existir
            if (!isset($_SESSION['carrinho'])) {
                $_SESSION['carrinho'] = [];
            }

            // Adiciona ou atualiza a quantidade do produto no carrinho
            // Se o produto já está no carrinho, adiciona a quantidade
            if (isset($_SESSION['carrinho'][$idProduto])) {
                $_SESSION['carrinho'][$idProduto] += $quantidade;
            } else {
                $_SESSION['carrinho'][$idProduto] = $quantidade;
            }

            $mensagem = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" adicionado ao carrinho!';
            $tipoMensagem = 'sucesso';
            $redirectUrl = 'ver_carrinho.php'; // Após adicionar, redireciona para o carrinho
        }
    }
} else {
    $mensagem = 'Acesso inválido para adicionar ao carrinho.';
    $tipoMensagem = 'erro';
}

// Redireciona com mensagem
$_SESSION['mensagem_publica'] = $mensagem;
$_SESSION['tipo_mensagem_publica'] = $tipoMensagem;
header('Location: ' . $redirectUrl);
exit();