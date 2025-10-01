<?php
// public/test_db.php 

// require_once __DIR__ . '/../vendor/autoload.php'; // Inclui o autoloader do Composer

// use GatePass\Core\Database; // Importa a classe Database do namespace correto

// echo "<h1>Teste da Conexão com o Banco de Dados</h1>";

// try {
//     // Obtém a instância da classe Database usando o método em português
//     $instanciaDb = Database::obterInstancia(); 
    
//     // Obtém o objeto PDO da conexão usando o método em português
//     $pdo = $instanciaDb->obterConexao();   

//     echo "<p>Conexão com o banco de dados estabelecida com sucesso!</p>";

//     // Exemplo rápido: tentar uma consulta simples
//     $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
//     $quantidadeUsuarios = $stmt->fetchColumn();
//     echo "<p>Número de usuários no banco de dados: " . $quantidadeUsuarios . "</p>";

// } catch (Exception $e) {
//     // Captura e exibe qualquer exceção que ocorra durante a conexão ou consulta
//     echo "<h2 style='color: red;'>Ocorreu um erro:</h2>";
//     echo "<pre>" . $e->getMessage() . "</pre>";
// }
