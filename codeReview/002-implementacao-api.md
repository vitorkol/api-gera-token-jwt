# CR-002 — Implementação da API JWT

**Responsável:** Dev Fullstack · **Data:** 2026-10-06 · **Delega de:** Tech Lead (DR-002, DR-003)

## Endpoints implementados (contrato DR-002)

| Método | Rota              | Auth                        | Resultado nos testes manuais            |
|--------|-------------------|-----------------------------|------------------------------------------|
| POST   | `/api/register`   | pública                     | 201 + token (role fixa `user`)          |
| POST   | `/api/login`      | pública                     | 200 + `access_token` / 401 credenciais  |
| GET    | `/api/me`         | `auth:api`                  | 200 usuário + role                      |
| GET    | `/api/role`       | `auth:api`                  | 200 `{"role":"admin"}` / 401 sem token  |
| POST   | `/api/refresh`    | `auth:api`                  | 200 novo token                          |
| POST   | `/api/logout`     | `auth:api`                  | 200, token na blacklist                 |
| GET    | `/api/admin/ping` | `auth:api` + `role:admin`   | 200 admin / 403 demais / 401 sem token  |

## Arquivos criados / alterados

| Arquivo | Tipo | Descrição |
|---------|------|-----------|
| `app/Models/User.php` | alterado | `implements JWTSubject`; `role` no fillable; claim `role` no payload |
| `app/Http/Controllers/Api/AuthController.php` | novo | register, login, me, refresh, logout |
| `app/Http/Controllers/Api/RoleController.php` | novo | `show` (GET /role) e `adminPing` (demo de rule) |
| `app/Http/Middleware/EnsureUserHasRole.php` | novo | middleware `role:admin,manager` → 403 |
| `bootstrap/app.php` | alterado | rota `api:`, alias `role`, exceções JWT → 401 JSON |
| `config/auth.php` | alterado | guard `api` (`driver => jwt`) |
| `routes/api.php` | novo | contrato acima |
| `database/migrations/2026_10_05_000000_add_role_to_users_table.php` | novo | coluna `role` default `user`, indexada |
| `database/seeders/AdminUserSeeder.php` | novo | contas demo admin/user |
| `database/seeders/DatabaseSeeder.php` | alterado | chama AdminUserSeeder |

## Decisões do DEV (à luz dos DRs)

- **Laravel 12** não tem `Kernel.php`/`Handler.php`: alias de middleware e handlers de
  exceção registrados em `bootstrap/app.php` (padrão da versão).
- **Login inválido → 401** (não 422), conforme DR-003; validação de formato → 422.
- **`/api/role` lê do banco** (`auth('api')->user()->role`) — fonte de verdade; o claim
  `role` do payload existe como conveniência para inspeção do token.
- **Logout** usa `auth('api')->logout()` → token entra na blacklist (drift + blacklist habilitados no config default).
- **Register** grava sempre `role = user` (não há auto-promoção — DR-003).
- 401 sem token aparece como `{"message":"Unauthenticated."}` (resposta default do guard
  para request sem token; exceções específicas expiradas/inválidas caem nos handlers do bootstrap).

## Sanity check do DEV (evidência)

```
login ok, token len: 336
--- /api/role:    {"role":"admin"}
--- /api/me:      {"user":{...,"role":"admin"}}
--- /api/admin/ping: 200 Pong! Você tem a role admin. 🛡️
--- sem token:    {"message":"Unauthenticated."} [401]
```

**Status:** implementação concluída → handoff para Q.A (CR-004).
