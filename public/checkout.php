<?php
// public/checkout.php - Resumo do Carrinho e Seleção de Pagamento

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Produto;

// --- 1. Proteção: Apenas clientes logados podem acessar o checkout ---
if (!isset($_SESSION['id_cliente_logado'])) {
    $_SESSION['mensagem_publica'] = 'Você precisa estar logado como cliente para finalizar uma compra.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: login_cliente.php');
    exit();
}

$carrinho = $_SESSION['carrinho'] ?? [];
$itensParaCheckout = []; // Irá armazenar os detalhes completos dos produtos no carrinho
$totalCarrinho = 0;
$mensagem = '';
$tipoMensagem = '';

// --- 2. Validação e Montagem dos Itens do Carrinho para Checkout ---
if (empty($carrinho)) {
    $_SESSION['mensagem_publica'] = 'Seu carrinho está vazio. Adicione produtos antes de prosseguir para o checkout.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: listar_produtos_publico.php');
    exit();
}

foreach ($carrinho as $idProd => $qtd) {
    $produto = Produto::buscarPorId($idProd);

    if (!$produto) {
        // Se o produto não existe, remova-o do carrinho e informe o erro
        unset($_SESSION['carrinho'][$idProd]);
        $mensagem = 'Um produto no seu carrinho não está mais disponível e foi removido.';
        $tipoMensagem = 'erro';
        // Redirecionar para recarregar o carrinho e mostrar a mensagem
        header('Location: ver_carrinho.php');
        exit();
    }

    // Re-validação de estoque e reserva (similar à lógica de detalhes_produto.php mas não é a mesma)
    $quantidadeDisponivel = $produto->obterQuantidadeDisponivel();
    $quantidadeReservada = $produto->obterQuantidadeReservada();
    $dataReserva = $produto->obterDataReserva();
    $reservadoPorClienteId = $produto->obterReservadoPorClienteId();
    $tempoReservaEmSegundos = 120;
    $agora = time();
    $idClienteLogado = $_SESSION['id_cliente_logado'];

    $ehItemReservadoParaMim = ($quantidadeReservada > 0 && $reservadoPorClienteId === $idClienteLogado);
    $reservaExpirou = ($quantidadeReservada > 0 && $dataReserva !== null && ($agora - strtotime($dataReserva)) > $tempoReservaEmSegundos);

    $motivoIndisponibilidade = '';

    if ($reservaExpirou) {
        // A reserva expirou, o item não está disponível. Libere a reserva no DB.
        $produto->definirQuantidadeDisponivel($quantidadeDisponivel + $quantidadeReservada);
        $produto->definirQuantidadeReservada(0);
        $produto->definirDataReserva(null);
        $produto->definirReservadoPorClienteId(null);
        $produto->salvar();
        $motivoIndisponibilidade = 'A reserva do produto "' . htmlspecialchars($produto->obterNome()) . '" expirou.';
    } elseif ($ehItemReservadoParaMim) {
        // Reservado para mim, mas só posso comprar 1 unidade
        if ($qtd !== 1) {
            $motivoIndisponibilidade = 'Você só pode comprar 1 unidade do item "' . htmlspecialchars($produto->obterNome()) . '" que está reservado para você.';
        }
    } elseif ($qtd > $quantidadeDisponivel) {
        // Quantidade solicitada maior que a disponível
        $motivoIndisponibilidade = 'A quantidade de "' . htmlspecialchars($produto->obterNome()) . '" excede o estoque disponível.';
    } elseif ($quantidadeDisponivel === 0 && !$ehItemReservadoParaMim) {
        // Esgotado e não é reservado para mim
        $motivoIndisponibilidade = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" esgotado ou não disponível.';
    } elseif ($quantidadeReservada > 0 && $reservadoPorClienteId !== null && $reservadoPorClienteId !== $idClienteLogado) {
        // Reservado por outro
        $motivoIndisponibilidade = 'Produto "' . htmlspecialchars($produto->obterNome()) . '" está temporariamente reservado por outro cliente.';
    }

    if (!empty($motivoIndisponibilidade)) {
        // Se o item não está disponível, remova-o do carrinho e informe o erro.
        unset($_SESSION['carrinho'][$idProd]);
        $_SESSION['mensagem_publica'] = 'Erro no carrinho: ' . $motivoIndisponibilidade;
        $_SESSION['tipo_mensagem_publica'] = 'erro';
        header('Location: ver_carrinho.php'); // Volta para o carrinho para o cliente ajustar
        exit();
    }

    // Se o item passou por todas as validações:
    $itensParaCheckout[] = [
        'id_produto' => $idProd,
        'produto' => $produto,
        'quantidade' => $qtd,
        'subtotal' => $produto->obterPreco() * $qtd
    ];
    $totalCarrinho += $produto->obterPreco() * $qtd;
}

if (empty($itensParaCheckout)) {
    // Se após a validação nenhum item sobrou no carrinho
    $_SESSION['mensagem_publica'] = 'Seu carrinho ficou vazio após a validação de estoque. Por favor, verifique os produtos.';
    $_SESSION['tipo_mensagem_publica'] = 'erro';
    header('Location: listar_produtos_publico.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container-checkout { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 700px; margin: 20px auto; }
        h1, h2 { text-align: center; color: var(--cor-fonte); margin-bottom: 20px; }
        .resumo-tabela { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .resumo-tabela th, .resumo-tabela td { border: 1px solid var(--cor-borda); padding: 10px; text-align: left; }
        .resumo-tabela th { background-color: var(--cor-primaria); color: white; }
        .resumo-tabela .subtotal, .resumo-tabela .total { font-weight: bold; text-align: right; }
        .resumo-tabela .total { font-size: 1.3em; color: var(--cor-sucesso); }
        .metodos-pagamento { margin-bottom: 20px; }
        .metodos-pagamento label { display: block; margin-bottom: 10px; font-size: 1.1em; cursor: pointer; }
        .metodos-pagamento input[type="radio"] { margin-right: 10px; transform: scale(1.2); }
        .links-navegacao { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;}
        .links-navegacao a { color: var(--cor-primaria); text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
        .mensagem-sucesso { color: var(--cor-sucesso); border: 1px solid var(--cor-sucesso); background-color: #d4edda; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .mensagem-erro { color: var(--cor-perigo); border: 1px solid var(--cor-perigo); background-color: #f8d7da; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
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

    <div class="container-checkout">
        <h1>Finalizar Compra</h1>
        <h2>Resumo do Pedido</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <table class="resumo-tabela">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço Unit.</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itensParaCheckout as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['produto']->obterNome()); ?></td>
                    <td>R$ <?php echo number_format($item['produto']->obterPreco(), 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($item['quantidade']); ?></td>
                    <td class="subtotal">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="total">Total do Pedido:</td>
                    <td class="total">R$ <?php echo number_format($totalCarrinho, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Escolha o Método de Pagamento</h2>
        <form action="processar_compra.php" method="POST">
            <?php foreach ($itensParaCheckout as $index => $item): ?>
                <input type="hidden" name="itens_carrinho[<?php echo $index; ?>][id_produto]" value="<?php echo htmlspecialchars($item['id_produto']); ?>">
                <input type="hidden" name="itens_carrinho[<?php echo $index; ?>][quantidade]" value="<?php echo htmlspecialchars($item['quantidade']); ?>">
            <?php endforeach; ?>

            <div class="metodos-pagamento">
                <label>
                    <input type="radio" name="metodo_pagamento" value="PIX" required> PIX
                </label>
                <label>
                    <input type="radio" name="metodo_pagamento" value="Boleto" required> Boleto Bancário
                </label>
                <label>
                    <input type="radio" name="metodo_pagamento" value="CartaoCredito" required> Cartão de Crédito
                </label>
            </div>

            <button type="submit">Confirmar Pagamento</button>
        </form>

        <div class="links-navegacao">
            <a href="ver_carrinho.php">Voltar para o Carrinho</a>
            <a href="listar_produtos_publico.php">Continuar Comprando</a>
        </div>
    </div>
</body>
</html>