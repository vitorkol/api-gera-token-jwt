# 🔐 api-gera-token-jwt

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)
![JWT](https://img.shields.io/badge/Auth-JWT-black?logo=jsonwebtokens&logoColor=white)
![Tests](https://img.shields.io/badge/tests-21%20passed-brightgreen)
![Smoke](https://img.shields.io/badge/smoke_E2E-19%2F19%20PASS-brightgreen)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-4c1)

</div>

> ### API de autenticação que **gera tokens JWT**, **valida o token do usuário** para logar em um sistema e **expõe a role (rule)** do usuário autenticado — com uma SPA React de demonstração pronta para uso.

---

## 💡 Sobre o projeto

Este projeto nasceu como uma API em **CakePHP** para emitir tokens de autenticação a sistemas legados.
Ele foi **totalmente reestruturado** para uma stack moderna e ativamente mantida:

| | Antes | Agora |
|---|---|---|
| **Backend** | CakePHP | **Laravel 12** (PHP 8.2, runtime Docker) |
| **Autenticação** | JWT customizado | **`php-open-source-saver/jwt-auth`** (guard stateless, blacklist, refresh) |
| **Frontend** | — | **React 18 + Vite 5** (login, sessão e áreas por role) |
| **Testes** | pontuais | **21 testes automatizados + smoke E2E 19/19** com evidências versionadas |

### ✨ Por que Laravel (e não CakePHP)?

- **Ecossistema ativo**: Laravel 12 tem suporte garantido até 2027 — versões com correções de segurança contínuas.
- **Autenticação first-class**: guards, middlewares e o guard `jwt` plugável em minutos.
- **Testing embutido**: `php artisan test` com SQLite `:memory:` — suíte roda em segundos, sem infra.
- **DX superior**: Eloquent, validação de requests, seeders e exception handling declarativo no `bootstrap/app.php`.

## 🧩 Funcionalidades

- 🔑 **Login/registro com JWT** — token assinado (HS256) com a `role` no payload
- 🛡️ **Validação de token** em `/api/me` e `/api/role` — 401 para token ausente, inválido, expirado ou revogado
- 👥 **Roles (rules)**: `admin`, `manager`, `user` — middleware `role:*` devolve 403 quando a rule não basta
- 🔄 **Refresh e logout com blacklist** — token invalidado de imediato no logout
- 🖥️ **SPA React** — login, badge de role, rota admin protegida no cliente **e** no servidor
- 🐳 **Zero fricção de ambiente** — backend roda em contêiner `php:8.2-cli` (sem instalar PHP na máquina)

## ⚡ Comece em 3 passos

```bash
# 1) API Laravel em http://localhost:8000 (Docker)
docker run --rm -d --name jwt-api -u $(id -u):$(id -g) -e HOME=/tmp \
  -p 8000:8000 -v "$(pwd)/backend":/app -w /app \
  php:8.2-cli php artisan serve --host=0.0.0.0 --port=8000

# 2) Frontend React em http://localhost:5173
cd frontend && npm install && npm run dev

# 3) Entre com uma conta demo 🎉
```

| E-mail | Senha | Role |
|---|---|---|
| `admin@example.com` | `password` | `admin` — acesso total à área admin |
| `user@example.com` | `password` | `user` — recebe 403 no endpoint admin |

> Primeira execução? Rode `php artisan migrate --seed` no contêiner (comando completo no `decisionTree/002-arquitetura-api.md` e histórico do repo).

## 🔌 Endpoints

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `POST` | `/api/login` | pública | valida credenciais e **gera o JWT** |
| `POST` | `/api/register` | pública | cria usuário (role fixa `user`) + token |
| `GET` | `/api/me` | `auth:api` | valida o token → usuário + role |
| `GET` | `/api/role` | `auth:api` | valida o token → `{"role": "admin"}` |
| `POST` | `/api/refresh` | `auth:api` | renova o token |
| `POST` | `/api/logout` | `auth:api` | invalida o token (blacklist) |
| `GET` | `/api/admin/ping` | `auth:api` + `role:admin` | demonstra a validação de **rule** (403 p/ demais) |

Contratos JSON completos: [`decisionTree/002-arquitetura-api.md`](decisionTree/002-arquitetura-api.md).

## 🧪 Qualidade

```bash
# Suíte automatizada (21 testes · 50 assertions)
docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp \
  -v "$(pwd)/backend":/app -w /app php:8.2-cli php artisan test

# Smoke test E2E (com a API no ar) — 19 checagens de status
bash scripts/smoke-test.sh
```

Logs brutos das execuções ficam versionados em [`evidence/`](evidence/README.md) — transparência total
sobre o que foi testado e quando (22 testes de regressão + 19 checagens E2E contra servidor real).

## 🗂️ Estrutura

```
├── backend/        # API Laravel 12 (JWT, roles, testes)
├── frontend/       # SPA React 18 (Vite 5)
├── decisionTree/   # 📐 decisões de arquitetura (DR-001..004)
├── codeReview/     # 📝 registros do DEV + relatório do Q.A
├── evidence/       # 🧾 evidências brutas de teste
└── scripts/        # 🧪 smoke test E2E
```

## 🤝 Contribuindo — Pull Requests são bem-vindos!

Este projeto evolui com a comunidade. Encontrou um bug, quer uma feature nova ou melhorou a doc?
**Abra uma Pull Request!**

1. Faça um **fork** do repositório
2. Crie uma branch descritiva: `git checkout -b feat/minha-contribuicao`
3. Commit com mensagens claras: `git commit -m "feat: adiciona X porque Y"`
4. Rode os testes antes de enviar: `php artisan test` + `bash scripts/smoke-test.sh`
5. Abra o **Pull Request** descrevendo o *porquê* da mudança — revisão rápida garantida 💜

> 💡 Fluxo de trabalho do repo: arquitetura em `decisionTree/`, implementação registrada em
> `codeReview/` e evidências de teste em `evidence/` — mantenha essa organização nos seus PRs.

---

<div align="center">
Feito com 💜 por <a href="https://github.com/vitorkol">Vitor Campos</a> e contribuidores — se este projeto te ajudou, deixe uma ⭐!
</div>
