<?php
// public/login_cliente.php - Página de Login de Cliente (Comprador)

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Cliente;

$email = '';
$mensagem = '';
$tipoMensagem = '';

// Se o cliente já estiver logado, redireciona para a página de compras do cliente
if (isset($_SESSION['id_cliente_logado'])) {
    header('Location: minhas_compras.php');
    exit();
}

// Processa a submissão do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagem = 'Por favor, preencha seu email e senha.';
        $tipoMensagem = 'erro';
    } else {
        $cliente = Cliente::buscarPorEmail($email);

        if ($cliente && Cliente::verificarSenha($senha, $cliente->obterSenha())) {
            // Cliente autenticado com sucesso!
            $_SESSION['id_cliente_logado'] = $cliente->obterIdCliente();
            $_SESSION['email_cliente_logado'] = $cliente->obterEmail();
            $_SESSION['nome_cliente_logado'] = $cliente->obterNome();

            header('Location: minhas_compras.php');
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
    <title>Login de Cliente - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos específicos para login de cliente se necessário */
        body.centered-content { justify-content: center; align-items: center; }
        .container { max-width: 400px; }
        .links-navegacao a { margin: 0 5px; } /* Ajuste de margem se necessário */
    </style>
</head>
<body class="centered-content">
    <div class="container">
        <h1>Login de Cliente</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="login_cliente.php" method="POST">
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

        <div class="links-navegacao">
            <p>Ainda não tem uma conta? <a href="cadastro_cliente.php">Cadastre-se aqui</a></p>
            <p><a href="login.php">Acesso para Vendedores/Admin</a></p>
            <p><a href="listar_produtos_publico.php">Voltar para a Vitrine</a></p> </div>
    </div>
</body>
</html>