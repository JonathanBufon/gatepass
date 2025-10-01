-- db/schema.sql

-- Define o charset padrão para o banco de dados
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabela de Usuários
-- Armazena as informações dos usuários que gerenciam o sistema (vendedores, administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE, -- O email deve ser único para login
    senha VARCHAR(255) NOT NULL,       -- Armazenar senhas hashadas
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE         -- Para ativar/desativar usuários
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Produtos/Ingressos
CREATE TABLE IF NOT EXISTS produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    quantidade_total INT NOT NULL,
    quantidade_disponivel INT NOT NULL,
    quantidade_reservada INT DEFAULT 0,
    data_reserva TIMESTAMP NULL,
    reservado_por_cliente_id INT NULL,
    url_foto_perfil VARCHAR(255) NULL, -- NOVO: URL da foto principal do ingresso
    url_foto_fundo VARCHAR(255) NULL,  -- NOVO: URL da foto de fundo/banner do ingresso
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (reservado_por_cliente_id) REFERENCES clientes(id_cliente) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Clientes
-- Armazena as informações dos clientes que realizam as compras (agora independentes de um vendedor cadastrador)
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE, -- O email deve ser único para login do cliente
    senha VARCHAR(255) NOT NULL,       -- Campo para armazenar a senha hashada do cliente
    cpf VARCHAR(14) UNIQUE NULL,
    telefone VARCHAR(20) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Compras
CREATE TABLE IF NOT EXISTS compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_produto INT NOT NULL,
    id_cliente INT NOT NULL,
    id_usuario_vendedor INT NOT NULL,
    quantidade_comprada INT NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    metodo_pagamento VARCHAR(50) NOT NULL, -- NOVO: Coluna para o método de pagamento
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
