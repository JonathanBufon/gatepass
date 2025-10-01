<?php
// public/dashboard_vendedor.php - Painel de Controle do Vendedor

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth; // Importa a classe Auth

// Chama o método de verificação de login no início da página
// Apenas usuários (vendedores) logados podem acessar este dashboard
Auth::verificarLogin(); 

// Se o código chegou até aqui, o usuário está logado
$nomeUsuario = $_SESSION['nome_usuario'] ?? 'Usuário'; // Obtém o nome do usuário da sessão
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Vendedor - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Você pode adicionar estilos específicos para o dashboard aqui ou em style.css */
        /* Mantendo o estilo base do container para consistência */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; text-align: center; }
        h1 { color: #333; margin-bottom: 20px; }
        p { margin-bottom: 10px; }
        .links-dashboard a {
            display: block; /* Cada link em sua própria linha */
            padding: 10px 15px;
            margin: 10px auto;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            max-width: 300px; /* Limita a largura dos botões */
        }
        .links-dashboard a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Painel do Vendedor, <?php echo htmlspecialchars($nomeUsuario); ?>!</h1>
        <p>Aqui você pode gerenciar seus produtos e acessar as ferramentas de vendas.</p>
        
        <div class="links-dashboard">
            <a href="editar_perfil.php">Editar Meu Perfil</a>
            <a href="listar_usuarios.php">Listar Usuários</a>
            <a href="cadastrar_produto.php">Cadastrar Novo Produto</a>
            <a href="listar_produtos.php">Meus Produtos</a>
            <a href="logout.php">Sair</a>
        </div>

    </div>
</body>
</html>