FROM php:8.2-fpm

# Instala dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    netcat-traditional \
    libpq-dev

# Limpa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala MinIO Client
RUN wget https://dl.min.io/client/mc/release/linux-amd64/mc && \
    chmod +x mc && \
    mv mc /usr/local/bin/

# Define diretório de trabalho
WORKDIR /var/www

# Copia arquivos do projeto
COPY . .

# Copia script de inicialização
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configura PHP-FPM
RUN echo "listen = 9000" >> /usr/local/etc/php-fpm.d/www.conf

# Define o script de inicialização como entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
