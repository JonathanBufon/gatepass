<?php
// public/editar_perfil.php

session_start(); 

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth;
use GatePass\Models\Usuario;

Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$mensagem = '';
$tipoMensagem = '';

$usuario = Usuario::buscarPorId($idUsuarioLogado);

if (!$usuario) {
    header('Location: logout.php');
    exit();
}

$nome = $usuario->obterNome();
$email = $usuario->obterEmail();
$senha = '';
$confirmaSenha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeNovo = $_POST['nome'] ?? '';
    $emailNovo = $_POST['email'] ?? '';
    $senhaNova = $_POST['senha'] ?? '';
    $confirmaSenhaNova = $_POST['confirma_senha'] ?? '';

    $erros = [];

    if (empty($nomeNovo)) {
        $erros[] = 'O campo Nome é obrigatório.';
    }
    if (empty($emailNovo)) {
        $erros[] = 'O campo Email é obrigatório.';
    } elseif (!filter_var($emailNovo, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O Email informado não é válido.';
    } else {
        $usuarioComNovoEmail = Usuario::buscarPorEmail($emailNovo);
        if ($usuarioComNovoEmail && $usuarioComNovoEmail->obterIdUsuario() !== $idUsuarioLogado) {
            $erros[] = 'Este email já está cadastrado por outro usuário.';
        }
    }

    // Validação de senha, se uma nova senha for fornecida
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

    // Se não houver erros, tenta atualizar o usuário
    if (empty($erros)) {
        try {
            $usuario->definirNome(htmlspecialchars($nomeNovo));
            $usuario->definirEmail(htmlspecialchars($emailNovo));

            // Se uma nova senha foi fornecida e passou na validação, atualiza-a
            if (!empty($senhaNova)) { // A validação acima já garante que não é a senha atual
                $usuario->definirSenha(Usuario::gerarHashSenha($senhaNova));
            }

            if ($usuario->salvar()) {
                $_SESSION['nome_usuario'] = $usuario->obterNome();
                $_SESSION['email_usuario'] = $usuario->obterEmail();

                $mensagem = 'Perfil atualizado com sucesso!';
                $tipoMensagem = 'sucesso';
                $nome = $usuario->obterNome();
                $email = $usuario->obterEmail();
                $senha = '';
                $confirmaSenha = '';
            } else {
                $mensagem = 'Ocorreu um erro ao atualizar o perfil. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        $nome = htmlspecialchars($nomeNovo);
        $email = htmlspecialchars($emailNovo);
        $senha = '';
        $confirmaSenha = '';
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
    <title>Editar Perfil - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos específicos se necessário, ou usar os do style.css */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #0056b3; }
        .mensagem-sucesso { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .mensagem-erro { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .link-voltar { text-align: center; margin-top: 20px; }
        .link-voltar a { color: #007bff; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Perfil</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="editar_perfil.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Nova Senha (deixe em branco para não alterar):</label>
                <input type="password" id="senha" name="senha">
            </div>
            <div class="form-group">
                <label for="confirma_senha">Confirmar Nova Senha:</label>
                <input type="password" id="confirma_senha" name="confirma_senha">
            </div>
            <button type="submit">Atualizar Perfil</button>
        </form>

        <div class="link-voltar">
            <a href="index.php">Voltar para a Página Principal</a>
            <br><br>
            <a href="excluir_perfil.php" style="color: red; text-decoration: none; font-weight: bold;">Excluir Minha Conta</a>
        </div>
    </div>
</body>
</html>