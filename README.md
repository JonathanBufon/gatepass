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
gatepass-api/
├── api/v1
│   ├── controllers
│   ├── services
│   ├── repositories
│   ├── middlewares
│   ├── validators
│   ├── docs
│   └── tests
├── db
│   ├── migrations
│   └── seeds
├── public/css
├── src
│   ├── config
│   └── utils
├── vendor
├── Dockerfile
├── README.md
├── composer.json
├── composer.lock
├── docker-compose.yml
└── setup.sh
```
---

## Contato e Suporte

Caso necessite de ajuda, tenha dúvidas ou queira contribuir com o desenvolvimento da API, sinta-se à vontade para entrar em contato com **Jonathan Bufon** através do e-mail:

- **E-mail:** `jonathanbufon@gmail.com`  
- **Assunto:** `Commit da API do GatePass`
