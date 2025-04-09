# Sistema de Gestão de Servidores

Sistema desenvolvido em Laravel para gerenciamento de servidores efetivos e temporários, com suporte a fotos e endereços múltiplos.

## Requisitos

- Docker
- Docker Compose
- Git

## Instalação

1. Clone o repositório:
```bash
git clone <seu-repositorio>
cd <seu-repositorio>
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

### Autenticação

- POST `/api/login` - Login com email e senha
- POST `/api/refresh` - Renovar token JWT

### Endpoints

Todos os endpoints requerem autenticação JWT e suportam paginação.

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

#### Servidores Temporários
- GET `/api/servidores-temporarios` - Listar servidores temporários
- POST `/api/servidores-temporarios` - Criar servidor temporário
- GET `/api/servidores-temporarios/{id}` - Obter servidor temporário
- PUT `/api/servidores-temporarios/{id}` - Atualizar servidor temporário
- DELETE `/api/servidores-temporarios/{id}` - Excluir servidor temporário

### Observações

- O token JWT expira em 5 minutos
- As fotos são armazenadas no MinIO com links temporários de 5 minutos
- Todas as consultas suportam paginação através dos parâmetros `page` e `per_page`
- O CORS está configurado para aceitar apenas requisições da origem definida em `APP_URL`

## Containers

- **app**: Aplicação Laravel (PHP 8.2)
- **nginx**: Servidor web
- **db**: PostgreSQL
- **minio**: Servidor de armazenamento de objetos S3

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
   - Refresh Token

2. **Pessoas**
   - Listar Pessoas
   - Criar Pessoa

3. **Servidores Efetivos**
   - Listar Servidores Efetivos
   - Criar Servidor Efetivo
   - Buscar por Unidade
   - Buscar por Nome

4. **Servidores Temporários**
   - Listar Servidores Temporários
   - Criar Servidor Temporário

5. **Unidades**
   - Listar Unidades
   - Criar Unidade

6. **Lotações**
   - Listar Lotações
   - Criar Lotação

### Configuração do Ambiente

1. Configure as variáveis de ambiente no Postman:
   - `base_url`: Defina como `http://localhost:8000` ou a URL da sua API
   - `token`: Após fazer login, coloque o token JWT retornado aqui

2. Para endpoints que requerem upload de arquivo (como fotos), use o Postman para selecionar um arquivo local quando necessário.

3. A paginação está configurada em vários endpoints usando os parâmetros `page` e `per_page`.

4. Todos os endpoints protegidos já estão configurados para enviar o token JWT no cabeçalho `Authorization`.

### Fluxo de Teste

1. Primeiro, use o endpoint de Login para obter um token
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
- Autenticação JWT
- Paginação de resultados

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Crie um Pull Request

## Licença

Este projeto está licenciado sob a licença MIT.
