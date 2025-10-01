<?php
// backend/api/v1/lugares.php - Endpoint de API para Gerenciamento de Lugares

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Apenas para desenvolvimento
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
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
        // Lógica para cadastrar um novo lugar
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado.']);
            exit();
        }

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados || empty($dados['nome_lugar'])) {
            http_response_code(400);
            echo json_encode(['erro' => 'O nome do lugar é obrigatório.']);
            exit();
        }

        try {
            $lugar = new Lugar(
                $payload->id,
                htmlspecialchars($dados['nome_lugar']),
                htmlspecialchars($dados['descricao_lugar'] ?? null)
            );
            if ($lugar->salvar()) {
                http_response_code(201);
                echo json_encode(['mensagem' => 'Lugar cadastrado com sucesso!', 'id_lugar' => $lugar->obterIdLugar()]);
            } else {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao cadastrar lugar.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // Lógica para listar todos os lugares do usuário logado
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado.']);
            exit();
        }

        try {
            $lugares = Lugar::buscarTodos($payload->id);
            http_response_code(200);
            echo json_encode($lugares);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao buscar lugares: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Lógica para editar um lugar existente
        $payload = Auth::validarToken();
        $idLugar = $_GET['id'] ?? null;
        if (!$payload || $payload->tipo !== 'usuario' || !filter_var($idLugar, FILTER_VALIDATE_INT)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de lugar inválido.']);
            exit();
        }
        
        $lugar = Lugar::buscarPorId($idLugar);
        if (!$lugar || $lugar->obterIdUsuarioCriador() !== $payload->id) {
            http_response_code(403);
            echo json_encode(['erro' => 'Você não tem permissão para editar este lugar.']);
            exit();
        }

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados) {
            http_response_code(400);
            echo json_encode(['erro' => 'Requisição inválida.']);
            exit();
        }

        $lugar->definirNomeLugar(htmlspecialchars($dados['nome_lugar'] ?? $lugar->obterNomeLugar()));
        $lugar->definirDescricaoLugar(htmlspecialchars($dados['descricao_lugar'] ?? $lugar->obterDescricaoLugar()));
        
        if ($lugar->salvar()) {
            http_response_code(200);
            echo json_encode(['mensagem' => 'Lugar atualizado com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar lugar.']);
        }
        break;

    case 'DELETE':
        // Lógica para excluir um lugar
        $payload = Auth::validarToken();
        $idLugar = $_GET['id'] ?? null;
        if (!$payload || $payload->tipo !== 'usuario' || !filter_var($idLugar, FILTER_VALIDATE_INT)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de lugar inválido.']);
            exit();
        }

        $lugar = Lugar::buscarPorId($idLugar);
        if (!$lugar || $lugar->obterIdUsuarioCriador() !== $payload->id) {
            http_response_code(403);
            echo json_encode(['erro' => 'Você não tem permissão para excluir este lugar.']);
            exit();
        }
        
        try {
            if ($lugar->excluir()) {
                http_response_code(200);
                echo json_encode(['mensagem' => 'Lugar excluído com sucesso!']);
            } else {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro ao excluir lugar.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}