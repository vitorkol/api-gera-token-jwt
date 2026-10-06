# CR-005 — Relatório do Q.A (Testes e Validações)

**Responsável:** Q.A · **Data:** 2026-10-06
**Objeto:** entrega do DEV (CR-001 a CR-004) — API JWT Laravel 12 + Frontend React 18

> 📌 **Evidências brutas (logs) armazenadas em [`evidence/`](../evidence/README.md)**, conforme DR-004.
> Este arquivo é o relatório interpretado; os logs crus das execuções estão em `evidence/*.log`.

## Veredito: ✅ APROVADO

| Validação | Resultado | Evidência |
|-----------|-----------|-----------|
| Suíte automatizada (`php artisan test`) | ✅ **21 testes, 50 assertions, 0 falhas** (13.0s) | `evidence/001-artisan-test.log` |
| Smoke test E2E (servidor real + curl) | ✅ **19 PASS / 0 FAIL** | `evidence/002-smoke-test-e2e.log` |
| Build do frontend (`npm run build`) | ✅ OK — 41 módulos, bundle 172 kB (gzip 56 kB) | `evidence/003-frontend-build.log` |
| Runtime PHP 8.2 (platform check do vendor) | ✅ `PLATFORM_OK` | CR-001 |

## A. Testes automatizados — saída do `php artisan test` (log completo: `evidence/001-artisan-test.log`)

```
   PASS  Tests\Feature\AuthApiTest
  ✓ login com credenciais validas retorna token
  ✓ login com senha incorreta retorna 401
  ✓ login com email inexistente retorna 401
  ✓ login com payload invalido retorna 422
  ✓ register cria usuario com role user e retorna token
  ✓ register com email duplicado retorna 422
  ✓ register com senha curta retorna 422
  ✓ me retorna usuario autenticado
  ✓ me sem token retorna 401
  ✓ token malformado retorna 401
  ✓ refresh retorna novo token valido
  ✓ logout invalida o token blacklist

   PASS  Tests\Feature\RoleAccessTest
  ✓ role endpoint valida token e retorna role
  ✓ role endpoint sem token retorna 401
  ✓ admin ping permitido para role admin
  ✓ admin ping negado para role user 403
  ✓ admin ping negado para role manager 403
  ✓ admin ping sem token retorna 401
  ✓ payload do jwt contem claim role

  Tests:    21 passed (50 assertions)
```

Cobertura dos requisitos do usuário:

- **"api que gera tokens usando jwt"** → login/registro retornam JWT assinado (HS256) com `role` no payload ✓
- **"validar o token do usuário para logar em um sistema"** → credenciais validadas no login; token valida a sessão em `/api/me` (401 sem/inválido/expirado/blacklist) ✓
- **"validar o token do usuário para pegar a rule"** → `/api/role` valida o token e devolve `{"role": "..."}`; middleware `role:*` aplica a rule (`403`) ✓

## B. Smoke test E2E — 19/19 PASS (log completo: `evidence/002-smoke-test-e2e.log`)

```
PASS  login admin gera token (336 chars)     PASS  admin/ping admin => 200
PASS  login user gera token (335 chars)      PASS  admin/ping user => 403
PASS  login senha errada => 401              PASS  admin/ping sem token => 401
PASS  role sem token => 401                  PASS  token adulterado => 401
PASS  role admin => 200                      PASS  refresh gera token novo
PASS  role user => 200                       PASS  me com token renovado => 200
PASS  payload role user = {"role":"user"}    PASS  logout admin => 200
PASS  me com token => 200                    PASS  me com token logoutado => 401
PASS  me sem token => 401                    PASS  register => 201
                                             PASS  novo usuario tem role user
```

Cenários críticos cobertos: **401** (sem token, malformado, adulterado, após logout),
**403** (autenticado sem a rule), **blacklist** pós-logout, **refresh** de token,
**role fixa `user`** no auto-cadastro.

## C. Frontend

- `npm run build` concluído sem erros ✓
- Fluxo validado por contrato: token em `localStorage`, `Authorization: Bearer`, auto-logout em 401, badge de role, rota `/admin` restrita no cliente + 403 real do backend.

## Riscos e recomendações (não bloqueiam)

1. **CORS aberto (`*`) para dev** — restringir `allowed_origins` em produção (DR-003).
2. **Rate limiting em `/api/login`** — adicionar `throttle` na rota antes de produção.
3. Laravel 12 roda em PHP 8.2 no contêiner; considerar `php:8.3-cli` em produção.
4. Accounts demo usam senha `password` — trocar em qualquer ambiente real.
