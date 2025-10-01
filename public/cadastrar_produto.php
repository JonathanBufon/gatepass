<?php
// public/cadastrar_produto.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth;
use GatePass\Models\Produto;
use GatePass\Utils\FileUpload;

Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$nomeUsuarioLogado = $_SESSION['nome_usuario'] ?? 'Usuário';

$nome = '';
$descricao = '';
$preco = '';
$quantidade = '';
$mensagem = '';
$tipoMensagem = '';

// Variáveis para armazenar URLs das imagens
$urlFotoPerfil = null;
$urlFotoFundo = null;

// Processa o formulário se foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco = $_POST['preco'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';

    $erros = [];

    // Validação dos dados
    if (empty($nome)) {
        $erros[] = 'O campo Nome é obrigatório.';
    }
    if (empty($preco) || !is_numeric($preco) || (float)$preco <= 0) {
        $erros[] = 'O Preço deve ser um número positivo.';
    }
    if (empty($quantidade) || !filter_var($quantidade, FILTER_VALIDATE_INT) || (int)$quantidade <= 0) {
        $erros[] = 'A Quantidade deve ser um número inteiro positivo.';
    }

    // --- Processamento dos Uploads de Imagens ---

    // Foto de Perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        try {
            $urlFotoPerfil = FileUpload::upload($_FILES['foto_perfil'], 'produtos');
        } catch (Exception $e) {
            $erros[] = 'Erro ao fazer upload da Foto de Perfil: ' . $e->getMessage();
        }
    } elseif (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Captura erros quando o arquivo foi tentado mas não foi OK_FILE (tamanho, tipo, etc.)
        $erros[] = 'Erro no upload da Foto de Perfil: ' . $_FILES['foto_perfil']['error'];
    }

    // Foto de Fundo
    if (isset($_FILES['foto_fundo']) && $_FILES['foto_fundo']['error'] === UPLOAD_ERR_OK) {
        try {
            $urlFotoFundo = FileUpload::upload($_FILES['foto_fundo'], 'produtos');
        } catch (Exception $e) {
            $erros[] = 'Erro ao fazer upload da Foto de Fundo: ' . $e->getMessage();
        }
    } elseif (isset($_FILES['foto_fundo']) && $_FILES['foto_fundo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $erros[] = 'Erro no upload da Foto de Fundo: ' . $_FILES['foto_fundo']['error'];
    }


    if (empty($erros)) {
        try {
            $preco = (float)$preco;
            $quantidade = (int)$quantidade;

            $novoProduto = new Produto(
                $idUsuarioLogado,
                htmlspecialchars($nome),
                $preco,
                $quantidade,
                $quantidade,
                empty($descricao) ? null : htmlspecialchars($descricao),
                0, null, null,
                $urlFotoPerfil, 
                $urlFotoFundo 
            );

            if ($novoProduto->salvar()) {
                $mensagem = 'Produto "' . $novoProduto->obterNome() . '" cadastrado com sucesso!';
                $tipoMensagem = 'sucesso';
                $nome = ''; $descricao = ''; $preco = ''; $quantidade = '';
                $urlFotoPerfil = null; // Limpa as URLs para o próximo formulário
                $urlFotoFundo = null;
            } else {
                $mensagem = 'Ocorreu um erro inesperado ao cadastrar o produto. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        $nome = htmlspecialchars($nome);
        $descricao = htmlspecialchars($descricao);
        $preco = htmlspecialchars((string)$preco);
        $quantidade = htmlspecialchars((string)$quantidade);

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
    <title>Cadastrar Produto - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="number"], textarea, input[type="file"] { /* input[type="file"] também estilizado */
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 80px; }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #218838; }
        .mensagem-sucesso { color: green; border: 1px solid green; background-color: #e6ffe6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .mensagem-erro { color: red; border: 1px solid red; background-color: #ffe6e6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .link-voltar { text-align: center; margin-top: 20px; }
        .link-voltar a { color: #007bff; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastrar Novo Produto</h1>
        <p>Olá, <?php echo htmlspecialchars($nomeUsuarioLogado); ?>! Preencha os detalhes do seu produto/ingresso.</p>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data"> <div class="form-group">
                <label for="nome">Nome do Produto/Ingresso:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($descricao); ?></textarea>
            </div>
            <div class="form-group">
                <label for="preco">Preço:</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0.01" value="<?php echo htmlspecialchars($preco); ?>" required>
            </div>
            <div class="form-group">
                <label for="quantidade">Quantidade Total:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" step="1" value="<?php echo htmlspecialchars($quantidade); ?>" required>
            </div>
            <div class="form-group">
                <label for="foto_perfil">Foto de Perfil do Ingresso (JPG, PNG, GIF, máx. 5MB):</label>
                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
            </div>
            <div class="form-group">
                <label for="foto_fundo">Foto de Fundo/Banner do Ingresso (JPG, PNG, GIF, máx. 5MB):</label>
                <input type="file" id="foto_fundo" name="foto_fundo" accept="image/jpeg,image/png,image/gif">
            </div>
            <button type="submit">Cadastrar Produto</button>
        </form>

        <div class="link-voltar">
            <a href="listar_produtos.php">Ver Meus Produtos</a> |
            <a href="dashboard_vendedor.php">Voltar para o Painel</a>
        </div>
    </div>
</body>
</html>