<?php
// public/login.php - Página de Login de Usuário (Vendedor/Admin)

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Usuario;

$mensagem = '';
$tipoMensagem = '';
$email = '';

// Se o USUÁRIO (vendedor) já estiver logado, redireciona para o dashboard do vendedor
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard_vendedor.php');
    exit();
}

// Processa a submissão do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagem = 'Por favor, preencha todos os campos.';
        $tipoMensagem = 'erro';
    } else {
        $usuario = Usuario::buscarPorEmail($email);

        if ($usuario && Usuario::verificarSenha($senha, $usuario->obterSenha())) {
            // Usuário autenticado com sucesso!
            $_SESSION['id_usuario'] = $usuario->obterIdUsuario();
            $_SESSION['nome_usuario'] = $usuario->obterNome();   

            header('Location: dashboard_vendedor.php');
            exit();
        } else {
            $mensagem = 'Email ou senha inválidos.';
            $tipoMensagem = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuário - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos específicos para login de usuário se necessário */
        body.centered-content { justify-content: center; align-items: center; }
        .container { max-width: 400px; }
        .links-navegacao a, .link-cadastro a { margin: 0 5px; } /* Ajuste de margem se necessário */
    </style>
</head>
<body class="centered-content">
    <div class="container">
        <h1>Login de Usuário</h1>
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <div class="link-cadastro">
            <p>Não tem uma conta de usuário? <a href="cadastro_usuario.php">Cadastre-se aqui</a>.</p>
            <p><a href="login_cliente.php">Login como Cliente</a></p>
            <p><a href="listar_produtos_publico.php">Voltar para a Vitrine</a></p> </div>
    </div>
</body>
</html>