<?php
// public/listar_usuarios.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Core\Auth; 
use GatePass\Models\Usuario;

// 1. Verifica se o usuário está logado. Se não, redireciona.
Auth::verificarLogin();

// 2. Busca todos os usuários do banco de dados
$usuarios = Usuario::buscarTodos();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Usuários - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos para a listagem */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 800px; margin: 20px auto; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
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
        .link-voltar { text-align: center; margin-top: 20px; }
        .link-voltar a { color: #007bff; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Listagem de Usuários</h1>

        <?php if (empty($usuarios)): ?>
            <p>Nenhum usuário cadastrado no sistema.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data de Cadastro</th>
                        <th>Ativo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario->obterIdUsuario()); ?></td>
                        <td><?php echo htmlspecialchars($usuario->obterNome()); ?></td>
                        <td><?php echo htmlspecialchars($usuario->obterEmail()); ?></td>
                        <td><?php echo htmlspecialchars($usuario->obterDataCadastro()); ?></td>
                        <td><?php echo $usuario->estaAtivo() ? 'Sim' : 'Não'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="link-voltar">
            <a href="index.php">Voltar para a Página Principal</a>
            <p><a href="logout.php">Sair</a></p>
        </div>
    </div>
</body>
</html>