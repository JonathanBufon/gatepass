<?php
// public/excluir_perfil.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth;
use GatePass\Models\Usuario;

// 1. Verifica se o usuário está logado. Se não, redireciona.
Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$mensagem = '';
$tipoMensagem = '';

// Busca os dados do usuário logado para exibição (nome do usuário)
$usuario = Usuario::buscarPorId($idUsuarioLogado);

if (!$usuario) {
    // Se por algum motivo o usuário não for encontrado (já deletado, sessão inválida)
    header('Location: logout.php');
    exit();
}

// 2. Processa a submissão do formulário de confirmação de exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmacao = $_POST['confirmacao'] ?? '';

    if ($confirmacao === 'sim') {
        try {
            // Tenta excluir o usuário
            if ($usuario->excluir()) {
                // Se a exclusão foi bem-sucedida, destrói a sessão e redireciona
                $_SESSION = array();
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
                // Mensagem para ser exibida na página de login
                // Usar session temporária é uma forma de passar mensagens após redirecionamento
                session_start(); // Inicia a sessão para guardar a mensagem
                $_SESSION['logout_message'] = 'Sua conta foi excluída com sucesso.';
                session_write_close(); // Garante que a mensagem seja salva antes do redirecionamento

                header('Location: login.php');
                exit();
            } else {
                $mensagem = 'Ocorreu um erro ao excluir sua conta. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor ao tentar excluir: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        $mensagem = 'Você deve confirmar a exclusão para continuar.';
        $tipoMensagem = 'erro';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Perfil - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos (copiados para fins de teste) */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center;}
        h1 { color: #dc3545; margin-bottom: 20px; } /* Vermelho para alerta */
        p { margin-bottom: 20px; color: #555; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="checkbox"] { margin-right: 10px; }
        button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545; /* Botão de exclusão vermelho */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }
        button:hover { background-color: #c82333; }
        .mensagem-sucesso { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .mensagem-erro { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .link-voltar { margin-top: 20px; }
        .link-voltar a { color: #007bff; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Excluir Minha Conta</h1>
        <p>Você tem certeza que deseja excluir sua conta, **<?php echo htmlspecialchars($usuario->obterNome()); ?>**?</p>
        <p style="color: red; font-weight: bold;">Esta ação é irreversível!</p>
        <p>Todos os seus produtos e clientes cadastrados também serão excluídos.</p>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="excluir_perfil.php" method="POST">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirmacao" value="sim" required>
                    Sim, eu tenho certeza que quero excluir minha conta.
                </label>
            </div>
            <button type="submit">Excluir Minha Conta Permanentemente</button>
        </form>

        <div class="link-voltar">
            <a href="editar_perfil.php">Cancelar e Voltar</a>
        </div>
    </div>
</body>
</html>