# CR-003 — Frontend React (Vite 5 + React 18)

**Responsável:** Dev Fullstack · **Data:** 2026-10-06 · **Delega de:** Tech Lead (DR-001, DR-002)

## Instalação

- Scaffold manual determinístico (sem prompts interativos): `package.json` com
  `react@^18.3`, `react-dom@^18.3`, `react-router-dom@^6`, `vite@^5`, `@vitejs/plugin-react@^4`.
- `npm install` executado ✅ (Node 24).

## Arquitetura do frontend

| Arquivo | Papel |
|---------|-------|
| `src/api.js` | Wrapper `fetch` com `Authorization: Bearer`; token no `localStorage` (`jwt_token`); auto-clear em 401 |
| `src/context/AuthContext.jsx` | `AuthProvider`: bootstrap valida token salvo via `GET /api/me`; `login/register/logout` |
| `src/components/ProtectedRoute.jsx` | Rota protegida; prop `roles` valida rule no cliente (a definitiva é 403 do backend) |
| `src/pages/Login.jsx` | Form de login; contas demo pré-preenchidas |
| `src/pages/Register.jsx` | Form de cadastro; exibe erros de validação 422 do Laravel |
| `src/pages/Home.jsx` | Perfil + role (via `/api/me` e `/api/role`); botão que testa `/api/admin/ping` |
| `src/pages/Admin.jsx` | Área admin (rota `roles=['admin']`) |
| `src/App.jsx` | Topbar com badge de role + rotas |
| `src/index.css` | Estilo dark minimalista |

## Fluxo de autenticação

1. `POST /api/login` → `access_token` salvo no `localStorage`.
2. `GET /api/me` monta a sessão (usuário + role).
3. Toda request carrega `Authorization: Bearer <token>`.
4. 401 → token limpo e redirect para `/login`.
5. Role exibida no topbar (badge) e usada para restringir a rota `/admin`.

## Configuração

- `VITE_API_URL` (opcional) sobrepõe a URL da API; default `http://localhost:8000/api`.
- Dev server: `npm run dev` → `http://localhost:5173`.

## Pendências para o Q.A

- `npm run build` deve concluir sem erros (QA valida no pacote).
- Teste manual da UI: login admin → Home → endpoint admin 200; login user → 403 na Home.
