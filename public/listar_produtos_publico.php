<?php
// public/listar_produtos_publico.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Produto;

$produtos = Produto::buscarTodos();

$mensagem = '';
$tipoMensagem = '';
if (isset($_SESSION['mensagem_publica'])) {
    $mensagem = $_SESSION['mensagem_publica'];
    $tipoMensagem = $_SESSION['tipo_mensagem_publica'];
    unset($_SESSION['mensagem_publica']);
    unset($_SESSION['tipo_mensagem_publica']);
}

$nomeClienteLogado = $_SESSION['nome_cliente_logado'] ?? '';
$isClienteLogado = isset($_SESSION['id_cliente_logado']);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingressos e Produtos - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="listar_produtos_publico.php" class="logo">
            <img src="images/gate-pass-logo.png" alt="Logo GatePass">
        </a>
        <nav class="nav-links">
            <?php if ($isClienteLogado): ?>
                <span>Olá, <?php echo htmlspecialchars($nomeClienteLogado); ?>!</span>
                <a href="minhas_compras.php">Minhas Compras</a>
                <a href="ver_carrinho.php">Ver Carrinho (<?php echo count($_SESSION['carrinho'] ?? []); ?>)</a>
                <a href="logout_cliente.php" class="btn-secundario">Sair</a>
            <?php else: ?>
                <a href="login_cliente.php" class="btn-cliente">Login Cliente</a>
                <a href="cadastro_cliente.php" class="btn-cliente">Cadastre-se Cliente</a>
                <a href="ver_carrinho.php">Ver Carrinho (<?php echo count($_SESSION['carrinho'] ?? []); ?>)</a>
            <?php endif; ?>
            <a href="login.php" class="btn-secundario">Acesso Vendedor</a>
        </nav>
    </header>

    <div class="main-content">
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <p style="text-align: center; font-size: 1.2em; color: #555;">Nenhum ingresso ou produto disponível no momento.</p>
        <?php else: ?>
            <div class="produto-grid">
                <?php foreach ($produtos as $produto):
                    $disponivel = $produto->obterQuantidadeDisponivel() > 0;
                    $urlFoto = $produto->obterUrlFotoPerfil() ? htmlspecialchars($produto->obterUrlFotoPerfil()) : '';
                ?>
                    <div class="produto-card">
                        <div class="produto-card-imagem">
                            <?php if ($urlFoto): ?>
                                <img src="<?php echo $urlFoto; ?>" alt="Foto de Perfil do Ingresso">
                            <?php else: ?>
                                <div class="sem-imagem">Sem Imagem</div>
                            <?php endif; ?>
                        </div>
                        <div class="produto-card-content">
                            <div>
                                <h2><?php echo htmlspecialchars($produto->obterNome()); ?></h2>
                                <p class="descricao"><?php echo htmlspecialchars($produto->obterDescricao() ?? 'Sem descrição.'); ?></p>
                            </div>
                            <div class="info-compra">
                                <div class="preco-info">
                                    <p class="preco">R$ <?php echo number_format($produto->obterPreco(), 2, ',', '.'); ?></p>
                                    <p class="quantidade-disponivel">
                                        <?php echo htmlspecialchars($produto->obterQuantidadeDisponivel()); ?> unidades disponíveis
                                    </p>
                                </div>
                                <?php if ($disponivel): ?>
                                    <form action="adicionar_carrinho.php" method="POST">
                                        <input type="hidden" name="id_produto" value="<?php echo $produto->obterIdProduto(); ?>">
                                        <input type="hidden" name="quantidade" value="1">
                                        <button type="submit" class="btn-comprar">Adicionar ao Carrinho</button>
                                    </form>
                                    <a href="detalhes_produto.php?id=<?php echo $produto->obterIdProduto(); ?>" class="btn-detalhes">Ver Detalhes</a>
                                <?php else: ?>
                                    <a href="#" class="btn-comprar indisponivel">Esgotado</a>
                                    <a href="detalhes_produto.php?id=<?php echo $produto->obterIdProduto(); ?>" class="btn-detalhes">Ver Detalhes</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>