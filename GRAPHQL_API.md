# API GraphQL - Plataforma Cidadania Digital

## Visão Geral

Esta API GraphQL foi desenvolvida para permitir que IAs externas acessem os dados da plataforma de forma segura e estruturada.

## Endpoint

```
POST /api/graphql
```

## Autenticação

A API requer autenticação via token Bearer. O token deve ser configurado na variável de ambiente `AI_ACCESS_TOKEN`.

### Header de Autenticação
```
Authorization: Bearer seu-token-aqui
```

## Configuração

1. Configure o token de acesso no arquivo `.env`:
```env
AI_ACCESS_TOKEN=your-secure-token-here
```

2. O endpoint está configurado em `/routes/api.php` e usa o middleware `AiTokenMiddleware`.
3. A rota de compatibilidade `/graphql` exige o mesmo token, inclusive para introspecção.
   O GraphiQL em `/graphiql` consulta `/api/graphql`: informe o header `Authorization`
   na aba Headers. O login do painel não substitui esse token.
4. Após atualizar o código, limpe/recrie os caches de configuração, rotas e schema
   conforme o processo de deploy (`php artisan optimize:clear` e
   `php artisan lighthouse:clear-cache`; opcionalmente `php artisan lighthouse:cache`).
   Confirme que `AI_ACCESS_TOKEN` está configurado antes de disponibilizar a API.

## Contrato de leitura para integração de conteúdo

Esta ampliação utiliza campos e relacionamentos existentes; não exige migrations,
novos cadastros ou alterações nos registros. Não implementa importação para Atopos,
agendamento, remoções no destino ou login unificado.

### Recursos publicados e autoria editorial

```graphql
query Conteudos($page: Int!) {
  resources(is_published: true, first: 50, page: $page) {
    data {
      id title description source source_url published_at created_at updated_at
      is_published author authors { id name }
      publisher language coverage contributor copy_rights original_date
      format_id format identifier key_words
      subject { id name slug }
      tags { id name }
    }
    paginatorInfo { currentPage lastPage total hasMorePages }
  }
}
```

- `is_published` é derivado de `published_at != null`, seguindo o painel e o
  `scopePublished` existente. Não utiliza a antiga coluna booleana de mesmo nome.
- O filtro aceita `true` (publicados), `false` (rascunhos) ou ausência (ambos,
  preservando o comportamento anterior). Também funciona em `resource(id:, is_published:)`.
- Publicação mantém a semântica existente: data futura não representa agendamento.
- Recursos excluídos logicamente continuam omitidos. Ausência de um registro numa
  resposta parcial não deve ser interpretada pelo consumidor como exclusão.
- `author` é o texto legado; `authors` é a relação estruturada. `user` é a conta
  criadora, não a autoria editorial. Metadados opcionais podem ser nulos e a relação
  de autores pode estar vazia nos registros antigos.
- `source` preserva o valor armazenado; `source_url` resolve arquivos enviados pelo
  mecanismo público de uploads já utilizado no site. Não é uma imagem de capa nova.
- O filtro legado `user_id` consulta `created_by`, a chave real do modelo.
- O consumidor deve percorrer todas as páginas; não há filtro incremental de revisões
  nem feed de exclusões nesta versão. IDs devem ser associados ao ambiente de origem.

### Séries e episódios ordenados

```graphql
{
  series(is_active: true, first: 20, page: 1) {
    data {
      id slug title description thumbnail background_image background_credit updated_at
      videos(is_active: true) {
        id video_id title description is_active updated_at
        source_author source_title source_channel source_year
        episode { title description position created_at updated_at }
      }
    }
    paginatorInfo { hasMorePages }
  }
}
```

`videos` segue a ordem cadastrada na relação `series_videos` (posição e data de
inclusão). `episode` contém título/descrição/posição específicos daquela série.
O mesmo vídeo pode estar em várias séries com metadados diferentes. Fora de uma
série, `Video.episode` é nulo. Sem filtro, vídeos inativos continuam disponíveis ao
cliente autenticado; a consulta de integração deve solicitar `is_active: true`.
Alterações na relação têm seu próprio `updated_at`; não dependa apenas do
`updated_at` da série para detectar reordenações.

### Projetos do mapa

```graphql
{
  mapProjects(first: 50, page: 1) {
    data {
      id name country city lat lng proposers nature purpose description
      themes tics website instagram facebook tiktok app researcher
      created_at updated_at
    }
    paginatorInfo { total hasMorePages }
  }
}
```

Essa consulta sempre restringe a projetos aprovados com coordenadas (`onMap`),
ordena por ID e reutiliza `toMapArray`, a projeção pública existente. Links que não
sejam HTTP(S) são omitidos, e `app` é nulo se o projeto não declara aplicativo.
Notas internas e chaves de importação não integram o tipo GraphQL. Não há consulta
individual que permita contornar o filtro de aprovação.

### Compatibilidade e autenticação

Os campos novos são aditivos. A exigência de Bearer em `/graphql` é uma mudança
intencional: clientes que utilizavam essa rota sem token precisam passar o header.
O token existente dá acesso ao schema completo; esta PR não cria um token com
escopo restrito a conteúdos publicados. Nunca o incorpore ao app Flutter ou ao
JavaScript público; a futura sincronização deve executá-lo no backend.

### Validação local

```bash
docker compose -f compose.testing.yaml run --rm --no-deps php php vendor/bin/pest tests/Feature/Api
```

Os testes utilizam SQLite em memória, dados sintéticos e cache de schema desativado
para não reutilizar um schema antigo. Nenhuma chamada ao servidor remoto é necessária.


## Schema Disponível

### Tipos

#### User
```graphql
type User {
    id: ID!
    name: String!
    email: String!
    created_at: DateTime!
    updated_at: DateTime!
    posts: [Post!]!
    comments: [Comment!]!
}
```

#### Post
```graphql
type Post {
    id: ID!
    title: String!
    slug: String
    content: String
    image_url: String
    is_published: Boolean!
    published_at: DateTime
    created_at: DateTime!
    updated_at: DateTime!
    user: User!
    comments: [Comment!]!
}
```

#### Comment
```graphql
type Comment {
    id: ID!
    content: String!
    created_at: DateTime!
    updated_at: DateTime!
    post: Post!
    user: User!
}
```

### Queries Disponíveis

#### Usuários
```graphql
# Buscar usuário por ID
user(id: ID): User

# Buscar usuário por email
user(email: String): User

# Listar usuários com paginação
users(name: String): [User!]!
```

#### Posts
```graphql
# Buscar post por ID
post(id: ID!): Post

# Listar posts com filtros
posts(
    title: String,
    user_id: ID,
    is_published: Boolean,
    first: Int,
    page: Int
): [Post!]!
```

#### Comentários
```graphql
# Buscar comentário por ID
comment(id: ID!): Comment

# Listar comentários com filtros
comments(
    post_id: ID,
    user_id: ID,
    first: Int,
    page: Int
): [Comment!]!
```

## Exemplos de Uso

### Listar Usuários
```bash
curl -X POST "http://localhost/api/graphql" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{"query": "{ users { data { id name email } } }"}'
```

### Buscar Posts com Relacionamentos
```bash
curl -X POST "http://localhost/api/graphql" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "query": "{ 
      posts(first: 5) { 
        data { 
          id 
          title 
          content 
          user { 
            id 
            name 
          } 
          comments { 
            id 
            content 
            user { 
              name 
            } 
          } 
        } 
      } 
    }"
  }'
```

### Buscar Comentários
```bash
curl -X POST "http://localhost/api/graphql" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "query": "{ 
      comments(first: 10) { 
        data { 
          id 
          content 
          user { 
            name 
          } 
          post { 
            title 
          } 
        } 
      } 
    }"
  }'
```

## Paginação

As queries que retornam listas suportam paginação:

```graphql
{
  posts(first: 10, page: 2) {
    data {
      id
      title
    }
    paginatorInfo {
      currentPage
      lastPage
      total
      count
      hasMorePages
    }
  }
}
```

## Tratamento de Erros

### Token Inválido ou Ausente
```json
{
  "errors": [
    {
      "message": "Invalid or missing AI access token",
      "extensions": {
        "category": "authentication"
      }
    }
  ]
}
```

### Query Inválida
```json
{
  "errors": [
    {
      "message": "Cannot query field \"fieldName\" on type \"TypeName\".",
      "locations": [{"line": 1, "column": 10}]
    }
  ]
}
```

## Arquivos Importantes

- `/routes/api.php` - Definição da rota GraphQL
- `/app/Http/Middleware/AiTokenMiddleware.php` - Middleware de autenticação
- `/graphql/schema.graphql` - Schema GraphQL
- `/app/Http/Controllers/GraphQLController.php` - Controller GraphQL
- `/app/Models/Comment.php` - Model de comentários

## Segurança

- ✅ Autenticação obrigatória via token Bearer
- ✅ Validação de token usando `hash_equals()` para prevenir timing attacks
- ✅ Middleware customizado para controle de acesso
- ✅ Separação da API em rotas dedicadas (`/api/*`)

## Suporte

Para questões técnicas ou solicitações de novas funcionalidades, entre em contato com a equipe de desenvolvimento.