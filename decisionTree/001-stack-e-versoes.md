# DR-001 — Stack e Versões

**Status:** ✅ Aceita · **Autor:** Tech Lead · **Data:** 2026-10-05

## Contexto

- Runtime local possui **PHP 8.1.2** sem as extensões obrigatórias do Laravel
  (`pdo_sqlite`, `curl`, `dom`, `xml`) e sem sudo para instalá-las.
- Docker 29.x disponível; Node 24 + npm 11 disponíveis.

## Decisões

| Tema | Decisão | Justificativa |
|------|---------|---------------|
| Runtime PHP | **Docker `php:8.2-cli`** | PHP local (8.1) está incompleto; imagem oficial traz todas as extensões (dom, xml, pdo_sqlite, curl, openssl, mbstring). |
| Framework backend | **Laravel 12** (`laravel/laravel:^12.0`) | *Revisão 2026-10-05:* Laravel 10 está EOL e o Composer bloqueou todas as versões 10.x por security advisories (`policy.advisories.block`). Laravel 12 é a versão suportada (até 2027) e requer PHP ≥ 8.2 — atendido pelo runtime Docker. |
| JWT | **`php-open-source-saver/jwt-auth` ^2.1** | Fork mantido da lib clássica `tymon/jwt-auth`; API compatível (`JWTSubject`, guard `jwt`), versões ativas para L10. |
| Banco (dev/testes) | **SQLite** (arquivo dev, `:memory:` nos testes) | Zero infraestrutura; troca para MySQL/Postgres é só `DB_CONNECTION` no `.env`. |
| Frontend | **React 18.3 + Vite 5** | Combinação madura e estável; `npm run dev` nativo no Node 24. |
| HTTP client do front | **fetch nativo** (wrapper `src/api.js`) | Evita dependência extra; interceptor simples no wrapper. |
| Rotas do front | **react-router-dom ^6** | Padrão de mercado para SPA com rotas protegidas. |

## Consequências

- Laravel 11/12 não possuem `app/Http/Kernel.php` nem `app/Exceptions/Handler.php`: middleware e exceções são registrados em `bootstrap/app.php`.
- Comandos PHP/artisan/composer **sempre** via contêiner (ver `README.md` → "Como rodar").
- Platform pin no composer: `platform.php = 8.2.20` para o lock ser consistente com o runtime.
