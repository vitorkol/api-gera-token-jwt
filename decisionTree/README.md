# decisionTree — Registro de Decisões de Arquitetura

> Mantido pelo **Tech Lead**. Toda decisão estrutural relevante do projeto é registrada
> aqui antes de ser delegada ao DEV, em formato de Decision Record (DR).

## Índice

| DR  | Título                                                     | Status    |
|-----|------------------------------------------------------------|-----------|
| 001 | Stack e versões (Laravel 12 + Docker, React 18 + Vite 5) — **rev. 1: L10→L12** | ✅ Aceita |
| 002 | Arquitetura da API JWT (endpoints, fluxo, contratos)       | ✅ Aceita |
| 003 | Segurança, roles (rules) e tratamento de exceções          | ✅ Aceita |
| 004 | Workflow multiagentes (TL → DEV → QA) e artefatos          | ✅ Aceita |

## Papéis

- **Tech Lead** — define arquitetura, registra em `decisionTree/`, delega implementação.
- **Dev Fullstack** — implementa backend e frontend, registra tudo em `codeReview/`.
- **Q.A** — valida a entrega do DEV com testes automatizados e smoke tests, publica relatório, registra tudo em `evidence/`.

## Restrições do ambiente que moldaram as decisões

- PHP local 8.1.2 **sem** `pdo_sqlite`, `curl`, `dom`, `xml` e sem sudo → backend roda em Docker (`php:8.2-cli`).
- Docker 29.x, Node 24 e npm 11 disponíveis → frontend roda nativo.
- Objetivo do produto: API que **gera token JWT**, **valida o token** para login e **expõe a role (rule)** do usuário autenticado.
