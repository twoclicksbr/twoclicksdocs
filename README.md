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
