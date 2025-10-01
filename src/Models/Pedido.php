<?php
// backend/src/Models/Pedido.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use Exception;

class Pedido
{
    private ?int $id_pedido = null;
    private int $id_cliente;
    private int $id_usuario_vendedor;
    private string $status_pedido; // 'pendente', 'pago', 'cancelado'
    private string $metodo_pagamento;
    private float $valor_total;
    private string $data_pedido;

    private PDO $pdo;

    public function __construct(
        int $id_cliente,
        int $id_usuario_vendedor,
        string $status_pedido,
        string $metodo_pagamento,
        float $valor_total,
        string $data_pedido = '',
        ?int $id_pedido = null
    ) {
        $this->id_cliente = $id_cliente;
        $this->id_usuario_vendedor = $id_usuario_vendedor;
        $this->status_pedido = $status_pedido;
        $this->metodo_pagamento = $metodo_pagamento;
        $this->valor_total = $valor_total;
        $this->data_pedido = $data_pedido ?: date('Y-m-d H:i:s');
        $this->id_pedido = $id_pedido;
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters e Setters ---
    public function obterIdPedido(): ?int { return $this->id_pedido; }
    public function obterIdCliente(): int { return $this->id_cliente; }
    public function obterIdUsuarioVendedor(): int { return $this->id_usuario_vendedor; }
    public function obterStatusPedido(): string { return $this->status_pedido; }
    public function obterMetodoPagamento(): string { return $this->metodo_pagamento; }
    public function obterValorTotal(): float { return $this->valor_total; }
    public function obterDataPedido(): string { return $this->data_pedido; }

    public function definirStatusPedido(string $status_pedido): void { $this->status_pedido = $status_pedido; }

    // --- Métodos de Interação com o Banco de Dados ---
    public function salvar(): bool
    {
        if ($this->id_pedido === null) {
            $stmt = $this->pdo->prepare("INSERT INTO tb_pedidos (id_cliente, id_usuario_vendedor, status_pedido, metodo_pagamento, valor_total, data_pedido) VALUES (?, ?, ?, ?, ?, ?)");
            $sucesso = $stmt->execute([$this->id_cliente, $this->id_usuario_vendedor, $this->status_pedido, $this->metodo_pagamento, $this->valor_total, $this->data_pedido]);
            if ($sucesso) {
                $this->id_pedido = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        }
        // Pedidos geralmente não são atualizados, mas você pode adicionar essa lógica se precisar.
        return false;
    }

    public static function buscarPorId(int $id): ?Pedido
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE id_pedido = ?");
        $stmt->execute([$id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            return new Pedido($dados['id_cliente'], $dados['id_usuario_vendedor'], $dados['status_pedido'], $dados['metodo_pagamento'], (float)$dados['valor_total'], $dados['data_pedido'], $dados['id_pedido']);
        }
        return null;
    }

    public static function buscarPorCliente(int $id_cliente): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_pedidos WHERE id_cliente = ? ORDER BY data_pedido DESC");
        $stmt->execute([$id_cliente]);
        $pedidos = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pedidos[] = new Pedido($dados['id_cliente'], $dados['id_usuario_vendedor'], $dados['status_pedido'], $dados['metodo_pagamento'], (float)$dados['valor_total'], $dados['data_pedido'], $dados['id_pedido']);
        }
        return $pedidos;
    }
}