<?php
// backend/src/Models/Estrutura.php

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use Exception;

class Estrutura
{
    private ?int $id_estrutura = null;
    private int $id_lugar;
    private ?int $id_produto_padrao = null;
    private string $tipo_estrutura;
    private string $nome_bloco;
    private ?int $capacidade_pista = null;
    private ?string $prefixo_assentos = null;
    private ?int $qtd_assentos_por_bloco = null;

    private PDO $pdo;

    public function __construct(
        int $id_lugar,
        string $tipo_estrutura,
        string $nome_bloco,
        ?int $id_produto_padrao = null,
        ?int $capacidade_pista = null,
        ?string $prefixo_assentos = null,
        ?int $qtd_assentos_por_bloco = null,
        ?int $id_estrutura = null
    ) {
        $this->id_lugar = $id_lugar;
        $this->tipo_estrutura = $tipo_estrutura;
        $this->nome_bloco = $nome_bloco;
        $this->id_produto_padrao = $id_produto_padrao;
        $this->capacidade_pista = $capacidade_pista;
        $this->prefixo_assentos = $prefixo_assentos;
        $this->qtd_assentos_por_bloco = $qtd_assentos_por_bloco;
        $this->id_estrutura = $id_estrutura;
        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters e Setters ---
    public function obterIdEstrutura(): ?int { return $this->id_estrutura; }
    public function obterIdLugar(): int { return $this->id_lugar; }
    public function obterIdProdutoPadrao(): ?int { return $this->id_produto_padrao; }
    public function obterTipoEstrutura(): string { return $this->tipo_estrutura; }
    public function obterNomeBloco(): string { return $this->nome_bloco; }
    public function obterCapacidadePista(): ?int { return $this->capacidade_pista; }
    public function obterPrefixoAssentos(): ?string { return $this->prefixo_assentos; }
    public function obterQtdAssentosPorBloco(): ?int { return $this->qtd_assentos_por_bloco; }

    public function definirIdLugar(int $id_lugar): void { $this->id_lugar = $id_lugar; }
    public function definirIdProdutoPadrao(?int $id_produto_padrao): void { $this->id_produto_padrao = $id_produto_padrao; }
    public function definirTipoEstrutura(string $tipo_estrutura): void { $this->tipo_estrutura = $tipo_estrutura; }
    public function definirNomeBloco(string $nome_bloco): void { $this->nome_bloco = $nome_bloco; }
    public function definirCapacidadePista(?int $capacidade_pista): void { $this->capacidade_pista = $capacidade_pista; }
    public function definirPrefixoAssentos(?string $prefixo_assentos): void { $this->prefixo_assentos = $prefixo_assentos; }
    public function definirQtdAssentosPorBloco(?int $qtd_assentos_por_bloco): void { $this->qtd_assentos_por_bloco = $qtd_assentos_por_bloco; }

    // --- Métodos de Interação com o Banco de Dados ---
    public function salvar(): bool
    {
        if ($this->id_estrutura === null) {
            $stmt = $this->pdo->prepare("INSERT INTO tb_estruturas (id_lugar, id_produto_padrao, tipo_estrutura, nome_bloco, capacidade_pista, prefixo_assentos, qtd_assentos_por_bloco) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $sucesso = $stmt->execute([$this->id_lugar, $this->id_produto_padrao, $this->tipo_estrutura, $this->nome_bloco, $this->capacidade_pista, $this->prefixo_assentos, $this->qtd_assentos_por_bloco]);
            if ($sucesso) {
                $this->id_estrutura = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            $stmt = $this->pdo->prepare("UPDATE tb_estruturas SET id_lugar=?, id_produto_padrao=?, tipo_estrutura=?, nome_bloco=?, capacidade_pista=?, prefixo_assentos=?, qtd_assentos_por_bloco=? WHERE id_estrutura=?");
            return $stmt->execute([$this->id_lugar, $this->id_produto_padrao, $this->tipo_estrutura, $this->nome_bloco, $this->capacidade_pista, $this->prefixo_assentos, $this->qtd_assentos_por_bloco, $this->id_estrutura]);
        }
    }
    
    // --- Lógica de Geração de Assentos em Lote (CRÍTICO) ---
 public function gerarAssentosEmLote(): bool
    {
        if ($this->obterTipoEstrutura() !== 'assentos_numerados' || $this->obterQtdAssentosPorBloco() === null) {
            error_log("Tentativa de gerar assentos em lote para uma estrutura inválida.");
            return false;
        }

        $pdo = Database::obterInstancia()->obterConexao();
        $pdo->beginTransaction();

        try {
            for ($i = 1; $i <= $this->obterQtdAssentosPorBloco(); $i++) {
                $numero_assento = ($this->obterPrefixoAssentos() ?? '') . $i;

                $assento = new Assento(
                    $this->obterIdEstrutura(),
                    $numero_assento,
                    'disponivel'
                );

                if (!$assento->salvar()) {
                    throw new Exception("Falha ao salvar o assento " . $numero_assento . ".");
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Erro crítico ao gerar assentos em lote: " . $e->getMessage());
            return false;
        }
    }
    
    public static function buscarPorId(int $id): ?Estrutura
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_estruturas WHERE id_estrutura = ?");
        $stmt->execute([$id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dados) {
            return new Estrutura($dados['id_lugar'], $dados['tipo_estrutura'], $dados['nome_bloco'], $dados['id_produto_padrao'], $dados['capacidade_pista'], $dados['prefixo_assentos'], $dados['qtd_assentos_por_bloco'], $dados['id_estrutura']);
        }
        return null;
    }

    public static function buscarPorLugar(int $id_lugar): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM tb_estruturas WHERE id_lugar = ? ORDER BY nome_bloco");
        $stmt->execute([$id_lugar]);
        $estruturas = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $estruturas[] = new Estrutura($dados['id_lugar'], $dados['tipo_estrutura'], $dados['nome_bloco'], $dados['id_produto_padrao'], $dados['capacidade_pista'], $dados['prefixo_assentos'], $dados['qtd_assentos_por_bloco'], $dados['id_estrutura']);
        }
        return $estruturas;
    }
}