# DR-004 — Workflow Multiagentes e Artefatos

**Status:** ✅ Aceita · **Autor:** Tech Lead · **Data:** 2026-10-05

## Ciclo de trabalho

```
TECH LEAD ──(arquitetura + decisionTree/)──► DELEGA ──► DEV FULLSTACK
                                                            │
                                     implementa backend + frontend
                                     registra tudo em codeReview/
                                                            │
                                     DEV entrega pacote fechado ao QA
                                                            ▼
                                                        Q.A
                                              ─────────────────────
                                              • testes automatizados
                                              • smoke test E2E (curl)
                                              • registra tudo em evidence/

```

## Artefatos obrigatórios

| Artefato                     | Responsável | Local                     |
|------------------------------|-------------|---------------------------|
| Decisões de arquitetura      | Tech Lead   | `decisionTree/*.md`       |
| Log de implementação         | Dev         | `codeReview/001..004*.md` |
| Handoff DEV → QA             | Dev         | `codeReview/004-handoff-qa.md` |
| Relatório de testes do QA    | Q.A         | `codeReview/005-qa-report.md` |
| **Evidências brutas de teste** (logs) | Q.A | `evidence/*.log` |

## Definition of Done (acordo TL–DEV–QA)

- [ ] `php artisan test` verde (feature tests de auth + role).
- [ ] Smoke test E2E real: servidor no ar, login retorna JWT, `/api/me` e `/api/role` respondem com token válido, 401 sem token, 403 sem a rule.
- [ ] Frontend faz `npm run build` sem erros e consome os endpoints.
- [ ] `decisionTree/`, `codeReview/` e `evidence/` preenchidos.
