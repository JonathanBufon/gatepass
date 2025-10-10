<?php
// src/Models/Cliente.php 

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use PDOException;

class Cliente
{
    private ?int $id_cliente = null;
    private string $nome;
    private string $email;
    private string $senha; // A senha será armazenada como hash
    private ?string $cpf = null;
    private ?string $telefone = null;
    private string $data_cadastro;

    private PDO $pdo;

    /*

     Construtor da classe Cliente.

     */
    public function __construct(
        string $nome,             // OBRIGATÓRIO: Nome do cliente
        string $email,            // OBRIGATÓRIO: Email do cliente
        string $senha,            // OBRIGATÓRIO: Senha hashada do cliente
        ?string $cpf = null,      // OPCIONAL: CPF do cliente
        ?string $telefone = null, // OPCIONAL: Telefone do cliente
        string $data_cadastro = '', // OPCIONAL: Data de cadastro (padrão é a data/hora atual)
        ?int $id_cliente = null // OPCIONAL: ID do cliente (null para novo cliente, ou o ID existente para atualização)
    ) {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->cpf = $cpf;
        $this->telefone = $telefone;
        $this->data_cadastro = $data_cadastro ?: date('Y-m-d H:i:s');
        $this->id_cliente = $id_cliente;

        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters ---
    public function obterIdCliente(): ?int { return $this->id_cliente; }
    public function obterNome(): string { return $this->nome; }
    public function obterEmail(): string { return $this->email; }
    public function obterSenha(): string { return $this->senha; }
    public function obterCpf(): ?string { return $this->cpf; }
    public function obterTelefone(): ?string { return $this->telefone; }
    public function obterDataCadastro(): string { return $this->data_cadastro; }


    // --- Setters ---
    public function definirNome(string $nome): void { $this->nome = $nome; }
    public function definirEmail(string $email): void { $this->email = $email; }
    public function definirSenha(string $senha): void { $this->senha = $senha; }
    public function definirCpf(?string $cpf): void { $this->cpf = $cpf; }
    public function definirTelefone(?string $telefone): void { $this->telefone = $telefone; }


    // --- Métodos de Interação com o Banco de Dados (CRUD) ---

    public function salvar(): bool
    {
        if ($this->id_cliente === null) {
            // INSERT - Sem id_usuario_cadastro
            $stmt = $this->pdo->prepare(
                "INSERT INTO clientes (nome, email, senha, cpf, telefone, data_cadastro)
                 VALUES (:nome, :email, :senha, :cpf, :telefone, :data_cadastro)"
            );
            $sucesso = $stmt->execute([
                'nome' => $this->nome,
                'email' => $this->email,
                'senha' => $this->senha,
                'cpf' => $this->cpf,
                'telefone' => $this->telefone,
                'data_cadastro' => $this->data_cadastro
            ]);
            if ($sucesso) {
                $this->id_cliente = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            // UPDATE - Sem id_usuario_cadastro
            $stmt = $this->pdo->prepare(
                "UPDATE clientes SET
                 nome = :nome,
                 email = :email,
                 senha = :senha,
                 cpf = :cpf,
                 telefone = :telefone
                 WHERE id_cliente = :id_cliente"
            );
            return $stmt->execute([
                'nome' => $this->nome,
                'email' => $this->email,
                'senha' => $this->senha,
                'cpf' => $this->cpf,
                'telefone' => $this->telefone,
                'id_cliente' => $this->id_cliente
            ]);
        }
    }

    public static function buscarPorId(int $id): ?Cliente
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = :id_cliente");
        $stmt->execute(['id_cliente' => $id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return new Cliente(
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['cpf'],
                $dados['telefone'],
                $dados['data_cadastro'],
                $dados['id_cliente']
            );
        }
        return null;
    }

    public static function buscarPorEmail(string $email): ?Cliente
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return new Cliente(
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['cpf'],
                $dados['telefone'],
                $dados['data_cadastro'],
                $dados['id_cliente']
            );
        }
        return null;
    }

    /*

    Retorna todos os clientes.
    A filtragem para um vendedor ver "seus" clientes virá da tabela de Compras.
    Cliente[] Um array de instâncias de Cliente.

     */

    public static function buscarTodos(): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $sql = "SELECT * FROM clientes";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $clientes = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = new Cliente(
                $dados['nome'],
                $dados['email'],
                $dados['senha'],
                $dados['cpf'],
                $dados['telefone'],
                $dados['data_cadastro'],
                $dados['id_cliente']
            );
        }
        return $clientes;
    }

    public function excluir(): bool
    {
        if ($this->id_cliente === null) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id_cliente = :id_cliente");
        return $stmt->execute(['id_cliente' => $this->id_cliente]);
    }


    // Métodos Auxiliares para Senhas
    public static function gerarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    public static function verificarSenha(string $senhaPura, string $senhaHash): bool
    {
        return password_verify($senhaPura, $senhaHash);
    }
}