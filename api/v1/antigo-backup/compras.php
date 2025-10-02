<?php
// backend/api/v1/CompraController.php - Endpoint de API para o Módulo de Compras

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
use GatePass\Models\Produto;
use GatePass\Models\Assento;
use GatePass\Models\Pedido;
use GatePass\Models\ItemPedido;
use GatePass\Core\Auth;
use GatePass\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        // --- Lógica para Finalizar a Compra de Múltiplos Itens ---
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'cliente') {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado. Apenas clientes podem realizar compras.']);
            exit();
        }

        $idClienteLogado = $payload->id;
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!$dados || empty($dados['itens_carrinho']) || empty($dados['metodo_pagamento'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Requisição inválida. Dados do carrinho e método de pagamento são obrigatórios.']);
            exit();
        }

        $metodoPagamento = $dados['metodo_pagamento'];
        $itensCarrinho = $dados['itens_carrinho'];
        $valorTotalPedido = 0;
        $idUsuarioVendedor = null;

        $pdo = Database::obterInstancia()->obterConexao();
        $pdo->beginTransaction();

        try {
            // --- Validação Final do Estoque e Reserva para CADA ITEM do carrinho ---
            foreach ($itensCarrinho as $item) {
                $idProduto = $item['id_produto'];
                $quantidade = $item['quantidade'];
                
                $produto = Produto::buscarPorId($idProduto);
                if (!$produto) {
                    throw new Exception('Produto não encontrado no banco de dados.');
                }
                
                // Determina o vendedor principal do pedido (o primeiro produto)
                if ($idUsuarioVendedor === null) {
                    $idUsuarioVendedor = $produto->obterIdUsuario();
                }
                
                // Validação para assentos numerados
                if ($item['assentos'] ?? false) {
                    foreach ($item['assentos'] as $idAssento) {
                        $assento = Assento::buscarPorId($idAssento);
                        if (!$assento) {
                            throw new Exception('Assento inválido ou não encontrado.');
                        }
                        if ($assento->obterStatusAssento() !== 'disponivel' && $assento->obterIdClienteReserva() !== $idClienteLogado) {
                            throw new Exception('O assento ' . $assento->obterNumeroAssento() . ' não está disponível.');
                        }
                        
                        $valorTotalPedido += $produto->obterPreco();
                    }
                } else {
                    // Validação para pista
                    // Lógica para buscar a capacidade da pista e verificar a quantidade vendida
                    $valorTotalPedido += ($produto->obterPreco() * $quantidade);
                }
            }

            // --- Criação do Pedido e Itens do Pedido ---
            $pedido = new Pedido(
                $idClienteLogado,
                $idUsuarioVendedor,
                'pago', // Status 'pago' para pagamento simulado
                $metodoPagamento,
                $valorTotalPedido
            );
            if (!$pedido->salvar()) {
                throw new Exception('Falha ao criar o pedido.');
            }

            foreach ($itensCarrinho as $item) {
                $idProduto = $item['id_produto'];
                $quantidade = $item['quantidade'];
                $produto = Produto::buscarPorId($idProduto);

                if ($item['assentos'] ?? false) {
                    // Lógica para assentos numerados
                    foreach ($item['assentos'] as $idAssento) {
                        $assento = Assento::buscarPorId($idAssento);
                        $assento->definirStatusAssento('vendido');
                        $assento->definirIdClienteReserva(null);
                        $assento->definirDataReserva(null);
                        if (!$assento->salvar()) {
                            throw new Exception('Falha ao atualizar o status do assento ' . $assento->obterNumeroAssento());
                        }

                        $itemPedido = new ItemPedido(
                            $pedido->obterIdPedido(),
                            $idProduto,
                            1, // Quantidade sempre 1 para assentos
                            $produto->obterPreco(),
                            $assento->obterIdAssento()
                        );
                        if (!$itemPedido->salvar()) {
                            throw new Exception('Falha ao salvar item do pedido para o assento ' . $assento->obterNumeroAssento());
                        }
                    }
                } else {
                    // Lógica para pista
                    $itemPedido = new ItemPedido(
                        $pedido->obterIdPedido(),
                        $idProduto,
                        $quantidade,
                        $produto->obterPreco(),
                        null // id_assento é null para pista
                    );
                    if (!$itemPedido->salvar()) {
                        throw new Exception('Falha ao salvar item do pedido para o produto "' . $produto->obterNome() . '"');
                    }
                }
            }

            $pdo->commit();
            http_response_code(200); // Ok
            echo json_encode(['mensagem' => 'Compra realizada com sucesso!', 'pedido_id' => $pedido->obterIdPedido()]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao processar a compra: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // --- Lógica para Gerar Ingresso em PDF ---
        $payload = Auth::validarToken();
        $idPedido = $_GET['id_pedido'] ?? null;

        if (!$payload || $payload->tipo !== 'cliente' || !filter_var($idPedido, FILTER_VALIDATE_INT)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de pedido inválido.']);
            exit();
        }

        $pedido = Pedido::buscarPorId($idPedido);
        if (!$pedido || $pedido->obterIdCliente() !== $payload->id) {
            http_response_code(403);
            echo json_encode(['erro' => 'Você não tem permissão para acessar este pedido.']);
            exit();
        }

        // ... (lógica de geração do PDF a ser implementada, que irá
        // buscar os itens do pedido e renderizar o PDF) ...
        
        http_response_code(501);
        echo json_encode(['erro' => 'Funcionalidade de geração de PDF não implementada.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}
