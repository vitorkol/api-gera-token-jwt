# CR-001 — Instalação do Backend (Laravel 12 + JWT)

**Responsável:** Dev Fullstack · **Data:** 2026-10-06 · **Delega de:** Tech Lead (DR-001)

## O que foi executado

1. **Runtime**: PHP 8.1 local não possui as extensões do Laravel (`pdo_sqlite`, `curl`, `dom`,
   `xml`) e não há sudo → todos os comandos PHP/composer rodam em contêineres oficiais:
   - `composer:2` para composer (PHP 8.5 p/ resolução)
   - `php:8.2-cli` para runtime/artisan (verificado com `vendor/composer/platform_check.php` → `PLATFORM_OK`)
2. **Create-project**: `laravel/laravel:^12.0` com `--no-install --no-scripts` + `platform.php = 8.2.20`.
   - ⚠️ **Incidente registrado**: tentativa inicial com `laravel/laravel:^10.0` falhou —
     o Composer bloqueou todas as versões do framework 10.x por security advisories
     (Laravel 10 está EOL). Decisão de arquitetura revisada pelo TL (DR-001 rev. 1: L10 → L12).
3. **Dependência JWT**: `composer require php-open-source-saver/jwt-auth:^2.0` — instalada,
   auditoria: "No security vulnerability advisories found".
4. **Setup artisan** (contêiner php:8.2-cli):
   - `php artisan key:generate --force` ✅
   - `vendor:publish` do provider JWT → `config/jwt.php` ✅
   - `php artisan jwt:secret --force` → `JWT_SECRET` no `.env` (não versionado) ✅
   - `php artisan migrate --seed --force` ✅ (users + `add_role_to_users_table` + seeder)

## Estado final

- `backend/` com Laravel 12, lock consistente com plataforma 8.2.20.
- Banco SQLite: `database/database.sqlite` com 2 usuários demo:
  - `admin@example.com` / `password` → role `admin`
  - `user@example.com` / `password` → role `user`

## Como reproduzir

Ver `README.md` (raiz) → seção "Como rodar".
