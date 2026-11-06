# Ambiente local e testes

Este ambiente usa PHP 8.4 em Docker, com SQLite e Xdebug. Não exige alterar o
PHP instalado no computador nem iniciar o MySQL do Sail. O `composer.lock`
atual exige PHP >= 8.4.1, apesar de `composer.json` permitir PHP 8.2.
O PHP do container tem limite de memória de 512 MB para comportar a medição de
cobertura e aceita uploads de até 50 MB (os formulários aplicam seus limites).

Pré-requisitos: Docker com Compose e Node.js 22 com npm.
Execute os comandos na raiz do repositório.

## Preparação

```bash
docker compose -f compose.testing.yaml build
test -f .env || cp .env.example .env
```

No `.env`, substitua os valores que contêm `${...}` pelas configurações locais:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8085
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=/app/database/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

Se o `.env` já existir, preserve suas demais configurações. Nunca versione esse
arquivo nem credenciais. Em Linux, se seu usuário não for UID/GID 1000, exporte
`LOCAL_UID=$(id -u)` e `LOCAL_GID=$(id -g)` antes de usar o Compose.

```bash
docker compose -f compose.testing.yaml run --rm php composer install --no-interaction --prefer-dist
docker compose -f compose.testing.yaml run --rm php php artisan key:generate
npm ci
npm run build
```

## Executar os testes

```bash
docker compose -f compose.testing.yaml run --rm php composer test
```

O serviço `php` define SQLite em memória: cada execução usa dados descartáveis.
Os testes que usam `RefreshDatabase` executam as migrations automaticamente.
Não é necessário executar os seeders da aplicação para a suíte.

Para executar um arquivo ou filtrar um cenário:

```bash
docker compose -f compose.testing.yaml run --rm php vendor/bin/pest tests/Feature/Admin/MapProjectsTest.php
docker compose -f compose.testing.yaml run --rm php vendor/bin/pest --filter='approved projects'
```

## Medir cobertura

```bash
mkdir -p coverage
XDEBUG_MODE=coverage docker compose -f compose.testing.yaml run --rm php vendor/bin/pest --coverage --coverage-html=coverage/html --coverage-clover=coverage/clover.xml --log-junit=coverage/junit.xml
```

Abra `coverage/html/index.html` no navegador para inspecionar as linhas executadas.
A medição inclui `app/`, conforme `phpunit.xml`; não mede templates Blade,
JavaScript, CSS ou o esquema GraphQL. Uma linha executada não garante que todas
as suas regras foram verificadas por asserções. Testes ignorados não validam
comportamento.

## Executar o site

O serviço `app` usa um arquivo SQLite separado dos testes:

```bash
touch database/database.sqlite
docker compose -f compose.testing.yaml run --rm -e DB_DATABASE=/app/database/database.sqlite php php artisan migrate
docker compose -f compose.testing.yaml run --rm php php artisan storage:link
docker compose -f compose.testing.yaml --profile app up -d app
```

Acesse http://localhost:8085. O banco começa vazio, exceto pelos dados criados
pelas migrations. O `db:seed` atual falha em `UserSeeder` por referenciar
`Subject::SUBJECT_NAMES_IMAGES`, que não existe. Até corrigir esse problema,
use factories nos testes; os dados completos de demonstração não são requisito
para executar a suíte. Uma tentativa de seed pode deixar os dados anteriores à
falha gravados no banco local.

O endereço dos testes é `http://localhost`, igual ao CI. O servidor usa a porta
8085. Essa separação também evita interferir no teste existente de assinatura
de upload, que encaminha o host do proxy sem sua porta.

Para parar o servidor:

```bash
docker compose -f compose.testing.yaml --profile app down
```

O ambiente SQLite acompanha a escolha do CI atual. Testar também em MySQL é uma
etapa adicional para verificar diferenças de SQL, índices e constraints.
