FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    netcat-traditional \
    postgresql-client \
    libpq-dev

# Instala extensões do PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Instala a extensão Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala o MinIO Client
RUN curl -O https://dl.min.io/client/mc/release/linux-amd64/mc && \
    chmod +x mc && \
    mv mc /usr/local/bin/

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto
COPY . .

# Define as permissões
RUN chown -R www-data:www-data /var/www

# Expõe a porta 8000
EXPOSE 8000

# Define o script de entrada
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
