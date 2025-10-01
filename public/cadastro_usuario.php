<?php
// public/cadastro_usuario.php

require_once __DIR__ . '/../vendor/autoload.php';

use GatePass\Models\Usuario;

$nome = '';
$email = '';
$senha = '';
$confirmaSenha = '';
$mensagem = ''; // mensagens de sucesso ou erro
$tipoMensagem = ''; // 'sucesso' ou 'erro'

// Verifica se o formulário foi submetido (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta os dados do formulário
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';

    // 2. Validação dos dados
    $erros = [];

    if (empty($nome)) {
        $erros[] = 'O campo Nome é obrigatório.';
    }
    if (empty($email)) {
        $erros[] = 'O campo Email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O Email informado não é válido.';
    } else {
        // Verificar se o email já existe no banco de dados
        if (Usuario::buscarPorEmail($email)) {
            $erros[] = 'Este email já está cadastrado.';
        }
    }
    if (empty($senha)) {
        $erros[] = 'O campo Senha é obrigatório.';
    } elseif (strlen($senha) < 6) {
        $erros[] = 'A Senha deve ter pelo menos 6 caracteres.';
    }
    if ($senha !== $confirmaSenha) {
        $erros[] = 'A confirmação de senha não confere.';
    }

    // 3. Processa se não houver erros
    if (empty($erros)) {
        try {
            // Gera o hash da senha
            $senhaHash = Usuario::gerarHashSenha($senha);

            // Cria uma nova instância de Usuario
            $novoUsuario = new Usuario(
                null,       // id_usuario é null para um novo registro
                htmlspecialchars($nome), // Limpa o nome para segurança
                htmlspecialchars($email), // Limpa o email para segurança
                $senhaHash  // Passa a senha hashada
            );

            // Salva o usuário no banco de dados
            if ($novoUsuario->salvar()) {
                $mensagem = 'Usuário cadastrado com sucesso! Agora você pode fazer login.';
                $tipoMensagem = 'sucesso';
                
                // Limpa campos do formulário após sucesso
                $nome = $email = $senha = $confirmaSenha = '';
            } else {
                $mensagem = 'Ocorreu um erro inesperado ao cadastrar o usuário. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        // Exibe os erros de validação
        $mensagem = implode('<br>', $erros);
        $tipoMensagem = 'erro';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Cadastro de Usuário</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_usuario.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="confirma_senha">Confirmar Senha:</label>
                <input type="password" id="confirma_senha" name="confirma_senha" required>
            </div>
            <button type="submit">Cadastrar</button>
        </form>

        <div class="link-login">
            Já tem uma conta? <a href="login.php">Faça Login</a>
        </div>
    </div>
</body>
</html>