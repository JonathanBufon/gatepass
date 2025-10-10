<?php
// src/Models/Produto.php 

namespace GatePass\Models;

use GatePass\Core\Database;
use PDO;
use PDOException;

class Produto
{
    private ?int $id_produto = null;
    private int $id_usuario;
    private string $nome;
    private ?string $descricao = null;
    private float $preco;
    private int $quantidade_total;
    private int $quantidade_disponivel;
    private int $quantidade_reservada = 0;
    private ?string $data_reserva = null;
    private ?int $reservado_por_cliente_id = null;
    private ?string $url_foto_perfil = null; 
    private ?string $url_foto_fundo = null;
    private string $data_cadastro;

    private PDO $pdo;

    public function __construct(
        int $id_usuario,
        string $nome,
        float $preco,
        int $quantidade_total,
        int $quantidade_disponivel,
        ?string $descricao = null,
        int $quantidade_reservada = 0,
        ?string $data_reserva = null,
        ?int $reservado_por_cliente_id = null,
        ?string $url_foto_perfil = null, 
        ?string $url_foto_fundo = null, 
        string $data_cadastro = '',
        ?int $id_produto = null
    ) {
        $this->id_usuario = $id_usuario;
        $this->nome = $nome;
        $this->preco = $preco;
        $this->quantidade_total = $quantidade_total;
        $this->quantidade_disponivel = $quantidade_disponivel;

        $this->descricao = $descricao;
        $this->quantidade_reservada = $quantidade_reservada;
        $this->data_reserva = $data_reserva;
        $this->reservado_por_cliente_id = $reservado_por_cliente_id;
        $this->url_foto_perfil = $url_foto_perfil; 
        $this->url_foto_fundo = $url_foto_fundo; 
        $this->data_cadastro = $data_cadastro ?: date('Y-m-d H:i:s');
        $this->id_produto = $id_produto;

        $this->pdo = Database::obterInstancia()->obterConexao();
    }

    // --- Getters ---
    public function obterIdProduto(): ?int { return $this->id_produto; }
    public function obterIdUsuario(): int { return $this->id_usuario; }
    public function obterNome(): string { return $this->nome; }
    public function obterDescricao(): ?string { return $this->descricao; }
    public function obterPreco(): float { return $this->preco; }
    public function obterQuantidadeTotal(): int { return $this->quantidade_total; }
    public function obterQuantidadeDisponivel(): int { return $this->quantidade_disponivel; }
    public function obterQuantidadeReservada(): int { return $this->quantidade_reservada; }
    public function obterDataReserva(): ?string { return $this->data_reserva; }
    public function obterReservadoPorClienteId(): ?int { return $this->reservado_por_cliente_id; }
    public function obterUrlFotoPerfil(): ?string { return $this->url_foto_perfil; } 
    public function obterUrlFotoFundo(): ?string { return $this->url_foto_fundo; }
    public function obterDataCadastro(): string { return $this->data_cadastro; }

    // --- Setters ---
    public function definirIdUsuario(int $id_usuario): void { $this->id_usuario = $id_usuario; }
    public function definirNome(string $nome): void { $this->nome = $nome; }
    public function definirDescricao(?string $descricao): void { $this->descricao = $descricao; }
    public function definirPreco(float $preco): void { $this->preco = $preco; }
    public function definirQuantidadeTotal(int $quantidade_total): void { $this->quantidade_total = $quantidade_total; }
    public function definirQuantidadeDisponivel(int $quantidade_disponivel): void { $this->quantidade_disponivel = $quantidade_disponivel; }
    public function definirQuantidadeReservada(int $quantidade_reservada): void { $this->quantidade_reservada = $quantidade_reservada; }
    public function definirDataReserva(?string $data_reserva): void { $this->data_reserva = $data_reserva; }
    public function definirReservadoPorClienteId(?int $reservado_por_cliente_id): void { $this->reservado_por_cliente_id = $reservado_por_cliente_id; }
    public function definirUrlFotoPerfil(?string $url_foto_perfil): void { $this->url_foto_perfil = $url_foto_perfil; } 
    public function definirUrlFotoFundo(?string $url_foto_fundo): void { $this->url_foto_fundo = $url_foto_fundo; }   


    // --- Métodos de Interação com o Banco de Dados (CRUD) ---

    public function salvar(): bool
    {
        if ($this->id_produto === null) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO produtos (id_usuario, nome, descricao, preco, quantidade_total, quantidade_disponivel, quantidade_reservada, data_reserva, reservado_por_cliente_id, url_foto_perfil, url_foto_fundo, data_cadastro)
                 VALUES (:id_usuario, :nome, :descricao, :preco, :quantidade_total, :quantidade_disponivel, :quantidade_reservada, :data_reserva, :reservado_por_cliente_id, :url_foto_perfil, :url_foto_fundo, :data_cadastro)"
            );
            $sucesso = $stmt->execute([
                'id_usuario' => $this->id_usuario,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'preco' => $this->preco,
                'quantidade_total' => $this->quantidade_total,
                'quantidade_disponivel' => $this->quantidade_disponivel,
                'quantidade_reservada' => $this->quantidade_reservada,
                'data_reserva' => $this->data_reserva,
                'reservado_por_cliente_id' => $this->reservado_por_cliente_id,
                'url_foto_perfil' => $this->url_foto_perfil,
                'url_foto_fundo' => $this->url_foto_fundo, 
                'data_cadastro' => $this->data_cadastro
            ]);
            if ($sucesso) {
                $this->id_produto = (int)$this->pdo->lastInsertId();
            }
            return $sucesso;
        } else {
            $stmt = $this->pdo->prepare(
                "UPDATE produtos SET
                 id_usuario = :id_usuario,
                 nome = :nome,
                 descricao = :descricao,
                 preco = :preco,
                 quantidade_total = :quantidade_total,
                 quantidade_disponivel = :quantidade_disponivel,
                 quantidade_reservada = :quantidade_reservada,
                 data_reserva = :data_reserva,
                 reservado_por_cliente_id = :reservado_por_cliente_id,
                 url_foto_perfil = :url_foto_perfil, -- NOVO
                 url_foto_fundo = :url_foto_fundo -- NOVO
                 WHERE id_produto = :id_produto"
            );
            return $stmt->execute([
                'id_usuario' => $this->id_usuario,
                'nome' => $this->nome,
                'descricao' => $this->descricao,
                'preco' => $this->preco,
                'quantidade_total' => $this->quantidade_total,
                'quantidade_disponivel' => $this->quantidade_disponivel,
                'quantidade_reservada' => $this->quantidade_reservada,
                'data_reserva' => $this->data_reserva,
                'reservado_por_cliente_id' => $this->reservado_por_cliente_id,
                'url_foto_perfil' => $this->url_foto_perfil,
                'url_foto_fundo' => $this->url_foto_fundo,   
                'id_produto' => $this->id_produto
            ]);
        }
    }

    public static function buscarPorId(int $id): ?Produto
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id_produto = :id_produto");
        $stmt->execute(['id_produto' => $id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            return new Produto(
                $dados['id_usuario'],
                $dados['nome'],
                (float)$dados['preco'],
                $dados['quantidade_total'],
                $dados['quantidade_disponivel'],
                $dados['descricao'],
                $dados['quantidade_reservada'],
                $dados['data_reserva'],
                $dados['reservado_por_cliente_id'],
                $dados['url_foto_perfil'], 
                $dados['url_foto_fundo'],   
                $dados['data_cadastro'],
                $dados['id_produto']
            );
        }
        return null;
    }

    public static function buscarTodos(?int $id_usuario = null): array
    {
        $pdo = Database::obterInstancia()->obterConexao();
        $sql = "SELECT * FROM produtos";
        $params = [];

        if ($id_usuario !== null) {
            $sql .= " WHERE id_usuario = :id_usuario";
            $params['id_usuario'] = $id_usuario;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $produtos = [];
        while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = new Produto(
                $dados['id_usuario'],
                $dados['nome'],
                (float)$dados['preco'],
                $dados['quantidade_total'],
                $dados['quantidade_disponivel'],
                $dados['descricao'],
                $dados['quantidade_reservada'],
                $dados['data_reserva'],
                $dados['reservado_por_cliente_id'],
                $dados['url_foto_perfil'], 
                $dados['url_foto_fundo'],   
                $dados['data_cadastro'],
                $dados['id_produto']
            );
        }
        return $produtos;
    }

    public function excluir(): bool
    {
        if ($this->id_produto === null) {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE id_produto = :id_produto");
        return $stmt->execute(['id_produto' => $this->id_produto]);
    }

    public function reservar(int $idUsuarioReservando): bool
    { return true; }
    public function liberarReserva(): bool
    { return true; }

    public function decrementarEstoque(int $quantidade): bool
    {
        if ($this->quantidade_disponivel < $quantidade) {
            return false;
        }
        $this->quantidade_disponivel -= $quantidade;

        if ($this->quantidade_reservada > 0 && $this->reservado_por_cliente_id !== null) {
            $this->quantidade_reservada = 0;
            $this->data_reserva = null;
            $this->reservado_por_cliente_id = null;
        }
        return $this->salvar();
    }
}