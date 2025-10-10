# Usamos a imagem oficial do PHP 8.1 com FPM (FastCGI Process Manager)
FROM php:8.1-fpm

# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    pdo_mysql \
    zip

# Instala o Composer (gerenciador de dependências do PHP)
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Define o diretório de trabalho dentro do container
WORKDIR /var/www/html
