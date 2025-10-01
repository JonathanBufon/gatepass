<?php
// public/ver_carrinho.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Produto;

$carrinho = $_SESSION['carrinho'] ?? [];
$itensCarrinho = [];
$totalCarrinho = 0;
$mensagem = '';
$tipoMensagem = '';

// Recupera mensagens de outras páginas (ex: adicionar_carrinho.php)
if (isset($_SESSION['mensagem_publica'])) {
    $mensagem = $_SESSION['mensagem_publica'];
    $tipoMensagem = $_SESSION['tipo_mensagem_publica'];
    unset($_SESSION['mensagem_publica']);
    unset($_SESSION['tipo_mensagem_publica']);
}

// Processa ações do carrinho (atualizar quantidade, remover)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $idProdutoAcao = $_POST['id_produto'] ?? null;
        if (filter_var($idProdutoAcao, FILTER_VALIDATE_INT)) {
            $idProdutoAcao = (int)$idProdutoAcao;

            if ($_POST['acao'] === 'atualizar') {
                $novaQuantidade = $_POST['quantidade'] ?? 0;
                if (filter_var($novaQuantidade, FILTER_VALIDATE_INT) && $novaQuantidade > 0) {
                    $produto = Produto::buscarPorId($idProdutoAcao);
                    if ($produto && $novaQuantidade <= $produto->obterQuantidadeDisponivel()) {
                        $_SESSION['carrinho'][$idProdutoAcao] = $novaQuantidade;
                        $mensagem = 'Quantidade atualizada para o produto "' . htmlspecialchars($produto->obterNome()) . '".';
                        $tipoMensagem = 'sucesso';
                    } else {
                        $mensagem = 'Não há estoque suficiente para esta quantidade ou produto inválido.';
                        $tipoMensagem = 'erro';
                    }
                } else {
                    $mensagem = 'Quantidade inválida.';
                    $tipoMensagem = 'erro';
                }
            } elseif ($_POST['acao'] === 'remover') {
                if (isset($_SESSION['carrinho'][$idProdutoAcao])) {
                    $produtoRemovido = Produto::buscarPorId($idProdutoAcao);
                    unset($_SESSION['carrinho'][$idProdutoAcao]);
                    $mensagem = 'Produto "' . htmlspecialchars($produtoRemovido ? $produtoRemovido->obterNome() : 'ID ' . $idProdutoAcao) . '" removido do carrinho.';
                    $tipoMensagem = 'sucesso';
                } else {
                    $mensagem = 'Produto não encontrado no carrinho.';
                    $tipoMensagem = 'erro';
                }
            }
        } else {
            $mensagem = 'ID do produto para ação inválido.';
            $tipoMensagem = 'erro';
        }
    }
    // Redireciona para evitar reenvio do formulário
    header('Location: ver_carrinho.php');
    exit();
}


// Monta os itens do carrinho com detalhes do produto
if (!empty($carrinho)) {
    foreach ($carrinho as $idProd => $qtd) {
        $produto = Produto::buscarPorId($idProd);
        if ($produto) {
            // Segunda verificação da disponibilidade do ingresso para evitar compra de item esgotado no carrinho
            if ($qtd > $produto->obterQuantidadeDisponivel()) {
                $mensagem = 'Atenção: A quantidade de "' . htmlspecialchars($produto->obterNome()) . '" no seu carrinho excede o estoque disponível (' . htmlspecialchars($produto->obterQuantidadeDisponivel()) . ' unid.). Ajuste a quantidade.';
                $tipoMensagem = 'erro';
            }
            $itensCarrinho[] = [
                'produto' => $produto,
                'quantidade' => $qtd,
                'subtotal' => $produto->obterPreco() * $qtd
            ];
            $totalCarrinho += $produto->obterPreco() * $qtd;
        } else {
            // Produto no carrinho mas não existe mais no DB
            $mensagem = 'Um produto no seu carrinho não está mais disponível e foi removido.';
            $tipoMensagem = 'erro';
            unset($_SESSION['carrinho'][$idProd]); // Remove o item inválido do carrinho
            header('Location: ver_carrinho.php');
            exit();
        }
    }
}
// As mensagens que não vieram de POST são exibidas abaixo
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container-carrinho {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
        }
        .container-carrinho h1 {
            text-align: center;
            color: var(--cor-primaria);
            margin-bottom: 25px;
        }
        .carrinho-tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .carrinho-tabela th, .carrinho-tabela td {
            border: 1px solid var(--cor-borda);
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        .carrinho-tabela th {
            background-color: var(--cor-primaria);
            color: white;
        }
        .carrinho-tabela tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .carrinho-tabela .subtotal, .carrinho-tabela .total {
            font-weight: bold;
            text-align: right;
        }
        .carrinho-tabela .total {
            font-size: 1.3em;
            color: var(--cor-sucesso);
        }
        .carrinho-tabela input[type="number"] {
            width: 70px;
            padding: 5px;
            border: 1px solid var(--cor-borda);
            border-radius: 4px;
            font-size: 0.9em;
            text-align: center;
        }
        .carrinho-tabela button {
            padding: 8px 12px;
            font-size: 0.9em;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            color: white;
            background-color: var(--cor-secundaria);
            transition: background-color 0.2s ease;
        }
        .carrinho-tabela button:hover {
            background-color: var(--cor-secundaria-hover);
        }
        .carrinho-tabela .btn-remover {
            background-color: var(--cor-perigo);
        }
        .carrinho-tabela .btn-remover:hover {
            background-color: var(--cor-perigo-hover);
        }
        .botoes-carrinho {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            gap: 20px;
        }
        .botoes-carrinho a, .botoes-carrinho button {
            flex-grow: 1; /* Faz os botões dividirem o espaço */
            padding: 12px 20px;
            font-size: 1.1em;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .botoes-carrinho a.btn-continuar-comprando {
            background-color: var(--cor-secundaria);
            color: white;
        }
        .botoes-carrinho a.btn-continuar-comprando:hover {
            background-color: var(--cor-secundaria-hover);
        }
        .botoes-carrinho button.btn-finalizar-compra {
            background-color: var(--cor-sucesso);
            color: white;
        }
        .botoes-carrinho button.btn-finalizar-compra:hover {
            background-color: var(--cor-sucesso-hover);
        }
    </style>
</head>
<body>
    <header class="site-header">
        <a href="listar_produtos_publico.php" class="logo">GatePass</a>
        <nav class="nav-links">
            <?php if (isset($_SESSION['id_cliente_logado'])): ?>
                <span>Olá, <?php echo htmlspecialchars($_SESSION['nome_cliente_logado']); ?>!</span>
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

    <div class="container-carrinho">
        <h1>Meu Carrinho de Compras</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($itensCarrinho)): ?>
            <p style="text-align: center;">Seu carrinho está vazio.</p>
            <div class="links-navegacao">
                <a href="listar_produtos_publico.php">Comece a Comprar!</a>
            </div>
        <?php else: ?>
            <table class="carrinho-tabela">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço Unit.</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itensCarrinho as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['produto']->obterNome()); ?></td>
                        <td>R$ <?php echo number_format($item['produto']->obterPreco(), 2, ',', '.'); ?></td>
                        <td>
                            <form action="ver_carrinho.php" method="POST" style="display: flex; align-items: center; gap: 5px;">
                                <input type="hidden" name="id_produto" value="<?php echo $item['produto']->obterIdProduto(); ?>">
                                <input type="number" name="quantidade" value="<?php echo htmlspecialchars($item['quantidade']); ?>"
                                    min="1" max="<?php echo htmlspecialchars($item['produto']->obterQuantidadeDisponivel()); ?>">
                                <button type="submit" name="acao" value="atualizar">Atualizar</button>
                            </form>
                        </td>
                        <td class="subtotal">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                        <td>
                            <form action="ver_carrinho.php" method="POST">
                                <input type="hidden" name="id_produto" value="<?php echo $item['produto']->obterIdProduto(); ?>">
                                <button type="submit" name="acao" value="remover" class="btn-remover">Remover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="total">Total do Carrinho:</td>
                        <td class="total">R$ <?php echo number_format($totalCarrinho, 2, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div class="botoes-carrinho">
                <a href="listar_produtos_publico.php" class="btn-continuar-comprando">Continuar Comprando</a>
                <button type="button" onclick="window.location.href='checkout.php';" class="btn-finalizar-compra">Finalizar Compra</button>
                </div>
        <?php endif; ?>
    </div>
</body>
</html>