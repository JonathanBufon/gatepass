<?php
// backend/api/v1/usuarios.php - Endpoint de API para Gerenciamento de Usuários

// NOVO: Define o cabeçalho de resposta como JSON e permite CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Apenas para desenvolvimento
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../../vendor/autoload.php';
use GatePass\Models\Usuario;
use GatePass\Core\Auth;
use Exception;

// Responde a requisições OPTIONS (pré-voo CORS) imediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!$dados) {
            http_response_code(400); // Bad Request
            echo json_encode(['erro' => 'Requisição inválida. O corpo deve ser um JSON válido.']);
            exit();
        }

        $acao = $dados['acao'] ?? 'login';

        if ($acao === 'cadastro') {
            // --- Lógica de Cadastro ---
            $nome = $dados['nome'] ?? '';
            $email = $dados['email'] ?? '';
            $senha = $dados['senha'] ?? '';
            $confirmaSenha = $dados['confirma_senha'] ?? '';

            $erros = [];

            if (empty($nome) || empty($email) || empty($senha) || empty($confirmaSenha)) {
                $erros[] = 'Todos os campos são obrigatórios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erros[] = 'O Email informado não é válido.';
            } elseif (Usuario::buscarPorEmail($email)) {
                $erros[] = 'Este email já está cadastrado.';
            } elseif (strlen($senha) < 6) {
                $erros[] = 'A Senha deve ter pelo menos 6 caracteres.';
            } elseif ($senha !== $confirmaSenha) {
                $erros[] = 'A confirmação de senha não confere.';
            }

            if (!empty($erros)) {
                http_response_code(400);
                echo json_encode(['erros' => $erros]);
                exit();
            }

            try {
                $senhaHash = Usuario::gerarHashSenha($senha);
                $novoUsuario = new Usuario(
                    null,
                    htmlspecialchars($nome),
                    htmlspecialchars($email),
                    $senhaHash
                );

                if ($novoUsuario->salvar()) {
                    http_response_code(201); // Created
                    echo json_encode(['mensagem' => 'Usuário cadastrado com sucesso!']);
                } else {
                    http_response_code(500);
                    echo json_encode(['erro' => 'Ocorreu um erro ao cadastrar o usuário.']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
            }
        } else { // $acao === 'login'
            // --- Lógica de Login ---
            $email = $dados['email'] ?? '';
            $senha = $dados['senha'] ?? '';
            
            if (empty($email) || empty($senha)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Email e senha são obrigatórios.']);
                exit();
            }

            try {
                $usuario = Usuario::buscarPorEmail($email);
                if (!$usuario || !Usuario::verificarSenha($dados['senha'], $usuario->obterSenha())) {
                    http_response_code(401);
                    echo json_encode(['erro' => 'Email ou senha inválidos.']);
                    exit();
                }

                $token = Auth::gerarToken($usuario->obterIdUsuario(), $usuario->obterEmail(), 'usuario');

                http_response_code(200);
                echo json_encode(['mensagem' => 'Login bem-sucedido!', 'token' => $token, 'tipo' => 'usuario']);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['erro' => 'Erro interno do servidor.']);
            }
        }
        break;

    case 'GET':
        // Lógica GET para buscar dados do perfil do usuário logado
        $payload = Auth::validarToken();

        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401); // Unauthorized
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $usuario = Usuario::buscarPorId($payload->id);
        
        if (!$usuario) {
            http_response_code(404); // Not Found
            echo json_encode(['erro' => 'Usuário não encontrado.']);
            exit();
        }

        // Retorna os dados do perfil, excluindo a senha
        $perfil = [
            'id_usuario' => $usuario->obterIdUsuario(),
            'nome' => $usuario->obterNome(),
            'email' => $usuario->obterEmail(),
            'data_cadastro' => $usuario->obterDataCadastro(),
        ];
        http_response_code(200); // OK
        echo json_encode($perfil);
        break;

    case 'PUT':
        // --- Lógica de Edição de Perfil de Usuário ---
        $payload = Auth::validarToken();

        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401);
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }

        $idUsuarioLogado = $payload->id;
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!$dados) {
            http_response_code(400);
            echo json_encode(['erro' => 'Requisição inválida. O corpo deve ser um JSON válido.']);
            exit();
        }
        
        $usuario = Usuario::buscarPorId($idUsuarioLogado);
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['erro' => 'Usuário não encontrado.']);
            exit();
        }

        $nomeNovo = $dados['nome'] ?? $usuario->obterNome();
        $emailNovo = $dados['email'] ?? $usuario->obterEmail();
        $senhaNova = $dados['senha'] ?? '';
        $confirmaSenhaNova = $dados['confirma_senha'] ?? '';
        $erros = [];

        // Validações
        if (empty($nomeNovo)) {
            $erros[] = 'O campo Nome é obrigatório.';
        }
        if (empty($emailNovo) || !filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'O Email informado não é válido.';
        } else {
            $usuarioComNovoEmail = Usuario::buscarPorEmail($emailNovo);
            if ($usuarioComNovoEmail && $usuarioComNovoEmail->obterIdUsuario() !== $idUsuarioLogado) {
                $erros[] = 'Este email já está cadastrado por outro usuário.';
            }
        }
        
        if (!empty($senhaNova)) {
            if (strlen($senhaNova) < 6) {
                $erros[] = 'A Senha deve ter pelo menos 6 caracteres.';
            }
            if ($senhaNova !== $confirmaSenhaNova) {
                $erros[] = 'A confirmação de senha não confere.';
            }
            if (Usuario::verificarSenha($senhaNova, $usuario->obterSenha())) {
                $erros[] = 'A nova senha não pode ser igual à senha atual.';
            }
        }

        if (!empty($erros)) {
            http_response_code(400); // Requisição inválida
            echo json_encode(['erros' => $erros]);
            exit();
        }

        try {
            $usuario->definirNome(htmlspecialchars($nomeNovo));
            $usuario->definirEmail(htmlspecialchars($emailNovo));
            if (!empty($senhaNova)) {
                $usuario->definirSenha(Usuario::gerarHashSenha($senhaNova));
            }

            if ($usuario->salvar()) {
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
        // --- Lógica de Exclusão de Usuário ---
        $payload = Auth::validarToken();

        if (!$payload || $payload->tipo !== 'usuario') {
            http_response_code(401); // Não autorizado
            echo json_encode(['erro' => 'Acesso não autorizado ou token inválido.']);
            exit();
        }
        
        $idUsuarioLogado = $payload->id;
        $usuario = Usuario::buscarPorId($idUsuarioLogado);

        if (!$usuario) {
            http_response_code(404); // Não encontrado
            echo json_encode(['erro' => 'Usuário não encontrado.']);
            exit();
        }
        
        try {
            if ($usuario->excluir()) {
                http_response_code(200); // OK
                echo json_encode(['mensagem' => 'Usuário excluído com sucesso!']);
            } else {
                http_response_code(500); // Erro do servidor
                echo json_encode(['erro' => 'Ocorreu um erro ao excluir o usuário.']);
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