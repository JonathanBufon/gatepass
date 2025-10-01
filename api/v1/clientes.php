<?php
// backend/api/v1/clientes.php - Endpoint de API para Gerenciamento de Clientes

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Apenas para ambiente de desenvolvimento
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
use GatePass\Models\Cliente;
use GatePass\Core\Auth;
use Exception;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erro' => 'Requisição inválida. O corpo deve ser um JSON válido.']);
            exit();
        }

        $acao = $dados['acao'] ?? 'login';

        if ($acao === 'cadastro') {
            // Lógica de Cadastro de Cliente
            $nome = $dados['nome'] ?? '';
            $email = $dados['email'] ?? '';
            $senha = $dados['senha'] ?? '';
            $confirmaSenha = $dados['confirmar_senha'] ?? '';
            $cpf = $dados['cpf'] ?? null;
            $telefone = $dados['telefone'] ?? null;
            $erros = [];

            if (empty($nome) || empty($email) || empty($senha) || empty($confirmaSenha)) {
                $erros[] = 'Todos os campos são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'O Email informado não é válido.';
            } elseif (Cliente::buscarPorEmail($email)) {
                $erros[] = 'Este email já está cadastrado.';
            } elseif (strlen($senha) < 6) {
                $erros[] = 'A Senha deve ter pelo menos 6 caracteres.';
            } elseif ($senha !== $confirmaSenha) {
                $erros[] = 'A confirmação de senha não confere.';
            }
            if (!empty($erros)) {
                http_response_code(400); // Requisição inválida
                echo json_encode(['erros' => $erros]);
                exit();
            }

            try {
                $senhaHash = Cliente::gerarHashSenha($senha);
                $novoCliente = new Cliente(
                    htmlspecialchars($nome),
                    htmlspecialchars($email),
                    $senhaHash,
                    $cpf,
                    $telefone
                );
                if ($novoCliente->salvar()) {
                    http_response_code(201); // Criado
                    echo json_encode(['mensagem' => 'Cliente cadastrado com sucesso!']);
                } else {
                    http_response_code(500); // Erro do servidor
                    echo json_encode(['erro' => 'Ocorreu um erro ao cadastrar o cliente.']);
                }
            } catch (Exception $e) {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
            }
        } else { // $acao === 'login'
            // Lógica de Login de Cliente
            $email = $dados['email'] ?? '';
            $senha = $dados['senha'] ?? '';
            if (empty($email) || empty($senha)) {
                http_response_code(400); // Requisição inválida
                echo json_encode(['erro' => 'Email e senha são obrigatórios.']);
                exit();
            }

            try {
                $cliente = Cliente::buscarPorEmail($email);
                if (!$cliente || !Cliente::verificarSenha($senha, $cliente->obterSenha())) {
                    http_response_code(401); // Não autorizado
                    echo json_encode(['erro' => 'Email ou senha inválidos.']);
                    exit();
                }

                $token = Auth::gerarToken($cliente->obterIdCliente(), $cliente->obterEmail(), 'cliente');
                http_response_code(200);
                echo json_encode(['mensagem' => 'Login bem-sucedido!', 'token' => $token, 'tipo' => 'cliente']);
            } catch (Exception $e) {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Erro interno do servidor.']);
            }
        }
        break;

    case 'GET':
        // Lógica para buscar perfil do cliente logado
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'cliente') {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $cliente = Cliente::buscarPorId($payload->id);
        if (!$cliente) {
            http_response_code(404); // Não encontrado
            echo json_encode(['erro' => 'Cliente não encontrado.']);
            exit();
        }

        $perfil = [
            'id_cliente' => $cliente->obterIdCliente(),
            'nome' => $cliente->obterNome(),
            'email' => $cliente->obterEmail(),
            'cpf' => $cliente->obterCpf(),
            'telefone' => $cliente->obterTelefone()
        ];
        http_response_code(200); // OK
        echo json_encode($perfil);
        break;

    case 'PUT':
        // Lógica de Edição de Perfil de Cliente
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'cliente') {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $idClienteLogado = $payload->id;
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!$dados) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erro' => 'Requisição inválida.']);
            exit();
        }

        $cliente = Cliente::buscarPorId($idClienteLogado);
        if (!$cliente) {
            http_response_code(404); // Não encontrado
            echo json_encode(['erro' => 'Cliente não encontrado.']);
            exit();
        }

        $nomeNovo = $dados['nome'] ?? $cliente->obterNome();
        $emailNovo = $dados['email'] ?? $cliente->obterEmail();
        $senhaNova = $dados['senha'] ?? '';
        $confirmaSenhaNova = $dados['confirma_senha'] ?? '';
        $cpfNovo = $dados['cpf'] ?? $cliente->obterCpf();
        $telefoneNovo = $dados['telefone'] ?? $cliente->obterTelefone();

        $erros = [];
        if (empty($nomeNovo)) { $erros[] = 'O campo Nome é obrigatório.'; }
        if (empty($emailNovo) || !filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'O Email informado não é válido.';
        } else {
            $clienteComNovoEmail = Cliente::buscarPorEmail($emailNovo);
            if ($clienteComNovoEmail && $clienteComNovoEmail->obterIdCliente() !== $idClienteLogado) {
                $erros[] = 'Este email já está cadastrado.';
            }
        }
        if (!empty($senhaNova)) {
            if (strlen($senhaNova) < 6) { $erros[] = 'A Senha deve ter pelo menos 6 caracteres.'; }
            if ($senhaNova !== $confirmaSenhaNova) { $erros[] = 'A confirmação de senha não confere.'; }
            if (Cliente::verificarSenha($senhaNova, $cliente->obterSenha())) {
                $erros[] = 'A nova senha não pode ser igual à senha atual.';
            }
        }
        if (!empty($erros)) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erros' => $erros]);
            exit();
        }

        try {
            $cliente->definirNome(htmlspecialchars($nomeNovo));
            $cliente->definirEmail(htmlspecialchars($emailNovo));
            if (!empty($senhaNova)) { $cliente->definirSenha(Cliente::gerarHashSenha($senhaNova)); }
            $cliente->definirCpf($cpfNovo);
            $cliente->definirTelefone($telefoneNovo);

            if ($cliente->salvar()) {
                http_response_code(200); // OK
                echo json_encode(['mensagem' => 'Perfil atualizado com sucesso!']);
            } else {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Ocorreu um erro ao atualizar o perfil.']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Lógica de Exclusão de Cliente
        $payload = Auth::validarToken();
        if (!$payload || $payload->tipo !== 'cliente') {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $idClienteLogado = $payload->id;
        $cliente = Cliente::buscarPorId($idClienteLogado);
        if (!$cliente) {
            http_response_code(404); // Não encontrado
            echo json_encode(['erro' => 'Cliente não encontrado.']);
            exit();
        }
        
        try {
            if ($cliente->excluir()) {
                http_response_code(200); // OK
                echo json_encode(['mensagem' => 'Cliente excluído com sucesso!']);
            } else {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Ocorreu um erro ao excluir o cliente.']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Erro do servidor
            echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405); // Metodo Não Permitido
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}