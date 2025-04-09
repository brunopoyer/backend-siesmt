#!/bin/bash

# Aguarda o PostgreSQL iniciar
echo "Aguardando PostgreSQL..."
while ! nc -z db 5432; do
  sleep 0.1
done
echo "PostgreSQL iniciado"

# Aguarda o MinIO iniciar
echo "Aguardando MinIO..."
while ! nc -z minio 9000; do
  sleep 0.1
done
echo "MinIO iniciado"

# Instala dependências
composer install

# Gera chaves
php artisan key:generate
php artisan jwt:secret

# Limpa cache
php artisan config:clear
php artisan cache:clear

# Executa migrações
php artisan migrate --force

# Configura o MinIO
mc alias set myminio http://minio:9000 minioadmin minioadmin
mc mb myminio/laravel || true
mc policy set public myminio/laravel

# Inicia o servidor
php artisan serve --host=0.0.0.0
