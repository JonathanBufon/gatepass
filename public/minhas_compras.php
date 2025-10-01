<?php
// public/minhas_compras.php - 'Dashboard' do Cliente

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Cliente;
use GatePass\Models\Produto;
use GatePass\Models\Compra;

if (!isset($_SESSION['id_cliente_logado'])) {
    header('Location: login_cliente.php');
    exit();
}

$idClienteLogado = $_SESSION['id_cliente_logado'];
$nomeCliente = $_SESSION['nome_cliente_logado'] ?? 'Cliente';

$comprasCliente = Compra::buscarPorCliente($idClienteLogado);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial="scale=1.0">
    <title>Minhas Compras - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos para a listagem de compras */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 900px; margin: 20px auto; }
        h1 { text-align: center; color: var(--cor-primaria); margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid var(--cor-borda); }
        th, td { padding: 12px 15px; text-align: left; vertical-align: middle; }
        th { background-color: var(--cor-primaria); color: white; }
        tr:nth-child(even) { background-color: #f8f8f8; }
        tr:hover { background-color: #f1f1f1; }
        .links-navegacao { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;}
        .links-navegacao a { color: var(--cor-primaria); text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
        .btn-pdf {
            background-color: #dc3545; /* Vermelho para PDF */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            display: inline-block;
        }
        .btn-pdf:hover {
            background-color: #c82333;
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


    <div class="container">
        <h1>Minhas Compras</h1>
        <p>Estas são suas compras registradas:</p>

        <?php if (empty($comprasCliente)): ?>
            <p>Você ainda não realizou nenhuma compra.</p>
            <p><a href="listar_produtos_publico.php">Explore nossos ingressos e produtos!</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Compra</th>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Valor Total</th>
                        <th>Método Pgto.</th>
                        <th>Data da Compra</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($comprasCliente as $compra):
                        $produtoComprado = Produto::buscarPorId($compra->obterIdProduto());
                        $nomeProduto = $produtoComprado ? $produtoComprado->obterNome() : 'Produto Desconhecido';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($compra->obterIdCompra()); ?></td>
                        <td><?php echo htmlspecialchars($nomeProduto); ?></td>
                        <td><?php echo htmlspecialchars($compra->obterQuantidadeComprada()); ?></td>
                        <td>R$ <?php echo number_format($compra->obterValorTotal(), 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($compra->obterMetodoPagamento()); ?></td>
                        <td><?php echo htmlspecialchars($compra->obterDataCompra()); ?></td>
                        <td>
                            <a href="gerar_ingresso_pdf.php?id_compra=<?php echo $compra->obterIdCompra(); ?>" target="_blank" class="btn-pdf">Gerar Ingresso</a>
                        </td> </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="links-navegacao">
            <a href="listar_produtos_publico.php">Ver Mais Ingressos/Produtos</a>
            <a href="logout_cliente.php">Sair da Conta</a>
        </div>
    </div>
</body>
</html>