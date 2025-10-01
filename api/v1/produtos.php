<?php
// backend/api/v1/produtos.php - Endpoint de API para Gerenciamento de Produtos

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Apenas para desenvolvimento
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
use GatePass\Models\Produto;
use GatePass\Core\Auth;
use GatePass\Utils\FileUpload;
use Exception;

// Responde a requisições OPTIONS (pré-voo CORS) imediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // OK
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        // Lógica de Cadastro de Novo Produto
        $payload = Auth::validarToken();

        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $dados = $_POST;
        $erros = [];

        // Validação dos dados
        if (empty($dados['nome']) || empty($dados['preco']) || empty($dados['quantidade'])) {
            $erros[] = 'Nome, preço e quantidade são obrigatórios.';
        }
        if (!is_numeric($dados['preco']) || (float)$dados['preco'] <= 0) {
            $erros[] = 'Preço deve ser um número positivo.';
        }
        if (!filter_var($dados['quantidade'], FILTER_VALIDATE_INT) || (int)$dados['quantidade'] <= 0) {
            $erros[] = 'Quantidade deve ser um número inteiro positivo.';
        }

        // Processamento dos uploads de imagens
        $urlFotoPerfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            try { $urlFotoPerfil = FileUpload::upload($_FILES['foto_perfil'], 'produtos'); } catch (Exception $e) { $erros[] = $e->getMessage(); }
        }
        $urlFotoFundo = null;
        if (isset($_FILES['foto_fundo']) && $_FILES['foto_fundo']['error'] === UPLOAD_ERR_OK) {
            try { $urlFotoFundo = FileUpload::upload($_FILES['foto_fundo'], 'produtos'); } catch (Exception $e) { $erros[] = $e->getMessage(); }
        }

        if (!empty($erros)) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erros' => $erros]);
            exit();
        }

        try {
            $novoProduto = new Produto(
                $payload->id,
                htmlspecialchars($dados['nome']),
                (float)$dados['preco'],
                (int)$dados['quantidade'],
                (int)$dados['quantidade'],
                empty($dados['descricao']) ? null : htmlspecialchars($dados['descricao']),
                0, null, null,
                $urlFotoPerfil,
                $urlFotoFundo
            );

            if ($novoProduto->salvar()) {
                http_response_code(201); // Criado
                echo json_encode(['mensagem' => 'Produto cadastrado com sucesso!', 'id' => $novoProduto->obterIdProduto()]);
            } else {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Erro ao cadastrar produto.']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    case 'GET':
        // Lógica de Listagem de Produtos
        $payload = Auth::validarToken();
        $idUsuario = ($payload && $payload->tipo === 'usuario') ? $payload->id : null;

        try {
            $produtos = Produto::buscarTodos($idUsuario); // Retorna todos os produtos, ou só os do vendedor logado
            http_response_code(200); // OK
            echo json_encode($produtos);
        } catch (Exception $e) {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Lógica de Edição de Produto
        $payload = Auth::validarToken();
        $idProduto = $_GET['id'] ?? null;

        if (!$payload || $payload->tipo !== 'usuario' || !filter_var($idProduto, FILTER_VALIDATE_INT)) {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de produto inválido.']);
            exit();
        }
        
        $idProduto = (int)$idProduto;
        $produto = Produto::buscarPorId($idProduto);

        if (!$produto || $produto->obterIdUsuario() !== $payload->id) {
            http_response_code(403); // Proibido
            echo json_encode(['erro' => 'Você não tem permissão para editar este produto.']);
            exit();
        }

        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erro' => 'Requisição inválida. O corpo deve ser um JSON válido.']);
            exit();
        }

        $produto->definirNome(htmlspecialchars($dados['nome']));
        $produto->definirDescricao(empty($dados['descricao']) ? null : htmlspecialchars($dados['descricao']));
        $produto->definirPreco((float)$dados['preco']);
        $produto->definirQuantidadeTotal((int)$dados['quantidade']);
        
        if ($produto->salvar()) {
            http_response_code(200); // OK
            echo json_encode(['mensagem' => 'Produto atualizado com sucesso!']);
        } else {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro ao atualizar produto.']);
        }
        break;

    case 'DELETE':
        // Lógica de Exclusão de Produto
        $payload = Auth::validarToken();
        $idProduto = $_GET['id'] ?? null;

        if (!$payload || $payload->tipo !== 'usuario' || !filter_var($idProduto, FILTER_VALIDATE_INT)) {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou ID de produto inválido.']);
            exit();
        }

        $idProduto = (int)$idProduto;
        $produto = Produto::buscarPorId($idProduto);

        if (!$produto || $produto->obterIdUsuario() !== $payload->id) {
            http_response_code(403); // Proibido
            echo json_encode(['erro' => 'Você não tem permissão para excluir este produto.']);
            exit();
        }

        if ($produto->excluir()) {
            http_response_code(200); // OK
            echo json_encode(['mensagem' => 'Produto excluído com sucesso!']);
        } else {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro ao excluir produto.']);
        }
        break;

    default:
        http_response_code(405); // Metodo Não Permitido
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}