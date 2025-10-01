<?php
// public/excluir_produto.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth;
use GatePass\Models\Produto;

// 1. Verifica se o usuário está logado.
Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$mensagem = '';
$tipoMensagem = '';
$produto = null; // Objeto Produto a ser excluído

// Obtém o ID do produto da URL
$idProduto = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $idProduto = (int)$_GET['id'];
    $produto = Produto::buscarPorId($idProduto);

    if (!$produto) {
        // Se o produto não existe, informa e redireciona
        $_SESSION['mensagem_produto'] = 'Produto não encontrado para exclusão.';
        $_SESSION['tipo_mensagem_produto'] = 'erro';
        header('Location: listar_produtos.php');
        exit();
    }
    // 2. Verifica permissão: se o produto pertence ao usuário logado
    elseif ($produto->obterIdUsuario() !== $idUsuarioLogado) {
        $_SESSION['mensagem_produto'] = 'Você não tem permissão para excluir este produto.';
        $_SESSION['tipo_mensagem_produto'] = 'erro';
        header('Location: listar_produtos.php');
        exit();
    }
} else {
    $_SESSION['mensagem_produto'] = 'ID do produto não fornecido ou inválido para exclusão.';
    $_SESSION['tipo_mensagem_produto'] = 'erro';
    header('Location: listar_produtos.php');
    exit();
}

// Processa a confirmação de exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmacao = $_POST['confirmacao'] ?? '';

    if ($confirmacao === 'sim') {
        try {
            if ($produto->excluir()) {
                $_SESSION['mensagem_produto'] = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" excluído com sucesso!';
                $_SESSION['tipo_mensagem_produto'] = 'sucesso';
                header('Location: listar_produtos.php'); // Redireciona para a lista após sucesso
                exit();
            } else {
                $mensagem = 'Ocorreu um erro ao excluir o produto. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
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
    <title>Excluir Produto - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center;}
        h1 { color: #dc3545; margin-bottom: 20px; }
        p { margin-bottom: 20px; color: #555; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="checkbox"] { margin-right: 10px; }
        button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545; /* Vermelho para exclusão */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }
        button:hover { background-color: #c82333; }
        .mensagem-sucesso { color: green; border: 1px solid green; background-color: #e6ffe6; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .mensagem-erro { color: red; border: 1px solid red; background-color: #ffe6e6; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .link-voltar { margin-top: 20px; }
        .link-voltar a { color: #007bff; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Excluir Produto</h1>
        <p>Você tem certeza que deseja excluir o produto "<strong><?php echo htmlspecialchars($produto->obterNome()); ?></strong>"?</p>
        <p style="color: red; font-weight: bold;">Esta ação é irreversível!</p>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="excluir_produto.php?id=<?php echo htmlspecialchars($idProduto); ?>" method="POST">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirmacao" value="sim" required>
                    Sim, eu tenho certeza que quero excluir este produto.
                </label>
            </div>
            <button type="submit">Excluir Produto Permanentemente</button>
        </form>

        <div class="link-voltar">
            <a href="listar_produtos.php">Cancelar e Voltar para Meus Produtos</a>
        </div>
    </div>
</body>
</html>