# GatePass: Sistema de Compra e Venda de Ingressos/Produtos

![GatePass Logo](public/images/gate-pass-logo.png) 

## 📄 Visão Geral do Projeto

O **GatePass** é um sistema completo e moderno projetado para facilitar a compra e venda de ingressos ou produtos. Ele oferece uma experiência de usuário intuitiva com um design aprimorado, um controle de estoque robusto com lógica de reserva por tempo e um fluxo de compra seguro e simulado. O projeto foi construído com foco em boas práticas de desenvolvimento e arquitetura modular, utilizando **PHP puro** no backend e tecnologias web padrão para o frontend, tudo empacotado e orquestrado por **Docker** para um ambiente de desenvolvimento portátil e eficiente.

## 🧰 Linguagens e Tecnologias Utilizadas

Este projeto foi desenvolvido utilizando as seguintes tecnologias:

* **Linguagens:**
  * PHP 8.1: Para toda a lógica de backend, gerenciamento de sessões, interação com o banco de dados e controle de fluxo.
  * HTML: Estruturação das páginas e formulários da interface do usuário.
  * CSS: Estilização visual (layouts, componentes, responsividade), com uso de variáveis CSS para modularidade.
* **Banco de Dados:**
  * MariaDB 10.6: Sistema de gerenciamento de banco de dados relacional para persistência dos dados da aplicação.
* **Orquestração e Ambiente:**
  * Docker: Para criar e gerenciar containers isolados para a aplicação PHP e o banco de dados.
  * Docker Compose: Para definir e executar aplicações multi-container Docker com um único comando.
* **Dependências PHP (via Composer):**
  * Dompdf (`dompdf/dompdf`): Biblioteca essencial para a geração de documentos PDF a partir de HTML, utilizada para os ingressos.

## ✨ Funcionalidades Principais

O **GatePass** oferece um conjunto abrangente de funcionalidades, divididas por papéis de usuário:

### Módulo de Usuários (Vendedores/Administradores)

* **Autenticação Completa:**
  * Cadastro: Criação de novas contas de vendedor (`/public/cadastro_usuario.php`).
  * Login/Logout: Gerenciamento de sessões seguras (`/public/login.php`, `/public/logout.php`).
  * Verificação de Sessão: Proteção de páginas sensíveis (`GatePass\Core\Auth`).
* **Gerenciamento de Perfil:**
  * Edição de Perfil: Atualização de dados pessoais e senha (com validação de senha atual) (`/public/editar_perfil.php`).
  * Exclusão de Perfil: Remoção definitiva da conta com confirmação (`/public/excluir_perfil.php`).
* **Gestão de Produtos:**
  * Cadastro de Produtos: Adição de novos produtos/ingressos com detalhes (`/public/cadastrar_produto.php`).
    * Upload de Imagens: Suporte para upload de `Foto de Perfil` e `Foto de Fundo/Banner` do evento (JPG, PNG, GIF), vinculadas ao produto.
  * Visualização de Produtos: Listagem dos produtos cadastrados pelo próprio vendedor (`/public/listar_produtos.php`).
  * Edição de Produtos: Modificação de detalhes de produtos existentes (apenas os próprios) (`/public/editar_produto.php`).
  * Exclusão de Produtos: Remoção de produtos (apenas os próprios) (`/public/excluir_produto.php`).
* **Painel Administrativo:**
  * Dashboard do Vendedor: Página centralizada (`/public/dashboard_vendedor.php`) para acesso rápido a todas as funcionalidades de gestão.
  * Listagem de Usuários: Visão geral de outros usuários do sistema (`/public/listar_usuarios.php`).

### Módulo de Clientes (Compradores)

* **Autenticação Independente:**
  * Cadastro: Clientes podem se auto-cadastrar para criar sua conta (`/public/cadastro_cliente.php`).
  * Login/Logout: Gerenciamento de sessões específicas para clientes (`/public/login_cliente.php`, `/public/logout_cliente.php`).
* **Navegação e Compra:**
  * Vitrine de Produtos: Página pública e responsiva (`/public/listar_produtos_publico.php`) para visualizar todos os ingressos/produtos disponíveis.
    * Layout Moderno: Apresenta os produtos em um layout de cards visualmente aprimorado, com imagens e informações claras.
  * Detalhes do Produto: Página dedicada para informações aprofundadas do ingresso (`/public/detalhes_produto.php`).
    * Reserva de Estoque (2 Minutos): Quando a última unidade de um produto é visualizada por um cliente logado, ela é automaticamente reservada por 2 minutos.
    * Gerenciamento Automático: Durante a reserva, o item fica indisponível para outros; a reserva expira e o item é liberado se a compra não for concluída no prazo.
    * Status Claros: Exibe status como "Reservado para você", "Temporariamente reservado", ou "Esgotado".
  * Sistema de Carrinho de Compras:
    * Adicionar ao Carrinho: Inclusão de produtos (com quantidade) no carrinho a partir da vitrine ou detalhes.
    * Gerenciar Carrinho: Página dedicada (`/public/ver_carrinho.php`) para visualizar, ajustar quantidades e remover itens.
  * Fluxo de Checkout:
    * Página de resumo do pedido (`/public/checkout.php`) com re-validação de estoque final.
    * Seleção de Método de Pagamento: Opções simuladas de PIX, Boleto e Cartão de Crédito.
  * Finalização da Compra: Processamento seguro da transação (`/public/processar_compra.php`), com decremento de estoque e registro de múltiplas compras via transações atômicas.
* **Histórico de Compras:**
  * Minhas Compras: Clientes podem visualizar um histórico detalhado de suas compras (`/public/minhas_compras.php`).
  * Geração de Ingresso em PDF: Para cada compra, é possível gerar um ingresso em formato PDF com layout padrão, utilizando imagens do produto e dados do cliente (incluindo CPF).

### Medidas de Segurança Implementadas

* **Sanitização de Dados:** Uso rigoroso de `htmlspecialchars()` e `filter_var()` em todas as entradas de usuário e saídas HTML para prevenir ataques de Cross-Site Scripting (XSS).
* **Prevenção de SQL Injection:** Todas as interações com o banco de dados utilizam Prepared Statements (consultas preparadas) através da extensão PDO.
* **Hashing de Senhas:** Senhas dos usuários e clientes são armazenadas de forma segura com `Bcrypt`.
* **Gerenciamento Seguro de Arquivos:** A classe `GatePass\Utils\FileUpload` valida o tipo e tamanho dos arquivos de imagem, gera nomes únicos e move os arquivos de forma segura.
* **Controle de Acesso por Sessão:** Páginas protegidas e ações sensíveis verificam a autenticação e permissões do usuário logado.

## ⚙️ Como Instalar e Rodar Localmente

Siga estas instruções passo a passo para configurar e executar o projeto em seu ambiente de desenvolvimento.

### Pré-requisitos

* [Docker Desktop](https://www.docker.com/products/docker-desktop) (que inclui Docker Engine e Docker Compose) instalado em seu sistema operacional (Windows, macOS, Linux).

### Passos para Configuração e Execução

1.  **Clone o Repositório:**
    Abra seu terminal ou prompt de comando, navegue até o diretório onde deseja armazenar o projeto e clone o repositório:
    ```bash
    git clone https://github.com/JonathanBufon/gatepass.git GatePass
    cd GatePass

    ```

2.  **Execute o Script de Setup Automatizado:**
    Este script `setup.sh` automatiza todos os passos iniciais de configuração: criação de diretórios, ajuste de permissões, instalação de dependências Composer e inicialização dos serviços Docker (aplicação PHP e banco de dados MariaDB).
    ```bash
    # Concede permissão de execução ao script
    chmod +x setup.sh

    # Executa o script de setup
    ./setup.sh
    ```
    *Siga as instruções exibidas no terminal durante a execução do script.* Ele fará o seguinte:
    * Criará a pasta `public/uploads/produtos` e definirá as permissões necessárias.
    * Instalará as dependências PHP (incluindo Dompdf e as extensões PHP necessárias) via Composer em um container Docker.
    * Construirá as imagens Docker e iniciará os containers PHP (Apache) e MariaDB.
    * Na **primeira execução**, o banco de dados (`gatepass_db`) será inicializado e todas as tabelas (definidas em `db/schema.sql`) serão criadas.

### Solução de Problemas: Configuração Manual (Fallback)

Caso o script `setup.sh` falhe durante a execução, você pode tentar realizar os passos manualmente. **Isso é recomendado apenas se você encontrar erros no script automatizado.**

1.  **Crie as Pastas de Uploads e Ajuste Permissões:**
    ```bash
    mkdir -p public/uploads/produtos
    chmod -R 777 public/uploads # ATENÇÃO: Permissão 777 é para desenvolvimento/teste.
    ```

2.  **Instale as Dependências do Composer:**
    ```bash
    docker run --rm -v "$(pwd)":/app composer/composer install
    ```
    *Se você precisar atualizar dependências ou o `composer.lock` estiver fora de sincronia, use `docker run --rm -v "$(pwd)":/app composer/composer update`*.

3.  **Inicie os Serviços Docker:**
    ```bash
    docker compose up --build -d
    ```
    *Este comando irá construir suas imagens Docker e iniciar os containers. Na primeira vez, ele também inicializará seu banco de dados via `db/schema.sql`.*

### Finalização

1.  **Verifique o Status dos Containers:**
    Para confirmar que todos os serviços estão em execução após a configuração (automatizada ou manual):
    ```bash
    docker compose ps
    ```
    Você deverá ver `gatepass_app_php` e `gatepass_db_mariadb` com status `running`.

2.  **Acesse a Aplicação no Navegador:**
    Com os containers rodando, abra seu navegador web e acesse o endereço:
    ```
    http://localhost/public/
    ```
    Você será automaticamente redirecionado para a página de listagem pública de produtos, pronto para começar a usar o **GatePass**.

## 📖 Como Usar o Sistema

### Para Compradores (Clientes)

1.  **Navegar e Adicionar ao Carrinho:**
    * Acesse `http://localhost/public/` para explorar os produtos.
    * Use o botão "Adicionar ao Carrinho" nos cards de produto ou na página de detalhes.
2.  **Ver e Gerenciar Carrinho:**
    * No cabeçalho, clique em "Ver Carrinho" (exibe a quantidade de itens no carrinho) para acessar a página de gerenciamento (`/public/ver_carrinho.php`).
    * Nesta página, você pode ajustar a quantidade de itens ou remover itens do carrinho.
3.  **Cadastro ou Login de Cliente:**
    * Se você ainda não tem uma conta de cliente, pode se cadastrar via o link "Cadastre-se Cliente" no cabeçalho ou durante o checkout.
    * Se já tem uma conta, use o link "Login Cliente" no cabeçalho.
4.  **Finalizar Compra:**
    * No carrinho, clique em "Finalizar Compra" para ir à página de checkout (`/public/checkout.php`).
    * Revise o resumo do pedido e escolha o método de pagamento (PIX, Boleto ou Cartão - *lembre-se, a transação é simulada*).
    * Clique em "Confirmar Pagamento".
5.  **Minhas Compras e Ingresso PDF:**
    * Após a compra bem-sucedida, você será redirecionado para "Minhas Compras" (`/public/minhas_compras.php`).
    * Aqui, você verá o histórico de suas compras. Ao lado de cada compra, haverá um botão "Gerar Ingresso" para baixar o ingresso correspondente em formato PDF.

### Para Vendedores (Usuários)

1.  **Login de Vendedor:**
    * Na vitrine principal (`http://localhost/public/`), clique em "Acesso Vendedor" no cabeçalho.
2.  **Cadastro de Usuário (Opcional):**
    * Se ainda não tem uma conta de vendedor, use o link "Cadastre-se aqui" na página de login de vendedor (`/public/login.php`).
3.  **Acessar Painel do Vendedor:**
    * Após o login como vendedor, você será redirecionado para o `Painel do Vendedor` (`/public/dashboard_vendedor.php`).
4.  **Gerenciar Produtos:**
    * No painel, você pode "Cadastrar Novo Produto" (incluindo upload de fotos de perfil e banner!) e ver "Meus Produtos" para editar ou excluir os produtos que você cadastrou.
5.  **Outras Ações:**
    * "Editar Meu Perfil": Para gerenciar sua conta de vendedor.
    * "Listar Usuários": Para ver uma lista de todos os usuários do sistema.

## 📁 Estrutura do Projeto

```
GatePass/
├── public/                    # Arquivos acessíveis via web (HTML, CSS, JS, imagens e scripts PHP)
│   ├── index.php              # Página inicial que redireciona para a vitrine
│   ├── css/                   # Estilos CSS globais (ex: style.css)
│   ├── images/                # Imagens estáticas (logo, ícones, etc.)
│   ├── uploads/               # Uploads de usuários
│   │   └── produtos/          # Imagens específicas dos produtos
│   └── *.php                  # Scripts públicos (login, cadastro, vitrine, carrinho, checkout, etc.)
│
├── src/                       # Código-fonte da aplicação (lógica PHP)
│   ├── Core/                  # Classes centrais (conexão com o banco, autenticação)
│   │   ├── Database.php       # Conexão PDO com MariaDB
│   │   └── Auth.php           # Gerenciamento de sessões e autenticação
│   ├── Models/                # Modelos de dados (interação com o banco)
│   │   ├── Usuario.php        # Modelo para vendedores
│   │   ├── Cliente.php        # Modelo para clientes
│   │   ├── Produto.php        # Modelo para produtos
│   │   └── Compra.php         # Modelo para compras
│   └── Utils/                 # Utilitários diversos
│       └── FileUpload.php     # Classe para upload seguro de imagens
│
├── db/                        # Scripts SQL e estrutura do banco
│   └── schema.sql             # Definição das tabelas do banco (MariaDB)
│
├── vendor/                    # Dependências instaladas via Composer (ex: dompdf)
│
├── composer.json              # Lista de dependências PHP do projeto
├── composer.lock              # Versões travadas das dependências
│
├── Dockerfile                 # Define o ambiente Docker da aplicação PHP
├── docker-compose.yml         # Orquestração Docker (PHP + MariaDB)
├── setup.sh                   # Script de instalação e configuração automatizada
```


## 🗄️ Estrutura do Banco de Dados

O banco de dados `gatepass_db` é implementado em MariaDB e possui as seguintes tabelas principais:

* **`usuarios`**: Armazena as informações dos usuários (vendedores/administradores) do sistema.
    * `id_usuario` (PK, INT, Auto-Increment)
    * `nome` (VARCHAR)
    * `email` (VARCHAR, UNIQUE)
    * `senha` (VARCHAR, hashed)
    * `data_cadastro` (TIMESTAMP)
* **`clientes`**: Armazena as informações dos clientes (compradores) que realizam as compras.
    * `id_cliente` (PK, INT, Auto-Increment)
    * `nome` (VARCHAR)
    * `email` (VARCHAR, UNIQUE)
    * `senha` (VARCHAR, hashed)
    * `cpf` (VARCHAR, UNIQUE, NULLable)
    * `telefone` (VARCHAR, NULLable)
    * `data_cadastro` (TIMESTAMP)
* **`produtos`**: Contém os detalhes dos produtos/ingressos disponíveis para venda.
    * `id_produto` (PK, INT, Auto-Increment)
    * `id_usuario` (FK para `usuarios.id_usuario` - vendedor que cadastrou)
    * `nome` (VARCHAR)
    * `descricao` (TEXT, NULLable)
    * `preco` (DECIMAL)
    * `quantidade_total` (INT)
    * `quantidade_disponivel` (INT)
    * `quantidade_reservada` (INT)
    * `data_reserva` (TIMESTAMP, NULLable)
    * `reservado_por_cliente_id` (FK para `clientes.id_cliente`, NULLable - cliente que reservou)
    * `url_foto_perfil` (VARCHAR, NULLable)
    * `url_foto_fundo` (VARCHAR, NULLable)
    * `data_cadastro` (TIMESTAMP)
* **`compras`**: Registra cada transação de compra.
    * `id_compra` (PK, INT, Auto-Increment)
    * `id_produto` (FK para `produtos.id_produto`)
    * `id_cliente` (FK para `clientes.id_cliente`)
    * `id_usuario_vendedor` (FK para `usuarios.id_usuario` - vendedor do produto)
    * `quantidade_comprada` (INT)
    * `valor_total` (DECIMAL)
    * `metodo_pagamento` (VARCHAR)
    * `data_compra` (TIMESTAMP)

## 🤝 Como Contribuir

Contribuições são muito bem-vindas! Se você deseja colaborar com o projeto:

1.  **Faça um Fork** do repositório.
2.  **Crie uma nova Branch** para sua feature (`git checkout -b feature/minha-nova-funcionalidade`).
3.  **Implemente suas alterações** e certifique-se de que o código segue os padrões do projeto.
4.  **Faça Testes** para garantir que suas mudanças não introduzam bugs e funcionem conforme o esperado.
5.  **Faça Commit** de suas mudanças (`git commit -m "feat: Adiciona nova funcionalidade X"`).
6.  **Faça Push** para a sua Branch (`git push origin feature/minha-nova-funcionalidade`).
7.  **Abra um Pull Request** detalhando as mudanças realizadas.

## 📄 Licença

Este projeto está licenciado sob a Licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

**Desenvolvido por:**

* **Nome:** Jonathan Bufon
* **Email:** jonathanbufon@gmail.com

---
**Chapecó, Santa Catarina, Brasil.**
**Data da Documentação Final:** 26 de Julho de 2025.
=======

# gatepass
Projeto Final do curso DEV Evolution

