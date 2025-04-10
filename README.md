# Sistema de Gestão de Servidores

Sistema desenvolvido em Laravel para gerenciamento de servidores efetivos e temporários, com suporte a fotos e endereços múltiplos.

## Requisitos

- Docker
- Docker Compose
- Git
- PHP 8.1+
- Composer
- PostgreSQL

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/brunopoyer/backend-siesmt.git
cd backend-siesmt
```

2. Copie o arquivo de ambiente:
```bash
cp .env.example .env
```

3. Configure as variáveis de ambiente no arquivo `.env`:
```env
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=postgres
DB_PASSWORD=secret

MINIO_ACCESS_KEY_ID=minioadmin
MINIO_SECRET_ACCESS_KEY=minioadmin
MINIO_DEFAULT_REGION=us-east-1
MINIO_BUCKET=laravel
MINIO_ENDPOINT=http://minio:9000
MINIO_URL=http://localhost:9000
```

4. Inicie os containers:
```bash
# Se você já tinha o projeto rodando antes, remova os volumes antigos
docker-compose down -v

# Inicie os containers
docker-compose up -d
```

O sistema será configurado automaticamente, incluindo:
- Instalação de dependências
- Geração de chaves
- Execução de migrações
- Configuração do MinIO

## Uso

A API estará disponível em `http://localhost:8000/api`.

### Configuração do CORS

O sistema está configurado para aceitar requisições de qualquer origem (`*`). Se você precisar restringir para origens específicas, edite o arquivo `app/Http/Middleware/Cors.php`:

```php
$response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
```

### Autenticação

- POST `/api/login` - Login com email e senha
- POST `/api/register` - Registro de novo usuário
- POST `/api/logout` - Logout (requer autenticação)
- GET `/api/me` - Obter dados do usuário autenticado

### Endpoints

Todos os endpoints requerem autenticação com Sanctum e suportam paginação.

#### Unidades
- GET `/api/unidades` - Listar unidades
- POST `/api/unidades` - Criar unidade
- GET `/api/unidades/{id}` - Obter unidade
- PUT `/api/unidades/{id}` - Atualizar unidade
- DELETE `/api/unidades/{id}` - Excluir unidade

#### Lotações
- GET `/api/lotacoes` - Listar lotações
- POST `/api/lotacoes` - Criar lotação
- GET `/api/lotacoes/{id}` - Obter lotação
- PUT `/api/lotacoes/{id}` - Atualizar lotação
- DELETE `/api/lotacoes/{id}` - Excluir lotação

#### Servidores Efetivos
- GET `/api/servidores-efetivos` - Listar servidores efetivos
- POST `/api/servidores-efetivos` - Criar servidor efetivo
- GET `/api/servidores-efetivos/{id}` - Obter servidor efetivo
- PUT `/api/servidores-efetivos/{id}` - Atualizar servidor efetivo
- DELETE `/api/servidores-efetivos/{id}` - Excluir servidor efetivo
- GET `/api/servidores-efetivos/unidade/{unidId}` - Listar servidores por unidade
- GET `/api/servidores-efetivos/busca?nome={nome}` - Buscar servidor por nome
- GET `/api/servidores-efetivos/buscar-endereco` - Buscar endereço funcional por nome

#### Servidores Temporários
- GET `/api/servidores-temporarios` - Listar servidores temporários
- POST `/api/servidores-temporarios` - Criar servidor temporário
- GET `/api/servidores-temporarios/{id}` - Obter servidor temporário
- PUT `/api/servidores-temporarios/{id}` - Atualizar servidor temporário
- DELETE `/api/servidores-temporarios/{id}` - Excluir servidor temporário

## Testes

O sistema possui testes automatizados para garantir o funcionamento correto dos endpoints da API. Os testes estão localizados no diretório `tests/Feature`.

### Executando os Testes

Para executar todos os testes:

```bash
# Dentro do container
docker-compose exec app php artisan test

# Ou localmente (se tiver o PHP instalado)
php artisan test
```

Para executar um teste específico:

```bash
docker-compose exec app php artisan test --filter=NomeDoTeste
```

Por exemplo, para executar apenas os testes de autenticação:

```bash
docker-compose exec app php artisan test --filter=AuthControllerTest
```

### Cobertura de Testes

Os testes cobrem os seguintes componentes:

1. **Autenticação** (`AuthControllerTest`)
   - Login com credenciais válidas
   - Falha de login com credenciais inválidas
   - Registro de novos usuários
   - Logout
   - Obtenção de dados do usuário autenticado

2. **Unidades** (`UnidadeControllerTest`)
   - Listagem de unidades
   - Criação de unidades
   - Visualização de unidade específica
   - Atualização de unidades
   - Remoção de unidades

3. **Lotações** (`LotacaoControllerTest`)
   - Listagem de lotações
   - Criação de lotações
   - Visualização de lotação específica
   - Atualização de lotações
   - Remoção de lotações

4. **Servidores Efetivos** (`ServidorEfetivoControllerTest`)
   - Listagem de servidores efetivos
   - Criação de servidores efetivos
   - Visualização de servidor efetivo específico
   - Atualização de servidores efetivos
   - Remoção de servidores efetivos
   - Busca por unidade
   - Busca por nome

## Testando a API com Postman

Para facilitar o teste da API, disponibilizamos uma collection do Postman. Siga os passos abaixo para configurar e usar:

1. Abra o Postman
2. Clique no botão "Import"
3. Arraste o arquivo `postman_collection.json` ou navegue até ele
4. Após importar, você verá uma nova collection chamada "API Servidores"

### Estrutura da Collection

A collection está organizada nas seguintes pastas:

1. **Autenticação**
   - Login
   - Registrar
   - Logout

2. **Unidades**
   - Listar Unidades
   - Criar Unidade
   - Atualizar Unidade

3. **Lotações**
   - Listar Lotações
   - Criar Lotação
   - Atualizar Lotação

4. **Servidores Efetivos**
   - Listar Servidores Efetivos
   - Criar Servidor Efetivo
   - Atualizar Servidor Efetivo
   - Buscar por Unidade
   - Buscar por Nome

5. **Servidores Temporários**
   - Listar Servidores Temporários
   - Criar Servidor Temporário
   - Atualizar Servidor Temporário

### Configuração do Ambiente

1. Configure as variáveis de ambiente no Postman:
   - `base_url`: Defina como `http://localhost:8000` ou a URL da sua API
   - `token`: Após fazer login, coloque o token retornado aqui

2. Para endpoints que requerem upload de arquivo (como fotos), use o Postman para selecionar um arquivo local quando necessário.

3. A paginação está configurada em vários endpoints usando os parâmetros `page` e `per_page`.

4. Todos os endpoints protegidos já estão configurados para enviar o token no cabeçalho `Authorization`.

### Fluxo de Teste

1. Primeiro, use o endpoint de Login para obter um token (ou crie uma nova conta com o endpoint Registrar)
2. Copie o token retornado e coloque na variável de ambiente `token`
3. Depois disso, você pode testar todos os outros endpoints

Cada request já está pré-configurado com exemplos de dados que você pode modificar conforme necessário.

## Estrutura do Banco de Dados

O sistema utiliza as seguintes tabelas principais:

- `pessoas`: Informações básicas das pessoas
- `servidores_efetivos`: Dados específicos dos servidores efetivos
- `servidores_temporarios`: Dados específicos dos servidores temporários
- `unidades`: Unidades administrativas
- `enderecos`: Endereços das unidades e servidores
- `lotacoes`: Lotações dos servidores nas unidades
- `fotos`: Fotos dos servidores

## Funcionalidades

- Cadastro e gestão de servidores efetivos e temporários
- Upload e gerenciamento de fotos
- Múltiplos endereços por servidor/unidade
- Sistema de lotação
- Busca por nome e unidade
- Autenticação com Sanctum
- Paginação de resultados

## Licença

Este projeto está licenciado sob a licença MIT.
