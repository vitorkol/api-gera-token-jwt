# evidence/ — Evidências brutas de teste (Q.A)

> Conforme DR-004: o Q.A registra **tudo** que executa aqui, em forma de logs brutos.
> Os relatórios interpretados ficam em `codeReview/005-qa-report.md`.

| Arquivo | O que é | Data da execução | Resultado |
|---------|---------|------------------|-----------|
| `001-artisan-test.log` | Saída completa de `php artisan test` (contêiner `php:8.2-cli`) | 2026-10-06 | 21 passed (50 assertions) |
| `002-smoke-test-e2e.log` | Saída de `scripts/smoke-test.sh` contra servidor real (`php artisan serve`) | 2026-10-06 | 19 PASS / 0 FAIL |
| `003-frontend-build.log` | Saída de `npm run build` (Vite 5) | 2026-10-06 | build OK |

## Como reproduzir

```bash
# 1) Suíte automatizada
docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp \
  -v "$(pwd)/backend":/app -w /app php:8.2-cli php artisan test

# 2) Smoke E2E (com a API no ar)
bash scripts/smoke-test.sh

# 3) Build do frontend
cd frontend && npm run build
```
