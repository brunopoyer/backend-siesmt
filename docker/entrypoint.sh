#!/bin/bash

# Aguarda o PostgreSQL iniciar
echo "Aguardando PostgreSQL..."
while ! nc -z db 5432; do
  sleep 0.1
done
echo "PostgreSQL iniciado"

# Criar banco de dados de teste se não existir
PGPASSWORD=${DB_PASSWORD} psql -h db -U ${DB_USERNAME} -tc "SELECT 1 FROM pg_database WHERE datname = 'laravel_testing'" | grep -q 1 || PGPASSWORD=${DB_PASSWORD} psql -h db -U ${DB_USERNAME} -c "CREATE DATABASE laravel_testing"

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

# Limpa cache
php artisan config:clear
php artisan cache:clear

# Executa migrações e seeders
php artisan migrate --force
php artisan db:seed --force

# Configura o MinIO
mc alias set myminio http://minio:9000 minioadmin minioadmin
mc mb myminio/laravel || true
mc policy set public myminio/laravel

# Inicia o servidor
php artisan serve --host=0.0.0.0
