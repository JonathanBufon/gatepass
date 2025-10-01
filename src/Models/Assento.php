<?php
// backend/src/Models/Assento.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use Exception;

class Assento
{
    private ?int $id_assento = null;
    private int $id_estrutura;
    private string $numero_assento;
    private string $status_assento; // 'disponivel', 'reservado', 'vendido'
    private ?int $id_cliente_reserva = null;
    private ?string $data_reserva = null;

    private PDO $pdo;

    public function __construct(
        int $id_estrutura,
        string $numero_assento,
        string $status_assento,
        ?int $id_cliente_reserva = null,
        ?string $data_reserva = null,
        ?int $id_assento = null
    ) {
        $this->id_estrutura = $id_estrutura;
        $this->numero_assento = $numero_assento;
        $this->status_assento = $status_assento;
        $this->id_cliente_reserva = $id_cliente_reserva;
        $this->data_reserva = $data_reserva;
        $this->id_assento = $id_assento;
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters e Setters ---
    public function obterIdAssento(): ?int { return $this->id_assento; }
    public function obterIdEstrutura(): int { return $this->id_estrutura; }
    public function obterNumeroAssento(): string { return $this->numero_assento; }
    public function obterStatusAssento(): string { return $this->status_assento; }
    public function obterIdClienteReserva(): ?int { return $this->id_cliente_reserva; }
    public function obterDataReserva(): ?string { return $this->data_reserva; }

    public function definirIdEstrutura(int $id_estrutura): void { $this->id_estrutura = $id_estrutura; }
    public function definirNumeroAssento(string $numero_assento): void { $this->numero_assento = $numero_assento; }
    public function definirStatusAssento(string $status_assento): void { $this->status_assento = $status_assento; }
    public function definirIdClienteReserva(?int $id_cliente_reserva): void { $this->id_cliente_reserva = $id_cliente_reserva; }
    public function definirDataReserva(?string $data_reserva): void { $this->data_reserva = $data_reserva; }

    // --- Métodos de Interação com o Banco de Dados ---
    public function salvar(): bool
    {
        if ($this->id_assento === null) {
            $stmt = $this->pdo->prepare("INSERT INTO tb_assentos (id_estrutura, numero_assento, status_assento) VALUES (?, ?, ?)");
            $sucesso = $stmt->execute([$this->id_estrutura, $this->numero_assento, $this->status_assento]);
            if ($sucesso) {
                $this->id_assento = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            $stmt = $this->pdo->prepare("UPDATE tb_assentos SET id_estrutura=?, numero_assento=?, status_assento=?, id_cliente_reserva=?, data_reserva=? WHERE id_assento=?");
            return $stmt->execute([$this->id_estrutura, $this->numero_assento, $this->status_assento, $this->id_cliente_reserva, $this->data_reserva, $this->id_assento]);
        }
    }

    public static function buscarPorId(int $id): ?Assento
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_assentos WHERE id_assento = ?");
        $stmt->execute([$id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            return new Assento($dados['id_estrutura'], $dados['numero_assento'], $dados['status_assento'], $dados['id_cliente_reserva'], $dados['data_reserva'], $dados['id_assento']);
        }
        return null;
    }

    public static function buscarTodosAssentos(int $id_estrutura): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_assentos WHERE id_estrutura = ? ORDER BY numero_assento");
        $stmt->execute([$id_estrutura]);
        $assentos = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assentos[] = new Assento($dados['id_estrutura'], $dados['numero_assento'], $dados['status_assento'], $dados['id_cliente_reserva'], $dados['data_reserva'], $dados['id_assento']);
        }
        return $assentos;
    }
}