<?php
// backend/src/Models/ItemPedido.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use Exception;

class ItemPedido
{
    private ?int $id_item = null;
    private int $id_pedido;
    private int $id_produto;
    private ?int $id_assento = null;
    private int $quantidade;
    private float $preco_unitario;

    private PDO $pdo;

    public function __construct(
        int $id_pedido,
        int $id_produto,
        int $quantidade,
        float $preco_unitario,
        ?int $id_assento = null,
        ?int $id_item = null
    ) {
        $this->id_pedido = $id_pedido;
        $this->id_produto = $id_produto;
        $this->quantidade = $quantidade;
        $this->preco_unitario = $preco_unitario;
        $this->id_assento = $id_assento;
        $this->id_item = $id_item;
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters e Setters ---
    public function obterIdItem(): ?int { return $this->id_item; }
    public function obterIdPedido(): int { return $this->id_pedido; }
    public function obterIdProduto(): int { return $this->id_produto; }
    public function obterIdAssento(): ?int { return $this->id_assento; }
    public function obterQuantidade(): int { return $this->quantidade; }
    public function obterPrecoUnitario(): float { return $this->preco_unitario; }

    // --- Métodos de Interação com o Banco de Dados ---
    public function salvar(): bool
    {
        if ($this->id_item === null) {
            $stmt = $this->pdo->prepare("INSERT INTO tb_itens_pedido (id_pedido, id_produto, id_assento, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");
            $sucesso = $stmt->execute([$this->id_pedido, $this->id_produto, $this->id_assento, $this->quantidade, $this->preco_unitario]);
            if ($sucesso) {
                $this->id_item = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        }
        // Itens de pedido não são atualizados após a criação
        return false;
    }
}