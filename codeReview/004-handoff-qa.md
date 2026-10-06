# CR-004 — Handoff do DEV para o Q.A

**Responsável:** Dev Fullstack · **Data:** 2026-10-06
**Para:** Q.A — **entrega da implementação para testes e validações** (etapa 3 do DR-004)

## Escopo entregue

1. **Backend** (`backend/`): Laravel 12 + `php-open-source-saver/jwt-auth`
   - API gera JWT no login/registro; valida token em `/api/me`, `/api/role`, `/api/refresh`, `/api/logout`;
   - Validação de rule via middleware `role:*` (demo: `/api/admin/ping` exige `admin`);
   - Banco SQLite migrado + seed (contas demo).
2. **Frontend** (`frontend/`): React 18 + Vite 5 — login, cadastro, sessão com JWT, badge de role, rota admin.

## Ambiente do Q.A (comandos prontos no README da raiz)

```bash
# Backend (servidor + testes) — PHP 8.2 via Docker:
docker run --rm -d --name jwt-api -u $(id -u):$(id -g) -e HOME=/tmp \
  -p 8000:8000 -v "$(pwd)/backend":/app -w /app \
  php:8.2-cli php artisan serve --host=0.0.0.0 --port=8000

docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp \
  -v "$(pwd)/backend":/app -w /app php:8.2-cli php artisan test

# Frontend:
cd frontend && npm install && npm run build
```

## Plano de teste sugerido (Q.A)

### A. Feature tests automatizados (`php artisan test`)
- [ ] Login com credenciais válidas → 200 + `access_token` + `expires_in`
- [ ] Login com senha errada → 401; e-mail inexistente → 401
- [ ] Login com payload inválido → 422
- [ ] `GET /api/role` sem token → 401; com token → 200 e role correta
- [ ] `GET /api/me` com token → 200 com dados do usuário
- [ ] `POST /api/register` → 201, role `user`, e login subsequente funciona
- [ ] Register duplicado → 422; register com senha curta → 422
- [ ] `GET /api/admin/ping`: role admin → 200; role user → **403**; sem token → 401
- [ ] `POST /api/logout` invalida o token → chamada seguinte com o mesmo token → 401
- [ ] `POST /api/refresh` retorna token novo válido
- [ ] Payload do JWT contém claim `role`

### B. Smoke test E2E (curl contra servidor real)
- [ ] Servidor sobe do zero no contêiner
- [ ] Matriz de status codes dos endpoints
- [ ] Token expirado/alterado → 401

### C. Frontend
- [ ] `npm run build` sem erros

## Critérios de aceite (DoD do DR-004)

- `php artisan test` 100% verde.
- Smoke E2E confirmando 200/401/403 nos endpoints-chave.
- Build do frontend OK.
- Relatório publicado em `codeReview/005-qa-report.md`.
- Evidências brutas (logs de execução) armazenadas em `evidence/*.log` (DR-004).
