<?php
// src/Models/Compra.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use PDOException;

class Compra
{
    private ?int $id_compra = null;
    private int $id_produto;
    private int $id_cliente;
    private int $id_usuario_vendedor;
    private int $quantidade_comprada;
    private float $valor_total;
    private string $metodo_pagamento;
    private string $data_compra;

    private PDO $pdo;

    public function __construct(
        int $id_produto,
        int $id_cliente,
        int $id_usuario_vendedor,
        int $quantidade_comprada,
        float $valor_total,
        string $metodo_pagamento, 
        string $data_compra = '',
        ?int $id_compra = null
    ) {
        $this->id_produto = $id_produto;
        $this->id_cliente = $id_cliente;
        $this->id_usuario_vendedor = $id_usuario_vendedor;
        $this->quantidade_comprada = $quantidade_comprada;
        $this->valor_total = $valor_total;
        $this->metodo_pagamento = $metodo_pagamento; 
        $this->data_compra = $data_compra ?: date('Y-m-d H:i:s');
        $this->id_compra = $id_compra;

        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters ---
    public function obterIdCompra(): ?int { return $this->id_compra; }
    public function obterIdProduto(): int { return $this->id_produto; }
    public function obterIdCliente(): int { return $this->id_cliente; }
    public function obterIdUsuarioVendedor(): int { return $this->id_usuario_vendedor; }
    public function obterQuantidadeComprada(): int { return $this->quantidade_comprada; }
    public function obterValorTotal(): float { return $this->valor_total; }
    public function obterMetodoPagamento(): string { return $this->metodo_pagamento; } 
    public function obterDataCompra(): string { return $this->data_compra; }


    // --- Métodos de Interação com o Banco de Dados (CRUD) ---

    public function salvar(): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO compras (id_produto, id_cliente, id_usuario_vendedor, quantidade_comprada, valor_total, metodo_pagamento, data_compra)
             VALUES (:id_produto, :id_cliente, :id_usuario_vendedor, :quantidade_comprada, :valor_total, :metodo_pagamento, :data_compra)"
        );
        $sucesso = $stmt->execute([
            'id_produto' => $this->id_produto,
            'id_cliente' => $this->id_cliente,
            'id_usuario_vendedor' => $this->id_usuario_vendedor,
            'quantidade_comprada' => $this->quantidade_comprada,
            'valor_total' => $this->valor_total,
            'metodo_pagamento' => $this->metodo_pagamento, 
            'data_compra' => $this->data_compra
        ]);
        if ($sucesso) {
            $this->id_compra = (int)$this->pdo->lastInsertId();
        }
        return $sucesso;
    }

    public static function buscarPorId(int $id): ?Compra
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE id_compra = :id_compra");
        $stmt->execute(['id_compra' => $id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return new Compra(
                $dados['id_produto'],
                $dados['id_cliente'],
                $dados['id_usuario_vendedor'],
                $dados['quantidade_comprada'],
                (float)$dados['valor_total'],
                $dados['metodo_pagamento'], 
                $dados['data_compra'],
                $dados['id_compra']
            );
        }
        return null;
    }

    public static function buscarPorCliente(int $idCliente): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE id_cliente = :id_cliente ORDER BY data_compra DESC");
        $stmt->execute(['id_cliente' => $idCliente]);
        $compras = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $compras[] = new Compra(
                $dados['id_produto'],
                $dados['id_cliente'],
                $dados['id_usuario_vendedor'],
                $dados['quantidade_comprada'],
                (float)$dados['valor_total'],
                $dados['metodo_pagamento'], 
                $dados['data_compra'],
                $dados['id_compra']
            );
        }
        return $compras;
    }

    public static function buscarTodos(): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->query("SELECT * FROM compras ORDER BY data_compra DESC");
        $compras = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $compras[] = new Compra(
                $dados['id_produto'],
                $dados['id_cliente'],
                $dados['id_usuario_vendedor'],
                $dados['quantidade_comprada'],
                (float)$dados['valor_total'],
                $dados['metodo_pagamento'], 
                $dados['data_compra'],
                $dados['id_compra']
            );
        }
        return $compras;
    }

    public function excluir(): bool
    {
        if ($this->id_compra === null) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM compras WHERE id_compra = :id_compra");
        return $stmt->execute(['id_compra' => $this->id_compra]);
    }
}