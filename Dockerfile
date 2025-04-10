FROM php:8.2-fpm

# Argumentos definidos no docker-compose.yml
ARG user=laravel
ARG uid=1000

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libsodium-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar outras extensões PHP
RUN docker-php-ext-install mbstring exif pcntl bcmath sodium

# Obter último Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para executar comandos Composer e Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Definir diretório de trabalho
WORKDIR /var/www

USER $user
