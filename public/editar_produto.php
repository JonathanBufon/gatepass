<?php
// public/editar_produto.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth;
use GatePass\Models\Produto;
use GatePass\Utils\FileUpload;

Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$mensagem = '';
$tipoMensagem = '';
$produto = null;

// --- Lógica para buscar o produto ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $idProduto = (int)$_GET['id'];
    $produto = Produto::buscarPorId($idProduto);

    if (!$produto) {
        $mensagem = 'Produto não encontrado.';
        $tipoMensagem = 'erro';
        header('Location: listar_produtos.php');
        exit();
    }
    elseif ($produto->obterIdUsuario() !== $idUsuarioLogado) {
        $mensagem = 'Você não tem permissão para editar este produto.';
        $tipoMensagem = 'erro';
        header('Location: listar_produtos.php');
        exit();
    }
} else {
    $mensagem = 'ID do produto não fornecido ou inválido.';
    $tipoMensagem = 'erro';
    header('Location: listar_produtos.php');
    exit();
}

// Variáveis para preencher o formulário (dados atuais do produto ou do POST)
$nome = $produto->obterNome();
$descricao = $produto->obterDescricao();
$preco = (string)$produto->obterPreco();
$quantidade = (string)$produto->obterQuantidadeTotal();
$urlFotoPerfilAtual = $produto->obterUrlFotoPerfil(); // URL da foto atual (para exibição)
$urlFotoFundoAtual = $produto->obterUrlFotoFundo();   // URL da foto de fundo atual

// --- Lógica para processar a submissão do formulário ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $produto) {
    $nomeNovo = $_POST['nome'] ?? '';
    $descricaoNova = $_POST['descricao'] ?? '';
    $precoNovo = $_POST['preco'] ?? '';
    $quantidadeNova = $_POST['quantidade'] ?? '';

    $erros = [];

    // Validação dos dados
    if (empty($nomeNovo)) {
        $erros[] = 'O campo Nome é obrigatório.';
    }
    if (empty($precoNovo) || !is_numeric($precoNovo) || (float)$precoNovo <= 0) {
        $erros[] = 'O Preço deve ser um número positivo.';
    }
    if (empty($quantidadeNova) || !filter_var($quantidadeNova, FILTER_VALIDATE_INT) || (int)$quantidadeNova <= 0) {
        $erros[] = 'A Quantidade deve ser um número inteiro positivo.';
    }

    // --- Processamento dos Uploads de Imagens na Edição ---
    $urlFotoPerfilUpload = $urlFotoPerfilAtual; // Assume a foto atual se não for enviado nova
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        try {
            $urlFotoPerfilUpload = FileUpload::upload($_FILES['foto_perfil'], 'produtos');
        } catch (Exception $e) {
            $erros[] = 'Erro ao fazer upload da Nova Foto de Perfil: ' . $e->getMessage();
        }
    } elseif (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
         $erros[] = 'Erro no upload da Nova Foto de Perfil: ' . $_FILES['foto_perfil']['error'];
    }

    $urlFotoFundoUpload = $urlFotoFundoAtual; // Assume a foto atual se não for enviado nova
    if (isset($_FILES['foto_fundo']) && $_FILES['foto_fundo']['error'] === UPLOAD_ERR_OK) {
        try {
            $urlFotoFundoUpload = FileUpload::upload($_FILES['foto_fundo'], 'produtos');
        } catch (Exception $e) {
            $erros[] = 'Erro ao fazer upload da Nova Foto de Fundo: ' . $e->getMessage();
        }
    } elseif (isset($_FILES['foto_fundo']) && $_FILES['foto_fundo']['error'] !== UPLOAD_ERR_NO_FILE) {
         $erros[] = 'Erro no upload da Nova Foto de Fundo: ' . $_FILES['foto_fundo']['error'];
    }


    if (empty($erros)) {
        try {
            $precoNovo = (float)$precoNovo;
            $quantidadeNova = (int)$quantidadeNova;

            $produto->definirNome(htmlspecialchars($nomeNovo));
            $produto->definirDescricao(empty($descricaoNova) ? null : htmlspecialchars($descricaoNova));
            $produto->definirPreco($precoNovo);
            $produto->definirQuantidadeTotal($quantidadeNova);
            $produto->definirQuantidadeDisponivel((int)$quantidadeNova - $produto->obterQuantidadeReservada());

            $produto->definirUrlFotoPerfil($urlFotoPerfilUpload);
            $produto->definirUrlFotoFundo($urlFotoFundoUpload); 

            if ($produto->salvar()) {
                $mensagem = 'Produto "' . $produto->obterNome() . '" atualizado com sucesso!';
                $tipoMensagem = 'sucesso';
                // Re-popula as variáveis do formulário com os dados atualizados
                $nome = $produto->obterNome();
                $descricao = $produto->obterDescricao();
                $preco = (string)$produto->obterPreco();
                $quantidade = (string)$produto->obterQuantidadeTotal();
                $urlFotoPerfilAtual = $produto->obterUrlFotoPerfil(); // Atualiza para exibir a nova URL
                $urlFotoFundoAtual = $produto->obterUrlFotoFundo();   // Atualiza para exibir a nova URL
            } else {
                $mensagem = 'Ocorreu um erro ao atualizar o produto. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        $nome = htmlspecialchars($nomeNovo);
        $descricao = htmlspecialchars($descricaoNova);
        $preco = htmlspecialchars((string)$precoNovo);
        $quantidade = htmlspecialchars((string)$quantidadeNova);
        // Manter as URLs atuais se houver erro no upload, para não perder o que já tinha.
        $urlFotoPerfilAtual = $urlFotoPerfilAtual;
        $urlFotoFundoAtual = $urlFotoFundoAtual;

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
    <title>Editar Produto - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos similares aos de cadastrar_produto.php */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="number"], textarea, input[type="file"] { /* input[type="file"] estilizado */
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 80px; }
        .imagem-atual { margin-top: 10px; text-align: center; }
        .imagem-atual img { max-width: 100px; height: auto; border: 1px solid #eee; border-radius: 4px; }
        button {
            width: 100%;
            padding: 10px;
            background-color: #ffc107;
            color: #333;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #e0a800; }
        .mensagem-sucesso { color: green; border: 1px solid green; background-color: #e6ffe6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .mensagem-erro { color: red; border: 1px solid red; background-color: #ffe6e6; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .links-navegacao { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;}
        .links-navegacao a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Produto</h1>
        <p>Você está editando o produto ID: <?php echo htmlspecialchars($idProduto ?? 'N/A'); ?></p>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="editar_produto.php?id=<?php echo htmlspecialchars($idProduto ?? ''); ?>" method="POST" enctype="multipart/form-data"> <div class="form-group">
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
                <?php if ($urlFotoPerfilAtual): ?>
                    <div class="imagem-atual">
                        <p>Foto atual:</p>
                        <img src="<?php echo htmlspecialchars($urlFotoPerfilAtual); ?>" alt="Foto de Perfil Atual">
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="foto_fundo">Foto de Fundo/Banner do Ingresso (JPG, PNG, GIF, máx. 5MB):</label>
                <input type="file" id="foto_fundo" name="foto_fundo" accept="image/jpeg,image/png,image/gif">
                <?php if ($urlFotoFundoAtual): ?>
                    <div class="imagem-atual">
                        <p>Banner atual:</p>
                        <img src="<?php echo htmlspecialchars($urlFotoFundoAtual); ?>" alt="Foto de Fundo Atual">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">Atualizar Produto</button>
        </form>

        <div class="links-navegacao">
            <a href="listar_produtos.php">Voltar para Meus Produtos</a>
            <a href="dashboard_vendedor.php">Voltar para o Painel</a>
        </div>
    </div>
</body>
</html>