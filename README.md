# TwoClicks Doc

API REST para gerenciar documentação e tarefas dos projetos TwoClicks.

**Stack:** Laravel 11 · PHP 8.4 · PostgreSQL 17 · Sanctum

---

## Documentação

- [docs/api.md](docs/api.md) — Modelagem, endpoints, autenticação e infraestrutura

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

Ou via script:

```bash
composer setup
```

<!-- task #111: teste end-to-end do pipeline automatizado em 26/05/2026 -->
<!-- task #115: teste pós-migração shell em 27/05/2026 -->

<!-- task #116: validação do fluxo correto em 27/05/2026 -->
<!-- task #118: validação pós-fix webhook shell em 27/05/2026 -->
