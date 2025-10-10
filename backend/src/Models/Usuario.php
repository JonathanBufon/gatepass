<?php
// src/Models/Usuario.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use PDOException;

class Usuario
{
    // Propriedades do usuário
    private ?int $id_usuario = null; 
    private string $nome; 
    private string $email;
    private string $senha;
    private string $data_cadastro;
    private bool $ativo = true;

    private PDO $pdo; 

    
    public function __construct(?int $id_usuario = null, string $nome = '', string $email = '', string $senha = '', string $data_cadastro = '', bool $ativo = true)
    {
        $this->id_usuario = $id_usuario;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;

        // Se data_cadastro não for fornecida, usa a data/hora atual
        $this->data_cadastro = $data_cadastro ?: date('Y-m-d H:i:s');
        $this->ativo = $ativo;

        // Obtém a instância da conexão PDO do Singleton Database
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Métodos Getters ---
    public function obterIdUsuario(): ?int { return $this->id_usuario; }
    public function obterNome(): string { return $this->nome; }
    public function obterEmail(): string { return $this->email; }
    public function obterSenha(): string { return $this->senha; }
    public function obterDataCadastro(): string { return $this->data_cadastro; }
    public function estaAtivo(): bool { return $this->ativo; }


    // --- Métodos Setters ---
    public function definirNome(string $nome): void { $this->nome = $nome; }
    public function definirEmail(string $email): void { $this->email = $email; }


    // O setter de senha é usado com a senha JÁ HASHADA
    public function definirSenha(string $senha): void { $this->senha = $senha; }
    public function definirAtivo(bool $ativo): void { $this->ativo = $ativo; }


    // --- Métodos de Interação com o Banco de Dados (CRUD) ---
    public function salvar(): bool
    {
        if ($this->id_usuario === null) {
            // Inserir um novo usuário
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, data_cadastro, ativo) VALUES (:nome, :email, :senha, :data_cadastro, :ativo)");
            $sucesso = $stmt->execute([
                'nome' => $this->nome,
                'email' => $this->email,
                'senha' => $this->senha, 
                'data_cadastro' => $this->data_cadastro,
                'ativo' => $this->ativo
            ]);
            if ($sucesso) {
                $this->id_usuario = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            // Atualizar um usuário existente
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, ativo = :ativo WHERE id_usuario = :id_usuario");
            return $stmt->execute([
                'nome' => $this->nome,
                'email' => $this->email,
                'senha' => $this->senha, 
                'ativo' => $this->ativo,
                'id_usuario' => $this->id_usuario
            ]);
        }
    }

    public static function buscarPorId(int $id): ?Usuario
    {
        $pdo = Database::obterInstancia()->obterConexao(); // Obtém a conexão estaticamente
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
        $stmt->execute(['id_usuario' => $id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch em modo associativo

        if ($dados) {
            // Cria e retorna uma nova instância de Usuario com os dados do banco
            return new Usuario(
                $dados['id_usuario'],
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['data_cadastro'],
                (bool)$dados['ativo'] // Converte para booleano
            );
        }
        return null; // Usuário não encontrado
    }

    public static function buscarPorEmail(string $email): ?Usuario
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return new Usuario(
                $dados['id_usuario'],
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['data_cadastro'],
                (bool)$dados['ativo']
            );
        }
        return null;
    }

    // retorna true se o usuário foi excluído com sucesso, false caso contrário
    public function excluir(): bool
    {
        if ($this->id_usuario === null) {
            return false; 
        }
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        return $stmt->execute(['id_usuario' => $this->id_usuario]);
    }

   
    public static function buscarTodos(): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->query("SELECT * FROM usuarios");
        $usuarios = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $usuarios[] = new Usuario(
                $dados['id_usuario'],
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['data_cadastro'],
                (bool)$dados['ativo']
            );
        }
        return $usuarios;
    }

    // --- Métodos Auxiliares para Senhas ---

    /*
    gera um hash seguro para a senha
    usando o algoritmo bcrypt
    retorna o hash da senha
    é recomendado usar este método ao criar ou atualizar senhas
    */

    public static function gerarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    /*
    Verifica se a senha fornecida corresponde ao hash armazenado
    retorna true se a senha for válida, false caso contrário
     */
    
    public static function verificarSenha(string $senhaPura, string $senhaHash): bool
    {
        return password_verify($senhaPura, $senhaHash);
    }
}