# TwoClicks Doc — API

**Versão:** 1.0
**Data:** 17/05/2026
**Banco:** `tc_doc` (PostgreSQL)
**Stack:** Laravel 11 + PHP 8.4 + PostgreSQL 17

---

## 1. Visão Geral

API REST para gerenciar **documentação** e **tarefas** dos projetos TwoClicks (SmartClick360, Bethel360, etc.).

Acesso restrito ao Alex via tokens Sanctum (alex, claude, code).

### Hierarquia

```
projects
 ├── documents (N, hierárquicos via parent_id)
 │    └── document_blocks (N, hierárquicos via parent_id, conteúdo texto)
 └── tasks (N)
      └── task_details (N, ciclos de execução)
```

### Domínio

- API: `https://api.twoclicks.com.br`
- Prefixo: `/doc`
- Exemplo: `https://api.twoclicks.com.br/doc/smartclick360/documents`

---

## 2. Autenticação

**Laravel Sanctum** com tokens pessoais, **escopados por projeto**.

### Estratégia

Cada projeto tem 3 tokens próprios: `alex`, `claude`, `code`.

Todos vinculados ao mesmo usuário (`alex@twoclicks.com`), mas com `project_id` diferente.

### Exemplo

| Token (name) | Projeto | Uso |
|--------------|---------|-----|
| `alex`   | SmartClick360 | Postman / testes manuais |
| `claude` | SmartClick360 | Claude chat (via MCP) |
| `code`   | SmartClick360 | Claude Code (terminal) |
| `alex`   | Bethel360 | Postman / testes manuais |
| `claude` | Bethel360 | Claude chat (via MCP) |
| `code`   | Bethel360 | Claude Code (terminal) |

> O escopo do projeto é definido pelo token. O middleware identifica o `project_id` direto do token usado na request.

### Header de autenticação

```
Authorization: Bearer {token}
```

### Endpoints de autenticação

```
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
```

---

## 3. Modelagem do Banco (`tc_doc`)

### 3.1 `people` (pessoas)

- `id`
- `first_name`
- `surname`
- `created_at`
- `updated_at`
- `deleted_at`

### 3.2 `users` (login)

- `id`
- `person_id` — FK people
- `email` — unique
- `password`
- `created_at`
- `updated_at`
- `deleted_at`

### 3.3 `personal_access_tokens` (Sanctum + project_id)

- `id`
- `tokenable_type` — sempre `App\Models\User`
- `tokenable_id` — id do user (Alex)
- `project_id` — **FK projects** (define o escopo do token)
- `name` — alex / claude / code
- `token` — unique(64)
- `abilities` — nullable
- `last_used_at` — nullable
- `expires_at` — nullable
- `created_at`
- `updated_at`

> A única alteração em relação ao Sanctum padrão é a coluna `project_id`. Cada projeto tem seus 3 tokens (alex, claude, code), todos vinculados ao mesmo user Alex.

### 3.4 `audit_logs` (auditoria)

- `id`
- `person_id` — FK people (quem fez)
- `project_id` — FK projects (de qual projeto veio o token)
- `token_name` — alex / claude / code
- `action` — create / update / delete / restore
- `table_name`
- `record_id`
- `old_values` — JSON, nullable
- `new_values` — JSON, nullable
- `ip_address` — nullable
- `created_at`

### 3.5 `projects`

- `id`
- `name`
- `slug` — unique
- `order`
- `status`
- `created_at`
- `updated_at`
- `deleted_at`

### 3.6 `documents`

- `id`
- `project_id` — FK projects
- `parent_id` — FK documents, nullable (hierarquia)
- `title`
- `slug` — unique por projeto (combo `project_id + slug`)
- `order`
- `status`
- `created_at`
- `updated_at`
- `deleted_at`

### 3.7 `document_blocks` (conteúdo dos documentos)

- `id`
- `document_id` — FK documents
- `parent_id` — FK document_blocks, nullable (hierarquia)
- `content` — text
- `order`
- `status`
- `created_at`
- `updated_at`
- `deleted_at`

> Sem campo `type`. Todos os blocos são texto.

### 3.8 `task_statuses`

- `id`
- `name` — Fazer-Claude, Análise-Code, Validado-Claude, Execução-Code, Revisão-Claude, Aprovação-TwoClicks, Concluído-TwoClicks, Refazer-TwoClicks
- `slug`
- `color` — nullable
- `order`
- `status`

### 3.9 `task_fases`

- `id`
- `name` — Fase 1 (BD), Fase 2 (Infra backend), Fase 3 (Auth/permissões), Fase 4 (Pessoas), Fase 5 (Frontend base), Fase 6 (Segurança/admin), Fase 7 (Demais módulos), Fase 8 (Complementos)
- `slug`
- `order`
- `status`

### 3.10 `task_modulos`

- `id`
- `name` — Pessoas, Produtos, Vendas, Compras, Financeiro
- `slug`
- `order`
- `status`

### 3.11 `task_tipos`

- `id`
- `name` — Frontend, Backend, Banco de Dados, Infra/Deploy, Produto
- `slug`
- `order`
- `status`

### 3.12 `task_prioridades`

- `id`
- `name` — Alta, Média, Baixa
- `slug`
- `color` — nullable
- `order`
- `status`

### 3.13 `tasks`

- `id`
- `project_id` — FK projects
- `title`
- `description` — text, nullable
- `task_status_id` — FK task_statuses
- `task_fase_id` — FK task_fases
- `task_modulo_id` — FK task_modulos
- `task_tipo_id` — FK task_tipos
- `task_prioridade_id` — FK task_prioridades
- `order`
- `status`
- `created_at`
- `updated_at`
- `deleted_at`

> 1 tarefa = 1 fase. Demandas que envolvem várias fases viram N tarefas.

### 3.14 `task_details` (ciclos de execução)

- `id`
- `task_id` — FK tasks
- `task_status_id` — FK task_statuses (qual status gerou esse detalhe)
- `person_id` — FK people (autor)
- `prompt` — text
- `resumo` — text
- `started_at` — editável
- `finished_at` — editável
- `duration_minutes` — calculado automaticamente (finished_at − started_at)
- `created_at`

> **Regra de uso:** não editar (mantém histórico). **Tecnicamente:** PUT e DELETE existem pra emergências. Toda edição fica registrada em `audit_logs`.

---

## 4. Endpoints

### 4.1 Autenticação

```
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
```

### 4.2 Projects (CRUD)

```
GET    /api/doc/projects
GET    /api/doc/projects/{project}
POST   /api/doc/projects
PUT    /api/doc/projects/{project}
DELETE /api/doc/projects/{project}
```

### 4.3 Documents (CRUD)

```
GET    /api/doc/{project}/documents
GET    /api/doc/{project}/documents/{document}
POST   /api/doc/{project}/documents
PUT    /api/doc/{project}/documents/{document}
DELETE /api/doc/{project}/documents/{document}
```

### 4.4 Document Blocks (CRUD)

```
GET    /api/doc/{project}/documents/{document}/blocks
GET    /api/doc/{project}/documents/{document}/blocks/{block}
POST   /api/doc/{project}/documents/{document}/blocks
PUT    /api/doc/{project}/documents/{document}/blocks/{block}
DELETE /api/doc/{project}/documents/{document}/blocks/{block}
```

### 4.5 Tasks (CRUD)

```
GET    /api/doc/{project}/tasks
GET    /api/doc/{project}/tasks/{task}
POST   /api/doc/{project}/tasks
PUT    /api/doc/{project}/tasks/{task}
DELETE /api/doc/{project}/tasks/{task}
```

#### POST /api/doc/{project}/tasks/{task}/execute

Dispara sob demanda o `DispatchStatusWebhookJob` do status atual da task, sem precisar fazer uma transição. Usado pelo botão "▶ Executar" no admin. Útil para re-executar o Code após falha transitória (token expirado, rate limit etc.).

**Requisitos:** o status atual deve ter `webhook_url` **e** `code_prompt` preenchidos.

**Defesa em profundidade:**
- Rate limit: máx 1 execução por task a cada 60s (chave compartilhada entre API e web — anti duplo-clique e anti-burla via canal alternativo).
- Auditoria: registra `AuditLog` com `action='execute_status_webhook'` a cada chamada.

**Request:** sem body. Headers: `Authorization: Bearer {token}` + `Accept: application/json`.

**Respostas:**

```json
// 202 Accepted — job enfileirado
{ "message": "Job enfileirado", "task_id": 95, "status_slug": "aprovacao-twoclicks" }

// 422 Unprocessable — status atual sem webhook_url ou code_prompt
{ "message": "Status atual não suporta execução sob demanda (sem webhook_url ou code_prompt)." }

// 429 Too Many Requests — rate limit ativo
{ "message": "Aguarde 42s antes de re-executar." }
```

**Exemplo curl:**
```bash
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  https://docs.twoclicks.com.br/api/doc/tasks/95/execute
```

### 4.6 Task Details (CRUD — uso restrito a POST/GET, PUT/DELETE em emergência)

```
GET    /api/doc/{project}/tasks/{task}/details
GET    /api/doc/{project}/tasks/{task}/details/{detail}
POST   /api/doc/{project}/tasks/{task}/details
PUT    /api/doc/{project}/tasks/{task}/details/{detail}
DELETE /api/doc/{project}/tasks/{task}/details/{detail}
```

### 4.7 Tabelas de apoio (apenas leitura via GET)

```
GET /api/doc/task-statuses
GET /api/doc/task-fases
GET /api/doc/task-modulos
GET /api/doc/task-tipos
GET /api/doc/task-prioridades
```

---

## 5. Query strings (GET de listagem)

```
?status=...
?fase=...
?modulo=...
?tipo=...
?prioridade=...
?order=field,direction       (ex: created_at,desc)
?per_page=100                 (padrão: 100)
?page=1
?search=texto
```

---

## 6. Formato de resposta

### Sucesso (200/201)

```json
{
  "data": { ... },
  "meta": { "page": 1, "per_page": 100, "total": 543 }
}
```

### Erro (4xx/5xx)

```json
{
  "message": "Mensagem do erro",
  "errors": { ... }
}
```

---

## 7. Auditoria

Toda operação **create / update / delete / restore** em qualquer tabela do sistema gera automaticamente uma linha em `audit_logs` via Observer do Laravel.

Captura:
- `person_id` (do usuário do token)
- `project_id` (do projeto vinculado ao token)
- `token_name` (qual token usou: alex/claude/code)
- `action`, `table_name`, `record_id`
- `old_values`, `new_values` (JSON)
- `ip_address`

---

## 8. Pendências / Futuro

- [ ] Tools do MCP Server (definir quais ações o Claude chat pode fazer)
- [ ] Anexos em documentos e tarefas
- [ ] MCP Server publicado em `https://mcp.twoclicks.com.br`
- [ ] Integração com Notion (sincronização bidirecional?)

---

## 9. Infraestrutura

- **VPS:** Hostinger
- **Domínio API:** `api.twoclicks.com.br`
- **Domínio MCP:** `mcp.twoclicks.com.br` (futuro)
- **SSL:** Let's Encrypt (Certbot)
- **Banco:** PostgreSQL 17 — `tc_doc`
- **Servidor web:** Nginx ou Apache (a definir)

---

## 10. Sequência de implementação

1. SSL no VPS
2. Deploy básico Laravel
3. Banco `tc_doc` + migrations
4. Sanctum + coluna `project_id` em `personal_access_tokens`
5. Seed: criar projetos iniciais + 3 tokens (alex, claude, code) por projeto
6. Endpoints CRUD (projects → documents → blocks → tasks → details)
7. Middleware: extrair `project_id` do token usado
8. Auditoria (Observer + audit_logs)
9. MCP Server (depois)
10. Conexão no claude.ai (depois)
