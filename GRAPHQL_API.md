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