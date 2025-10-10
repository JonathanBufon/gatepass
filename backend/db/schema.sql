-- Arquivo: db/schema.sql
-- Descrição: Modelo de dados final e otimizado para o projeto GatePass.

-- -----------------------------------------------------------------------------
-- Configurações Iniciais
-- -----------------------------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0; -- Desabilita temporariamente para evitar erros de dependência

-- -----------------------------------------------------------------------------
-- Tabela de Usuários (Vendedores/Administradores)
-- -----------------------------------------------------------------------------

-- Finalidade: Armazena as contas dos vendedores que gerenciam produtos e eventos.
CREATE TABLE IF NOT EXISTS tb_usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Clientes (Compradores)
-- -----------------------------------------------------------------------------

-- Finalidade: Armazena as contas dos compradores que realizam as compras.
CREATE TABLE IF NOT EXISTS tb_clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE NULL,
    telefone VARCHAR(20) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Lugares
-- -----------------------------------------------------------------------------

-- **NOVA:** Representa os locais físicos dos eventos.
CREATE TABLE IF NOT EXISTS tb_lugares (
    id_lugar INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_criador INT NOT NULL,  -- O vendedor que cadastrou este lugar
    nome_lugar VARCHAR(255) NOT NULL,
    descricao_lugar TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario_criador) REFERENCES tb_usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Estruturas
-- -----------------------------------------------------------------------------

-- **NOVA:** Representa uma área específica dentro de um local (ex: "Pista", "Setor A").
CREATE TABLE IF NOT EXISTS tb_estruturas (
    id_estrutura INT AUTO_INCREMENT PRIMARY KEY,
    id_lugar INT NOT NULL,
    id_produto_padrao INT NULL,          -- FK para o produto padrão associado (ex: "Ingresso Pista")
    tipo_estrutura ENUM('pista', 'assentos_numerados') NOT NULL, -- Tipo de estrutura para o backend
    nome_bloco VARCHAR(50) NOT NULL,     -- O nome do bloco (ex: 'Bloco A')
    capacidade_pista INT NULL,            -- Capacidade total para tipo 'pista'
    prefixo_assentos VARCHAR(10) NULL,   -- Prefixo para a numeração (ex: 'A-')
    qtd_assentos_por_bloco INT NULL,     -- Quantidade de assentos a ser gerada em lote
    
    FOREIGN KEY (id_lugar) REFERENCES tb_lugares(id_lugar) ON DELETE CASCADE,
    FOREIGN KEY (id_produto_padrao) REFERENCES tb_produtos(id_produto) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Produtos (Tipos de Ingresso)
-- -----------------------------------------------------------------------------

-- **REFATORADO:** Representa os tipos de ingressos. Ligada diretamente à estrutura à qual pertence.
CREATE TABLE IF NOT EXISTS tb_produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,            -- O vendedor que cadastrou este produto
    id_estrutura INT NOT NULL,          -- A qual estrutura este produto se refere (removido id_lugar redundante)
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    url_foto_perfil VARCHAR(255) NULL,
    url_foto_fundo VARCHAR(255) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario) REFERENCES tb_usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_estrutura) REFERENCES tb_estruturas(id_estrutura) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- Tabela de Assentos
-- -----------------------------------------------------------------------------

-- **REFATORADO:** Assentos agora estão vinculados a uma estrutura e gerenciam o estoque individualmente.
CREATE TABLE IF NOT EXISTS tb_assentos (
    id_assento INT AUTO_INCREMENT PRIMARY KEY,
    id_estrutura INT NOT NULL,          -- A qual estrutura este assento pertence
    numero_assento VARCHAR(20) NOT NULL,  -- O número único do assento (ex: 'A10', 'B-15')
    status_assento ENUM('disponivel', 'reservado', 'vendido') NOT NULL,
    id_cliente_reserva INT NULL,        -- O cliente que reservou o assento
    data_reserva TIMESTAMP NULL,        -- Timestamp da reserva de 2 minutos
    
    FOREIGN KEY (id_estrutura) REFERENCES tb_estruturas(id_estrutura) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente_reserva) REFERENCES tb_clientes(id_cliente) ON DELETE SET NULL,
    UNIQUE KEY (id_estrutura, numero_assento) -- Garante que não haja assentos duplicados na mesma estrutura
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Pedidos
-- -----------------------------------------------------------------------------

-- **NOVA:** Tabela principal para cada pedido (checkout) realizado.
CREATE TABLE IF NOT EXISTS tb_pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_usuario_vendedor INT NOT NULL,  -- O vendedor principal do pedido
    status_pedido ENUM('pendente', 'pago', 'cancelado') NOT NULL,
    metodo_pagamento VARCHAR(50) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_cliente) REFERENCES tb_clientes(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_vendedor) REFERENCES tb_usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela de Itens do Pedido
-- -----------------------------------------------------------------------------

-- **REFATORADO:** Tabela de ligação que detalha os itens do pedido (suporta assentos ou pista).
CREATE TABLE IF NOT EXISTS tb_itens_pedido (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_produto INT NOT NULL,
    id_assento INT NULL,                    -- O assento específico vendido (será NULL para ingressos de pista)
    quantidade INT NOT NULL,                -- A quantidade de ingressos (será 1 para assentos numerados)
    preco_unitario DECIMAL(10, 2) NOT NULL,
    
    FOREIGN KEY (id_pedido) REFERENCES tb_pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES tb_produtos(id_produto) ON DELETE CASCADE,
    FOREIGN KEY (id_assento) REFERENCES tb_assentos(id_assento) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Reabilita as chaves estrangeiras
-- -----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;