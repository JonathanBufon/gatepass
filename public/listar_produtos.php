<?php
// public/listar_produtos.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth; 
use GatePass\Models\Produto;

// 1. Verifica se o usuário está logado. Se não, redireciona.
Auth::verificarLogin();

$idUsuarioLogado = $_SESSION['id_usuario'];
$nomeUsuarioLogado = $_SESSION['nome_usuario'] ?? 'Usuário';

// 2. Busca todos os produtos CADASTRADOS PELO USUÁRIO LOGADO
// Usamos o método buscarTodos passando o ID do usuário logado ;)
$produtos = Produto::buscarTodos($idUsuarioLogado);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Produtos - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos para a listagem de produtos */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 900px; margin: 20px auto; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .mensagem { text-align: center; margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .mensagem-sucesso { color: green; border: 1px solid green; background-color: #e6ffe6; }
        .mensagem-erro { color: red; border: 1px solid red; background-color: #ffe6e6; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .link-acao {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .link-acao:hover {
            text-decoration: underline;
        }
        .link-acao.excluir {
            color: #dc3545;
        }
        .botoes-acao {
            display: flex; /* Para manter os links de ação na mesma linha */
            gap: 5px;
        }
        .links-navegacao { text-align: center; margin-top: 20px; }
        .links-navegacao a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Meus Produtos Cadastrados</h1>
        <p>Olá, <?php echo htmlspecialchars($nomeUsuarioLogado); ?>! Aqui estão os produtos/ingressos que você cadastrou.</p>

        <?php
        // Adicionar mensagens de sucesso ou erro, se houver (ex: após exclusão/edição)
        // Você pode usar uma variável de sessão temporária para isso.
        if (isset($_SESSION['mensagem_produto'])) {
            echo '<div class="mensagem ' . htmlspecialchars($_SESSION['tipo_mensagem_produto']) . '">' . htmlspecialchars($_SESSION['mensagem_produto']) . '</div>';
            unset($_SESSION['mensagem_produto']);
            unset($_SESSION['tipo_mensagem_produto']);
        }
        ?>

        <?php if (empty($produtos)): ?>
            <p>Você ainda não cadastrou nenhum produto. <a href="cadastrar_produto.php">Cadastre um agora!</a></p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Total</th>
                        <th>Disponível</th>
                        <th>Reservado</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produto->obterIdProduto()); ?></td>
                        <td><?php echo htmlspecialchars($produto->obterNome()); ?></td>
                        <td>R$ <?php echo number_format($produto->obterPreco(), 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($produto->obterQuantidadeTotal()); ?></td>
                        <td><?php echo htmlspecialchars($produto->obterQuantidadeDisponivel()); ?></td>
                        <td><?php echo htmlspecialchars($produto->obterQuantidadeReservada()); ?></td>
                        <td><?php echo htmlspecialchars($produto->obterDescricao() ?? 'N/A'); ?></td>
                        <td class="botoes-acao">
                            <a href="editar_produto.php?id=<?php echo $produto->obterIdProduto(); ?>" class="link-acao">Editar</a>
                            <a href="excluir_produto.php?id=<?php echo $produto->obterIdProduto(); ?>" class="link-acao excluir">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="links-navegacao">
            <a href="cadastrar_produto.php">Cadastrar Novo Produto</a>
            <a href="index.php">Voltar para a Página Principal</a>
            <a href="logout.php">Sair</a>
        </div>
    </div>
</body>
</html>