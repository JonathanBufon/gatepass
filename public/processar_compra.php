<?php
// public/processar_compra.php - Processa múltiplos itens do carrinho

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Database;
use GatePass\Models\Produto;
use GatePass\Models\Compra;

if (!isset($_SESSION['id_cliente_logado'])) {
    $_SESSION['mensagem_publica'] = 'Você precisa estar logado como cliente para finalizar uma compra.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: login_cliente.php');
    exit();
}

$idClienteLogado = $_SESSION['id_cliente_logado'];

$mensagem = '';
$tipoMensagem = '';
$redirectDestination = 'listar_produtos_publico.php'; // Redirecionamento padrão em caso de erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itensCarrinhoPost = $_POST['itens_carrinho'] ?? []; // Coleta array de itens do carrinho
    $metodoPagamento = $_POST['metodo_pagamento'] ?? null;

    // --- 1. Validação Inicial dos Dados Recebidos ---
    if (empty($itensCarrinhoPost) || !is_array($itensCarrinhoPost)) {
        $mensagem = 'Nenhum item encontrado no pedido.';
        $tipoMensagem = 'erro';
    } elseif (empty($metodoPagamento) || !in_array($metodoPagamento, ['PIX', 'Boleto', 'CartaoCredito'])) {
        $mensagem = 'Método de pagamento inválido.';
        $tipoMensagem = 'erro';
    } else {
        $pdo = Database::obterInstancia()->obterConexao();
        $pdo->beginTransaction(); // Inicia a transação para garantir atomicidade

        try {
            $idsProdutosProcessados = []; // Para garantir que um produto não seja processado mais de uma vez se houver bug no formulário

            foreach ($itensCarrinhoPost as $itemPost) {
                $idProduto = $itemPost['id_produto'] ?? null;
                $quantidadeComprada = $itemPost['quantidade'] ?? null;

                if (!filter_var($idProduto, FILTER_VALIDATE_INT) || (int)$idProduto <= 0 ||
                    !filter_var($quantidadeComprada, FILTER_VALIDATE_INT) || (int)$quantidadeComprada <= 0) {

                    throw new Exception('Dados de item no pedido inválidos.');
                }

                $idProduto = (int)$idProduto;
                $quantidadeComprada = (int)$quantidadeComprada;

                // Garante que o mesmo produto não seja processado duas vezes no mesmo loop
                if (in_array($idProduto, $idsProdutosProcessados)) {
                    throw new Exception('Pedido contém itens duplicados para o mesmo produto.');
                }
                $idsProdutosProcessados[] = $idProduto;

                // --- 2. Re-validação de Estoque e Reserva para CADA ITEM ---
                $produto = Produto::buscarPorId($idProduto);

                if (!$produto) {
                    throw new Exception('Produto "' . htmlspecialchars($itemPost['id_produto']) . '" não encontrado para compra.');
                }

                $quantidadeDisponivel = $produto->obterQuantidadeDisponivel();
                $quantidadeReservada = $produto->obterQuantidadeReservada();
                $dataReserva = $produto->obterDataReserva();
                $reservadoPorClienteId = $produto->obterReservadoPorClienteId();
                $precoUnitario = $produto->obterPreco();
                $idUsuarioVendedor = $produto->obterIdUsuario();

                $tempoReservaEmSegundos = 120;
                $agora = time();

                $ehItemReservadoParaMim = ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado);
                $reservaExpirou = ($quantidadeReservada > 0 && $dataReserva !== null && ($agora - strtotime($dataReserva)) > $tempoReservaEmSegundos);

                $motivoNaoPodeComprar = '';

                if ($reservaExpirou) {
                    $produto->definirQuantidadeDisponivel($quantidadeDisponivel + $quantidadeReservada);
                    $produto->definirQuantidadeReservada(0);
                    $produto->definirDataReserva(null);
                    $produto->definirReservadoPorClienteId(null);
                    $produto->salvar();
                    $motivoNaoPodeComprar = 'A reserva do produto "' . htmlspecialchars($produto->obterNome()) . '" expirou. Por favor, tente novamente.';
                } elseif ($ehItemReservadoParaMim) {
                    if ($quantidadeComprada !== 1) { // Só pode comprar 1 unidade do item reservado para você
                        $motivoNaoPodeComprar = 'Você só pode comprar 1 unidade do item "' . htmlspecialchars($produto->obterNome()) . '" que está reservado para você.';
                    }
                } elseif ($quantidadeComprada > $quantidadeDisponivel) {
                    $motivoNaoPodeComprar = 'Não há estoque suficiente para a quantidade desejada do produto "' . htmlspecialchars($produto->obterNome()) . '".';
                } elseif ($quantidadeDisponivel === 0 && !$ehItemReservadoParaMim) {
                    $motivoNaoPodeComprar = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" esgotado ou não disponível para compra.';
                } elseif ($quantidadeReservada > 0 && $reservadoPorClienteId !== null && $reservadoPorClienteId !== $idClienteLogado) {
                    $motivoNaoPodeComprar = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" está temporariamente reservado por outro cliente.';
                }

                if (!empty($motivoNaoPodeComprar)) {
                    throw new Exception($motivoNaoPodeComprar);
                }

                // --- 3. Processa a Compra do Item ---
                $valorTotalUnitario = $precoUnitario * $quantidadeComprada;

                $sucessoEstoque = $produto->decrementarEstoque($quantidadeComprada);
                if (!$sucessoEstoque) {
                    throw new Exception('Falha ao atualizar o estoque do produto "' . htmlspecialchars($produto->obterNome()) . '". Tente novamente.');
                }

                $novaCompra = new Compra(
                    $idProduto,
                    $idClienteLogado,
                    $idUsuarioVendedor,
                    $quantidadeComprada,
                    $valorTotalUnitario,
                    $metodoPagamento // Passa o método de pagamento
                );
                $sucessoCompra = $novaCompra->salvar();
                if (!$sucessoCompra) {
                    throw new Exception('Falha ao registrar a compra do produto "' . htmlspecialchars($produto->obterNome()) . '".');
                }
            } // Fim do foreach ($itensCarrinhoPost as $itemPost)

            // Se todas as compras e atualizações de estoque foram bem-sucedidas
            $pdo->commit(); // Comita a transação

            // --- 4. Limpa o Carrinho após Conclusão Bem-Sucedida ---
            unset($_SESSION['carrinho']);

            // Feedback e Redirecionamento de Sucesso
            $_SESSION['mensagem_publica'] = 'Sua compra foi realizada com sucesso! Você pode ver os detalhes em "Minhas Compras".';
            $_SESSION['tipo_mensagem_publica'] = 'sucesso';
            header('Location: minhas_compras.php');
            exit();

        } catch (Exception $e) {
            $pdo->rollBack(); // Desfaz todas as operações em caso de erro
            $mensagem = 'Erro ao processar a compra: ' . $e->getMessage();
            $tipoMensagem = 'erro';
            $redirectDestination = 'ver_carrinho.php'; // Volta para o carrinho em caso de erro
        }
    }
} else {
    $mensagem = 'Acesso inválido. Use o formulário de compra.';
    $tipoMensagem = 'erro';
    $redirectDestination = 'listar_produtos_publico.php';
}

// Redireciona em caso de erro ou acesso inválido
$_SESSION['mensagem_publica'] = $mensagem;
$_SESSION['tipo_mensagem_publica'] = $tipoMensagem;
header('Location: ' . $redirectDestination);
exit();