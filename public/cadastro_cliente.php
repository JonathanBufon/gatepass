<?php
// public/cadastro_cliente.php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
use GatePass\Models\Cliente;

$nome = '';
$email = '';
$senha = '';
$confirmarSenha = '';
$cpf = '';
$telefone = '';
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    $erros = [];

    // Validação dos campos
    if (empty($nome)) {
        $erros[] = 'O campo Nome é obrigatório.';
    }
    if (empty($email)) {
        $erros[] = 'O campo Email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O Email fornecido é inválido.';
    }
    if (empty($senha)) {
        $erros[] = 'O campo Senha é obrigatório.';
    } elseif (strlen($senha) < 6) {
        $erros[] = 'A Senha deve ter no mínimo 6 caracteres.';
    }
    if ($senha !== $confirmarSenha) {
        $erros[] = 'A Senha e a Confirmação de Senha não coincidem.';
    }

    if (!empty($cpf)) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) !== 11) {
            $erros[] = 'O CPF deve conter 11 dígitos.';
        }
    }
    if (!empty($telefone)) {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
    }

    // Verifica se o email já está cadastrado para um cliente
    if (empty($erros)) {
        if (Cliente::buscarPorEmail($email)) {
            $erros[] = 'Este Email já está cadastrado para outro cliente.';
        }
    }

    if (empty($erros)) {
        try {
            $senhaHash = Cliente::gerarHashSenha($senha);

            $novoCliente = new Cliente(
                htmlspecialchars($nome),
                htmlspecialchars($email),
                $senhaHash, // Senha já hashada
                empty($cpf) ? null : $cpf,
                empty($telefone) ? null : $telefone
            );

            if ($novoCliente->salvar()) {
                $mensagem = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                $tipoMensagem = 'sucesso';
                // Limpa os campos após o sucesso
                $nome = $email = $senha = $confirmarSenha = $cpf = $telefone = '';
            } else {
                $mensagem = 'Ocorreu um erro ao cadastrar o cliente. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = 'Este Email já está cadastrado para outro cliente.';
                $tipoMensagem = 'erro';
            } else {
                $mensagem = 'Erro no banco de dados: ' . $e->getMessage();
                $tipoMensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro interno do servidor: ' . $e->getMessage();
            $tipoMensagem = 'erro';
        }
    } else {
        // Mantém os valores preenchidos em caso de erro
        $nome = htmlspecialchars($nome);
        $email = htmlspecialchars($email);
        $cpf = htmlspecialchars($cpf);
        $telefone = htmlspecialchars($telefone);
        $senha = ''; // Senha e confirmação sempre limpas por segurança
        $confirmarSenha = '';

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
    <title>Cadastro de Cliente - GatePass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Estilos básicos (mantidos para fins de demonstração) */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
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
        .links-navegacao { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;}
        .links-navegacao a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .links-navegacao a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Cliente</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-<?php echo $tipoMensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_cliente.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <div class="form-group">
                <label for="cpf">CPF (opcional):</label>
                <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($cpf); ?>" placeholder="Apenas números">
            </div>
            <div class="form-group">
                <label for="telefone">Telefone (opcional):</label>
                <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" placeholder="Apenas números">
            </div>
            <button type="submit">Cadastrar</button>
        </form>

        <div class="links-navegacao">
            <p>Já tem uma conta? <a href="login_cliente.php">Faça Login</a></p>
            <a href="index.php">Voltar para Página Principal (Acesso de Vendedor)</a>
        </div>
    </div>
</body>
</html>