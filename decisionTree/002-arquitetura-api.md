# DR-002 — Arquitetura da API JWT

**Status:** ✅ Aceita · **Autor:** Tech Lead · **Data:** 2026-10-05

## Visão geral

API **stateless** de autenticação: gera e valida **JWT (RFC 7519)**, devolve o usuário e
a **role** (rule) associada. O React consome a API via HTTP com header `Authorization: Bearer`.

```
┌───────────────────┐  POST /api/login        ┌─────────────────────────┐
│ React (Vite:5173) │ ──────────────────────► │  Laravel API (:8000)    │
│                   │ ◄──── access_token ──── │  guard "api" (jwt)      │
│  AuthContext      │                         │  ─────────────────────  │
│  + localStorage   │  GET /api/me            │  • AuthController       │
│                   │  GET /api/role          │  • RoleController       │
│  ProtectedRoute   │  POST /api/logout       │  • EnsureUserHasRole    │
│  + RoleBadge      │  GET /api/admin/ping    │  • blacklist (logout)   │
└───────────────────┘ ◄──── JSON + 401/403 ── └─────────────────────────┘
```

## Contrato de endpoints

| Método | Rota               | Auth            | Descrição                                  | Sucesso | Erros |
|--------|--------------------|-----------------|--------------------------------------------|---------|-------|
| POST   | `/api/register`    | pública         | Cria usuário (role sempre `user`) + token  | 201     | 422   |
| POST   | `/api/login`       | pública         | Valida credenciais e **gera o JWT**        | 200     | 401, 422 |
| GET    | `/api/me`          | `auth:api`      | **Valida o token** e retorna o usuário + role | 200  | 401   |
| GET    | `/api/role`        | `auth:api`      | **Valida o token** e retorna `{ "role": "..." }` | 200 | 401 |
| POST   | `/api/refresh`     | `auth:api`      | Renova o token                             | 200     | 401   |
| POST   | `/api/logout`      | `auth:api`      | Invalida o token (blacklist)               | 200     | 401   |
| GET    | `/api/admin/ping`  | `auth:api` + `role:admin` | Demo de validação de **rule**     | 200     | 401, 403 |

### Contratos JSON (principais)

```jsonc
// POST /api/login  (200)
{ "access_token": "eyJ...", "token_type": "bearer", "expires_in": 3600 }

// GET /api/me (200)
{ "user": { "id": 1, "name": "...", "email": "...", "role": "admin" } }

// GET /api/role (200)
{ "role": "admin" }

// Erro de autenticação (401) — token ausente, inválido ou expirado
{ "message": "Token has expired" }  // ou "Unauthenticated."

// Erro de autorização (403) — autenticado sem a rule exigida
{ "message": "Esta ação requer a role 'admin'." }
```

## Estrutura de pastas

```
backend/                       # API Laravel (PHP 8.2, container)
  app/Http/Controllers/Api/    # AuthController, RoleController
  app/Http/Middleware/         # EnsureUserHasRole (rule:admin)
  app/Models/User.php          # implements JWTSubject + role no claim
  database/migrations/         # users + add_role_to_users_table
  database/seeders/            # AdminUserSeeder (contas de demo)
  routes/api.php               # contrato da tabela acima
frontend/                      # SPA React (Vite, :5173)
  src/context/AuthContext.jsx  # token no localStorage + /api/me
  src/api.js                   # fetch wrapper com Bearer token
  src/pages/                   # Login, Home, Admin
decisionTree/                  # decisões do Tech Lead (este diretório)
codeReview/                    # registros do DEV + relatório do QA
```

## Fluxo de login (sequência)

1. `POST /api/login` com e-mail/senha → Auth::attempt (guard `jwt`).
2. Sucesso → `auth('api')->login($user)` → `createToken` → retorna `access_token` (claims incluem `role`).
3. Front guarda o token no `localStorage` e chama `GET /api/me` para montar a sessão.
4. Requisições subsequentes levam `Authorization: Bearer <token>`; middleware `auth:api` valida.
5. `GET /api/role` devolve a rule em cache do token; páginas/rotas do front reagem a ela.
