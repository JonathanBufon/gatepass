<?php
// backend/src/Models/Lugar.php - CORRIGIDO com o método excluir()

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use Exception;

class Lugar
{
    private ?int $id_lugar = null;
    private int $id_usuario_criador;
    private string $nome_lugar;
    private ?string $descricao_lugar = null;
    private string $data_criacao;

    private PDO $pdo;

    public function __construct(
        int $id_usuario_criador,
        string $nome_lugar,
        ?string $descricao_lugar = null,
        string $data_criacao = '',
        ?int $id_lugar = null
    ) {
        $this->id_usuario_criador = $id_usuario_criador;
        $this->nome_lugar = $nome_lugar;
        $this->descricao_lugar = $descricao_lugar;
        $this->data_criacao = $data_criacao ?: date('Y-m-d H:i:s');
        $this->id_lugar = $id_lugar;
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters e Setters ---
    public function obterIdLugar(): ?int { return $this->id_lugar; }
    public function obterIdUsuarioCriador(): int { return $this->id_usuario_criador; }
    public function obterNomeLugar(): string { return $this->nome_lugar; }
    public function obterDescricaoLugar(): ?string { return $this->descricao_lugar; }
    public function obterDataCriacao(): string { return $this->data_criacao; }

    public function definirNomeLugar(string $nome_lugar): void { $this->nome_lugar = $nome_lugar; }
    public function definirDescricaoLugar(?string $descricao_lugar): void { $this->descricao_lugar = $descricao_lugar; }

    // --- Métodos de Interação com o Banco de Dados ---
    public function salvar(): bool
    {
        if ($this->id_lugar === null) {
            $stmt = $this->pdo->prepare("INSERT INTO tb_lugares (id_usuario_criador, nome_lugar, descricao_lugar, data_criacao) VALUES (?, ?, ?, ?)");
            $sucesso = $stmt->execute([$this->id_usuario_criador, $this->nome_lugar, $this->descricao_lugar, $this->data_criacao]);
            if ($sucesso) {
                $this->id_lugar = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            $stmt = $this->pdo->prepare("UPDATE tb_lugares SET nome_lugar=?, descricao_lugar=? WHERE id_lugar=?");
            return $stmt->execute([$this->nome_lugar, $this->descricao_lugar, $this->id_lugar]);
        }
    }

    // NOVO: Método de exclusão para completar o CRUD
    public function excluir(): bool
    {
        if ($this->id_lugar === null) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM tb_lugares WHERE id_lugar = ?");
        return $stmt->execute([$this->id_lugar]);
    }

    public static function buscarPorId(int $id): ?Lugar
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_lugares WHERE id_lugar = ?");
        $stmt->execute([$id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            return new Lugar($dados['id_usuario_criador'], $dados['nome_lugar'], $dados['descricao_lugar'], $dados['data_criacao'], $dados['id_lugar']);
        }
        return null;
    }

    public static function buscarTodos(?int $id_usuario_criador = null): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $sql = "SELECT * FROM tb_lugares";
        $params = [];
        if ($id_usuario_criador !== null) {
            $sql .= " WHERE id_usuario_criador = ?";
            $params[] = $id_usuario_criador;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $lugares = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lugares[] = new Lugar($dados['id_usuario_criador'], $dados['nome_lugar'], $dados['descricao_lugar'], $dados['data_criacao'], $dados['id_lugar']);
        }
        return $lugares;
    }
}