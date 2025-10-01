# backend/Dockerfile
FROM php:8.1-fpm-alpine

# Define o diretório de trabalho
WORKDIR /var/www/html

# Variáveis de ambiente para evitar problemas com Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala dependências do sistema e extensões PHP necessárias
RUN apk --no-cache update && apk add --no-cache \
    libjpeg-turbo-dev \
    libpng-dev \
    libfreetype-dev \
    zlib-dev \
    oniguruma-dev \
    curl \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql bcmath \
    && apk del --no-cache curl unzip git \
    && rm -rf /var/cache/apk/*

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o código do projeto (mantendo cache do Composer)
COPY ./composer.* ./

# Instala dependências PHP do projeto (se houver composer.json)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Copia o restante do código
COPY . .

# Porta exposta do PHP-FPM
EXPOSE 9000

