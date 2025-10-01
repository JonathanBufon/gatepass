<?php
// public/test_usuario.php 


/*

require_once __DIR__ . '/../vendor/autoload.php';

use GatePass\Models\Usuario;
use GatePass\Core\Database;

echo "<h1>Teste da Classe Usuario</h1>";

try {
    // --- OBTENDO A CONEXÃO COM A NOVA NOMENCLATURA ---
    $db = Database::obterInstancia(); // Usa o novo método obterInstancia()
    $pdo = $db->obterConexao();       // Usa o novo método obterConexao()

    echo "<p>Conexão com o banco de dados estabelecida com sucesso!</p>";

    // Exemplo rápido: tentar uma consulta simples (ainda usando $pdo)
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $quantidadeUsuarios = $stmt->fetchColumn();
    echo "<p>Número de usuários no banco de dados: " . $quantidadeUsuarios . "</p>";

    // ... (o restante do seu código de teste da classe Usuario permanece o mesmo,
    //      pois ele já chama os métodos de Usuario, não diretamente os de Database) ...

    // --- 1. Cadastrar um novo usuário ---
    echo "<h2>1. Cadastrando novo usuário...</h2>";
    $senhaPura = "minhasenha123";
    $senhaHash = Usuario::gerarHashSenha($senhaPura);

    $novoUsuario = new Usuario(
        null,
        "João da Silva",
        "joao.silva@example.com",
        $senhaHash
    );

    if ($novoUsuario->salvar()) {
        echo "<p>Usuário '{$novoUsuario->obterNome()}' cadastrado com sucesso! ID: {$novoUsuario->obterIdUsuario()}</p>";
        $idNovoUsuario = $novoUsuario->obterIdUsuario();
    } else {
        echo "<p style='color: red;'>Erro ao cadastrar usuário.</p>";
        $idNovoUsuario = null;
    }

    // --- 2. Buscar um usuário por ID (se cadastrado) ---
    if ($idNovoUsuario) {
        echo "<h2>2. Buscando usuário por ID {$idNovoUsuario}...</h2>";
        $usuarioEncontrado = Usuario::buscarPorId($idNovoUsuario);
        if ($usuarioEncontrado) {
            echo "<p>Usuário encontrado: Nome: {$usuarioEncontrado->obterNome()}, Email: {$usuarioEncontrado->obterEmail()}</p>";
            if (Usuario::verificarSenha($senhaPura, $usuarioEncontrado->obterSenha())) {
                echo "<p style='color: green;'>Verificação de senha OK!</p>";
            } else {
                echo "<p style='color: red;'>Verificação de senha FALHOU!</p>";
            }
        } else {
            echo "<p style='color: red;'>Usuário com ID {$idNovoUsuario} não encontrado.</p>";
        }
    }

    // --- 3. Buscar um usuário por Email ---
    echo "<h2>3. Buscando usuário por Email 'joao.silva@example.com'...</h2>";
    $usuarioPorEmail = Usuario::buscarPorEmail("joao.silva@example.com");
    if ($usuarioPorEmail) {
        echo "<p>Usuário encontrado por email: Nome: {$usuarioPorEmail->obterNome()}</p>";
    } else {
        echo "<p style='color: red;'>Usuário com email 'joao.silva@example.com' não encontrado.</p>";
    }

    // --- 4. Atualizar um usuário ---
    if ($usuarioEncontrado) {
        echo "<h2>4. Atualizando usuário {$usuarioEncontrado->obterNome()}...</h2>";
        $usuarioEncontrado->definirNome("João da Silva Atualizado");
        $usuarioEncontrado->definirEmail("joao.atualizado@example.com");
        if ($usuarioEncontrado->salvar()) {
            echo "<p>Usuário atualizado com sucesso! Novo Nome: {$usuarioEncontrado->obterNome()}</p>";
        } else {
            echo "<p style='color: red;'>Erro ao atualizar usuário.</p>";
        }
    }

    // --- 5. Listar todos os usuários ---
    echo "<h2>5. Listando todos os usuários...</h2>";
    $todosUsuarios = Usuario::buscarTodos();
    if (!empty($todosUsuarios)) {
        echo "<ul>";
        foreach ($todosUsuarios as $user) {
            echo "<li>ID: {$user->obterIdUsuario()}, Nome: {$user->obterNome()}, Email: {$user->obterEmail()}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhum usuário cadastrado.</p>";
    }

    // --- 6. Excluir um usuário (Opcional, use com cuidado) ---
    /*
    if ($idNovoUsuario) {
        echo "<h2>6. Excluindo usuário com ID {$idNovoUsuario}...</h2>";
        $usuarioParaExcluir = Usuario::buscarPorId($idNovoUsuario);
        if ($usuarioParaExcluir && $usuarioParaExcluir->excluir()) {
            echo "<p style='color: green;'>Usuário ID {$idNovoUsuario} excluído com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao excluir usuário ID {$idNovoUsuario}.</p>";
        }
    }
    */

/*

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Ocorreu um erro inesperado:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

*/