# DR-003 — Segurança, Roles (rules) e Exceções

**Status:** ✅ Aceita · **Autor:** Tech Lead · **Data:** 2026-10-05

## Ciclo do token (`config/jwt.php`)

| Parâmetro   | Valor default | Observação |
|-------------|---------------|------------|
| Algoritmo   | HS256         | Segredo em `JWT_SECRET` (gerado por `php artisan jwt:secret`, nunca versionado). |
| TTL         | 60 min        | Token de acesso curto; front renova com `/api/refresh`. |
| Refresh TTL | 14 dias       | Janela máxima para renovar. |
| Blacklist   | habilitada    | `/api/logout` invalida o token imediatamente. |

## Modelo de roles

- Coluna `role` em `users` (`string`, default `'user'`) com valores `admin`, `manager`, `user`.
- A role vai **no claim do payload** (`getJWTCustomClaims`) e também é lida do banco em `/api/role`
  (fonte de verdade = banco; claim = conveniência).
- **Auto-cadastro nunca promove role**: `POST /api/register` grava sempre `user`.
- Middleware `EnsureUserHasRole` (alias `role:admin`) nega com **403** quando autenticado sem a rule.

## Tratamento de exceções JWT (Handler)

Exceções da lib são convertidas em **401 JSON** (nunca 500):

| Exceção                      | Resposta                         |
|------------------------------|----------------------------------|
| `TokenExpiredException`      | 401 `"Token has expired"`        |
| `TokenInvalidException`      | 401 `"Token is invalid"`         |
| `TokenBlacklistedException`  | 401 `"Token has been blacklisted"` |
| `JWTException` (genérica)    | 401 `"Token not provided"` / mensagem da lib |
| Credenciais inválidas        | 401 `"Email ou senha inválidos."` |

## CORS

- `config/cors.php` do skeleton L10 já libera `api/*` para `*` — aceito **apenas para dev**.
- Pendência de produção (não bloqueia): restringir `allowed_origins` à URL do front.

## Evoluções futuras (não bloqueiam esta entrega)

- Refresh token rotativo + refresh em httpOnly cookie.
- Tabela `roles` + `role_user` (N:N) e permissions granulares.
- Rate limit em `/api/login` (`throttle:5,1`).
