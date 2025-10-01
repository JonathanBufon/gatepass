<?php
// backend/api/v1/estruturas.php - CORRIGIDO: ArgumentCountError

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
use GatePass\Models\Estrutura;
use GatePass\Models\Lugar;
use GatePass\Core\Auth;
use Exception;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        //  Lógica para cadastrar uma nova estrutura e gerar assentos (se aplicável)
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado.']);
            exit();
        }

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados || empty($dados['id_lugar']) || empty($dados['tipo_estrutura']) || empty($dados['nome_bloco'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados obrigatórios faltando.']);
            exit();
        }

        $idLugar = (int)$dados['id_lugar'];
        $lugar = Lugar::buscarPorId($idLugar);
        if (!$lugar || $lugar->obterIdUsuarioCriador() !== $payload->id) {
            http_response_code(403);
            echo json_encode(['erro' => 'Você não tem permissão para adicionar estruturas a este lugar.']);
            exit();
        }

        // Adicionando as verificações de tipo antes de converter
        $idProdutoPadrao = isset($dados['id_produto_padrao']) ? (int)$dados['id_produto_padrao'] : null;
        $capacidadePista = isset($dados['capacidade_pista']) ? (int)$dados['capacidade_pista'] : null;
        $prefixoAssentos = $dados['prefixo_assentos'] ?? null;
        $qtdAssentosPorBloco = isset($dados['qtd_assentos_por_bloco']) ? (int)$dados['qtd_assentos_por_bloco'] : null;
        
        try {
            $estrutura = new Estrutura(
                $idLugar,
                htmlspecialchars($dados['tipo_estrutura']),
                htmlspecialchars($dados['nome_bloco']),
                $idProdutoPadrao,      // Argumento 4
                $capacidadePista,      // Argumento 5
                htmlspecialchars($prefixoAssentos), // Argumento 6
                $qtdAssentosPorBloco,  // Argumento 7
                null                   // <-- ARGUMENTO 8 ADICIONADO AQUI
            );
            
            if ($estrutura->salvar()) {
                if ($estrutura->obterTipoEstrutura() === 'assentos_numerados') {
                    if (!$estrutura->gerarAssentosEmLote()) {
                        throw new Exception('Estrutura salva, mas falha ao gerar assentos.');
                    }
                }
                http_response_code(201);
                echo json_encode(['mensagem' => 'Estrutura e assentos (se aplicável) cadastrados com sucesso!', 'id_estrutura' => $estrutura->obterIdEstrutura()]);
            } else {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao cadastrar estrutura.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // Lógica para listar estruturas de um lugar específico
        $payload = Auth::validarToken();
        $idLugar = $_GET['id_lugar'] ?? null;
        if (!$payload || $payload->tipo !== 'usuario' || !filter_var($idLugar, FILTER_VALIDATE_INT)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de lugar inválido.']);
            exit();
        }
        
        try {
            $estruturas = Estrutura::buscarPorLugar($idLugar);
            http_response_code(200);
            echo json_encode($estruturas);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao buscar estruturas: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Lógica para editar uma estrutura existente
        http_response_code(501);
        echo json_encode(['erro' => 'Funcionalidade de edição de estrutura ainda não implementada.']);
        break;

    case 'DELETE':
        // Lógica para excluir uma estrutura
        http_response_code(501);
        echo json_encode(['erro' => 'Funcionalidade de exclusão de estrutura ainda não implementada.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}