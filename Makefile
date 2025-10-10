# -----------------------------------------------------------------------------
# Makefile para o projeto GatePass
#
# Este arquivo contém atalhos para os comandos mais comuns do Docker e Composer,
# simplificando o gerenciamento do ambiente de desenvolvimento.
#
# Uso: make [comando]
# Exemplo: make up
# -----------------------------------------------------------------------------

# Adiciona ajuda para os comandos
.PHONY: help
help:
	@echo "Comandos disponíveis:"
	@echo "  up             -> Inicia os containers Docker em modo detached e constrói as imagens."
	@echo "  down           -> Para e remove os containers Docker."
	@echo "  restart        -> Reinicia os containers."
	@echo "  logs           -> Exibe os logs de todos os containers em tempo real."
	@echo "  status         -> Lista os containers em execução."
	@echo "  shell          -> Acessa o terminal (bash) do container 'php'."
	@echo "  install        -> Instala as dependências do Composer."
	@echo "  update         -> Atualiza as dependências do Composer."
	@echo "  dump-autoload  -> Regenera o mapa de autoload do Composer (muito útil após criar novas classes)."
	@echo "  build-frontend -> Executa o build de produção do frontend React."

# --- Comandos do Docker ---

.PHONY: up
up:
	@echo "Iniciando os containers Docker..."
	docker compose up --build -d

.PHONY: down
down:
	@echo "Parando os containers Docker..."
	docker compose down

.PHONY: restart
restart:
	@echo "Reiniciando os conteiners..."
	docker compose down && docker compose up --build -d

.PHONY: logs
logs:
	@echo "Exibindo os logs..."
	docker compose logs -f

.PHONY: status
status:
	@echo "Status dos containers:"
	docker compose ps

# --- Comandos de Acesso ---

.PHONY: shell
shell:
	@echo "Acessando o terminal do container 'php'..."
	docker compose exec php bash

# --- Comandos do Composer ---

.PHONY: install
install:
	@echo "Instalando dependências do Composer..."
	docker compose exec php composer install

.PHONY: update
update:
	@echo "Atualizando dependências do Composer..."
	docker compose exec php composer update

.PHONY: dump-autoload
dump-autoload:
	@echo "Regenerando o mapa de autoload do Composer..."
	docker compose exec php composer dump-autoload

# --- Comandos do Frontend ---

.PHONY: build-frontend
build-frontend:
	@echo "Construindo o frontend React para produção..."
	docker compose exec php npm --prefix ./frontend run build

.PHONY: docker-god
docker-god:
	@echo "MODO DEUS ATIVADO! Limpando tudo e recomeçando do zero..."
	@echo "   -> Parando e removendo containers, volumes e órfãos..."
	docker compose down --volumes --remove-orphans
	@echo "   -> Reconstruindo e iniciando os containers..."
	make up
	@echo "   -> Instalando dependências do Composer..."
	make install
	@echo "Ambiente recriado com sucesso!"
