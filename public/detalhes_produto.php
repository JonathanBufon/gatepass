<?php
// public/detalhes_produto.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Produto;
use GatePass\Models\Cliente;

$produto = null;
$mensagem = '';
$tipoMensagem = '';
$idProduto = null;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $idProduto = (int)$_GET['id'];
    $produto = Produto::buscarPorId($idProduto);

    if (!$produto) {
        $_SESSION['mensagem_publica'] = 'Produto não encontrado.';
        $_SESSION['tipo_mensagem_publica'] = 'erro';
        header('Location: listar_produtos_publico.php');
        exit();
    }
} else {
    $_SESSION['mensagem_publica'] = 'ID do produto inválido.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: listar_produtos_publico.php');
    exit();
}

// --- Lógica da Reserva de 2 Minutos ---
$quantidadeDisponivel = $produto->obterQuantidadeDisponivel();
$quantidadeReservada = $produto->obterQuantidadeReservada();
$dataReserva = $produto->obterDataReserva();
$reservadoPorClienteId = $produto->obterReservadoPorClienteId(); 

$tempoReservaEmSegundos = 120;
$agora = time();

$isClienteLogado = isset($_SESSION['id_cliente_logado']);
$idClienteLogado = $isClienteLogado ? $_SESSION['id_cliente_logado'] : null;

if ($quantidadeReservada > 0 && $dataReserva !== null && $reservadoPorClienteId !== null) {
    $timestampReserva = strtotime($dataReserva);

    if (($agora - $timestampReserva) > $tempoReservaEmSegundos) {
        $produto->definirQuantidadeDisponivel($quantidadeDisponivel + $quantidadeReservada);
        $produto->definirQuantidadeReservada(0);
        $produto->definirDataReserva(null);
        $produto->definirReservadoPorClienteId(null);
        $produto->salvar();

        $mensagem = 'A reserva anterior expirou e o item foi liberado. Ele está disponível novamente!';
        $tipoMensagem = 'sucesso';

        $quantidadeDisponivel = $produto->obterQuantidadeDisponivel();
        $quantidadeReservada = $produto->obterQuantidadeReservada();
        $dataReserva = null;
        $reservadoPorClienteId = null;

    } else {
        if ($isClienteLogado && $reservadoPorClienteId === $idClienteLogado) {
            $tempoRestante = $tempoReservaEmSegundos - ($agora - $timestampReserva);
            $mensagem = 'Última unidade! Reservado para você por ' . max(0, $tempoRestante) . ' segundos.';
            $tipoMensagem = 'sucesso';
        } else {
            $mensagem = 'Última unidade! Temporariamente reservada por outro cliente.';
            $tipoMensagem = 'erro';
            $quantidadeDisponivel = 0; 
        }
    }
}

if ($quantidadeDisponivel === 1 && $quantidadeReservada === 0) {
    if ($isClienteLogado) {
        $produto->definirQuantidadeDisponivel(0);
        $produto->definirQuantidadeReservada(1);
        $produto->definirDataReserva(date('Y-m-d H:i:s'));
        $produto->definirReservadoPorClienteId($idClienteLogado);
        $produto->salvar();

        $mensagem = 'Última unidade! Reservada para você por ' . $tempoReservaEmSegundos . ' segundos.';
        $tipoMensagem = 'sucesso';

        $quantidadeDisponivel = 0;
        $quantidadeReservada = 1;
        $reservadoPorClienteId = $idClienteLogado;
    } else {
        $mensagem = 'Última unidade! Faça login para poder reservar e comprar.';
        $tipoMensagem = 'erro';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Produto - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos (removidos para style.css, aqui para referência) */
        .container-detalhes { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 700px; margin: 20px auto; }
        .produto-info { display: flex; flex-direction: column; gap: 15px; }
        .produto-info h1 { color: #333; margin-top: 0; font-size: 2.2em; text-align: center; }
        .produto-info .preco { font-size: 2.5em; font-weight: bold; color: #28a745; text-align: center; margin: 10px 0; }
        .produto-info .descricao { color: #555; line-height: 1.6; }
        .produto-info .disponibilidade { font-size: 1.1em; font-weight: bold; color: #007bff; }
        .produto-info .disponibilidade.esgotado { color: #dc3545; }
        .form-compra { margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px; }
        .form-compra label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-compra input[type="number"] { width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; text-align: center; }
        .form-compra button { padding: 12px 25px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer; transition: background-color 0.3s ease; margin-left: 15px; }
        .form-compra button:hover { background-color: #0056b3; }
        .btn-disabled { background-color: #cccccc; cursor: not-allowed; }
        .mensagem-sucesso { color: green; border: 1px solid green; background-color: #e6ffe6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .mensagem-erro { color: red; border: 1px solid red; background-color: #ffe6e6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .links-navegacao { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;}
        .links-navegacao a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
        /* Novo: Estilos para imagens de fundo/banner do produto */
        .produto-banner {
            width: 100%;
            height: 300px; /* Altura fixa para o banner */
            background-size: cover;
            background-position: center;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: #fff;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="container-detalhes">
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if ($produto): ?>
            <div class="produto-banner" style="background-image: url('<?php echo htmlspecialchars($produto->obterUrlFotoFundo() ?? ''); ?>');">
                <?php if (!$produto->obterUrlFotoFundo()): ?>
                    Nenhum Banner Disponível
                <?php endif; ?>
            </div>

            <div class="produto-info">
                <h1><?php echo htmlspecialchars($produto->obterNome()); ?></h1>
                <p class="descricao"><?php echo htmlspecialchars($produto->obterDescricao() ?? 'Sem descrição.'); ?></p>
                <p class="preco">R$ <?php echo number_format($produto->obterPreco(), 2, ',', '.'); ?></p>

                <?php
                $textoDisponibilidade = '';
                $disponibilidadeClass = '';

                if ($quantidadeDisponivel > 0) {
                    $textoDisponibilidade = htmlspecialchars($quantidadeDisponivel . ' unidades disponíveis');
                    $disponibilidadeClass = '';
                } elseif ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado) {
                    $textoDisponibilidade = "Reservado para você.";
                    $disponibilidadeClass = '';
                } elseif ($quantidadeReservada > 0 && $reservadoPorClienteId !== null && $reservadoPorClienteId !== $idClienteLogado) {
                    $textoDisponibilidade = "Temporariamente reservado por outro cliente.";
                    $disponibilidadeClass = 'esgotado';
                } else {
                    $textoDisponibilidade = 'Esgotado';
                    $disponibilidadeClass = 'esgotado';
                }
                ?>
                <p class="disponibilidade <?php echo $disponibilidadeClass; ?>">
                    Status: <?php echo $textoDisponibilidade; ?>
                </p>

                <div class="form-compra">
                    <?php if ($isClienteLogado): ?>
                        <?php 
                        $exibirFormulario = ($quantidadeDisponivel > 0) || ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado);
                        ?>
                        <?php if ($exibirFormulario): ?>
                            <form action="adicionar_carrinho.php" method="POST">
                                <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($produto->obterIdProduto()); ?>">
                                <label for="quantidade_compra">Quantidade:</label>
                                <input type="number" id="quantidade_compra" name="quantidade_compra"
                                    min="1"
                                    max="<?php
                                        if ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado) {
                                            echo 1;
                                        } else {
                                            echo htmlspecialchars($quantidadeDisponivel);
                                        }
                                    ?>"
                                    value="1" required>
                                <button type="submit">Adicionar ao Carrinho</button>
                            </form>
                            <?php if ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado): ?>
                                <p style="font-size: 0.9em; color: #888; margin-top: 10px;">
                                    Você tem uma reserva ativa para este item.
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p style="color: red; font-weight: bold;">Produto indisponível para compra no momento.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: #007bff; font-weight: bold;">Para comprar este item, por favor:</p>
                        <div class="links-navegacao">
                            <a href="login_cliente.php">Faça Login como Cliente</a>
                            <a href="cadastro_cliente.php">Ou Cadastre-se</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="links-navegacao" style="margin-top: 30px;">
            <a href="listar_produtos_publico.php">Voltar para a Listagem de Produtos</a>
        </div>
    </div>
</body>
</html>