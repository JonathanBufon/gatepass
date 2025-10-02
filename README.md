# Documentação da API do Projeto GatePass

## Boas Práticas de Contribuição

Para facilitar a colaboração no projeto e manter um fluxo de trabalho organizado, seguem algumas recomendações:

- **IDE recomendada:**  
  Utilize as ferramentas da **JetBrains** (PhpStorm ou WebStorm).
  > Estudantes da Unochapecó e de outras universidades conseguem uma licença **estudante vitalícia e gratuita**.

- **Configuração de acesso ao repositório:**
    1. Crie um **token de acesso pessoal (API Token)** no GitHub para autenticação.
    2. Configure sua chave **SSH** local e adicione ao GitHub.
    3. Clone o projeto usando SSH:
       ```bash
       git clone git@github.com:JonathanBufon/gatepass.git
       ```

- **Ambiente de desenvolvimento:**
    - Caso vá trabalhar no **frontend (React)**, utilize o **WebStorm**.
    - Caso vá trabalhar na **API (PHP)**, utilize o **PhpStorm**.

- **Fluxo de branchs (Git Flow simplificado):**
    - Sempre crie uma branch a partir da `main` para cada nova funcionalidade ou correção.
    - Nomeie a branch de forma clara, por exemplo:
        - `feature/autenticacao-jwt`
        - `fix/corrige-login-null`
    - Ao finalizar, abra um **Pull Request** para revisão antes do merge.

- **Commits semânticos:**  
  Utilize convenções de commits semânticos para manter o histórico limpo e rastreável. Exemplos:
    - `feat: adiciona autenticação JWT`
    - `fix: corrige erro ao validar ingresso`
    - `docs: atualiza README com estrutura do projeto`

---

## Introdução

Este documento detalha o commit inicial da API para o projeto GatePass, cujo objetivo é fornecer a base para a futura arquitetura de backend. Este commit é um **esboço** e não representa a versão final da API.

## Detalhes do Commit

**ID do Commit:** `create(gatepass-api)`

**Mensagem do Commit:** `feat: implementando a API inicial para o projeto Gatepass`

**Corpo da Mensagem:**
- Mapeamento de todas as rotas e endpoints.
- Inclusão da camada de rotas e esboço da estrutura inicial da API.
- Este commit representa a base inicial para a construção da API.

---

## Próximos Passos e Status Atual

A arquitetura do backend está sendo cuidadosamente estudada e estruturada para garantir a robustez e escalabilidade do projeto. As próximas etapas incluem:

- **Desenvolvimento da camada de `controller`:** Implementar a lógica de negócio para cada endpoint.
- **Refatoração da API:** O código será otimizado e aprimorado de acordo com a arquitetura final.
- **Implementação das demais camadas:** Conforme a arquitetura for definida, as camadas de serviço, repositório e modelo serão adicionadas para completar a estrutura do backend.


### Funcionalidades e Boas Práticas a Implementar

Para garantir que a API seja segura, escalável e fácil de manter, ainda é necessário implementar:

- **Autenticação e Autorização:**  
  Uso de **JWT** para autenticação stateless, com suporte a refresh tokens e middleware para controle de permissões.  
  *Importância:* garante que apenas usuários autorizados possam comprar, vender ou gerenciar ingressos.

- **Segurança:**  
  Implementar boas práticas como HTTPS, proteção contra SQL Injection (ORM/Query Builder), rate limiting, validação de entrada e sanitização.  
  *Importância:* a API lida com transações financeiras e deve evitar fraudes ou ataques.

- **Documentação (OpenAPI/Swagger):**  
  Criar documentação interativa acessível em `/api/v1/docs`, com exemplos de requisições e respostas.  
  *Importância:* facilita a integração com parceiros e o desenvolvimento de clientes.

- **Testes Automatizados:**  
  Testes unitários e de integração usando PHPUnit ou Pest, com banco em memória ou mockado.  
  *Importância:* garante confiabilidade e evita regressões em funcionalidades críticas.

- **Logs e Monitoramento:**  
  Uso de Monolog ou outra solução para logs de erros, requisições e transações, além de integração com ferramentas de monitoramento (Sentry, Grafana, Prometheus).  
  *Importância:* possibilita rastrear falhas e monitorar a saúde da aplicação em produção.

- **Banco de Dados com Migrations e Seeds:**  
  Criar scripts versionados para migração do banco e seeds com dados iniciais (usuário admin, eventos de teste).  
  *Importância:* mantém consistência entre ambientes de desenvolvimento, teste e produção.

- **Padronização Arquitetural (Camadas):**  
  Organização do código em **Controller → Service → Repository → Database**.  
  *Importância:* facilita manutenção, testes e evolução do projeto.

- **Infraestrutura (DevOps):**  
  Uso de Docker e `docker-compose` (já iniciado), além de integração contínua (CI/CD) para rodar testes automaticamente e facilitar deploys.  
  *Importância:* garante agilidade e confiabilidade no ciclo de desenvolvimento.

- **Cenário final Ideal:**

```
gatepass/
├── api/
│   └── v1/
│       ├── controllers/    # NOVO: Camada de Apresentação: Lida com Requests e Responses HTTP.
│       │   └── UsuarioController.php
│       ├── repositories/   # NOVO: Camada de Acesso a Dados: A única que "fala" com o banco.
│       │   └── UsuarioRepository.php
│       └── services/       # NOVO: Camada de Serviço: Contém toda a lógica de negócio.
│           └── UsuarioService.php
│
├── config/                 # NOVO: Centraliza as configurações da aplicação.
│   ├── routes.php          # Define todas as rotas da API para o componente symfony/routing.
│   └── services.php        # "Ensina" o Container a criar todos os serviços (Injeção de Dependência).
│
├── db/                     # Mantido para ativos do projeto, não para o código da aplicação.
│   └── schema.sql          # O script para criar a estrutura do banco de dados.
│
├── middlewares/            # NOVO: Para a lógica que roda antes dos controllers (agora como Event Listeners).
│   └── AuthMiddleware.php  # Exemplo: Verificação de token de autenticação.
│   └── CorsMiddleware.php  # NOVO: Adiciona cabeçalhos CORS para permitir acesso do frontend.
│
├── public/                 # A única pasta publicamente acessível (Document Root).
│   └── index.php           # Ponto de Entrada Único (Front Controller) para todas as requisições.
│
├── src/                    # Código fonte principal, o "Core" da sua aplicação.
│   └── Core/
│       ├── Kernel.php      # NOVO: O "coração" da aplicação. Inicializa tudo.
│       └── AuthService.php   # REFATORADO: Serviço para gerar e validar tokens JWT.
│   ├── Models/             # Classes que representam as entidades do banco (ex: Usuario.php).
│   └── Utils/              # Classes utilitárias reutilizáveis (ex: FileUpload.php).
│
├── tests/                  # NOVO: Pasta para os testes automatizados da sua API.
│
├── validators/             # NOVO: Para as classes de validação de dados de entrada.
│   └── ClienteValidator.php
│
├── vendor/                 # Pasta gerenciada pelo Composer, contém as dependências (Symfony, etc.).
│
├── .env                    # EM BREVE: Arquivo para variáveis de ambiente locais (NÃO vai para o Git).
├── .env.example            # EM BREVE: Um exemplo de como o arquivo .env deve ser.
├── composer.json           # ATUALIZADO: Define as dependências e o autoloading do projeto.
├── composer.lock           # Trava as versões exatas das dependências.
└── README.md               # ATUALIZADO: Documentação geral do projeto.
```
---

## Contato e Suporte

Caso necessite de ajuda, tenha dúvidas ou queira contribuir com o desenvolvimento da API, sinta-se à vontade para entrar em contato com **Jonathan Bufon** através do e-mail:

- **E-mail:** `jonathanbufon@gmail.com`
- **Assunto:** `Commit da API do GatePass`