# Incident Hub

Plataforma web para registro, acompanhamento, categorização e gerenciamento do ciclo de vida de incidentes operacionais de TI, com histórico auditável e regras estritas de transição de estado.

Projeto desenvolvido seguindo **Spec-Driven Development (SDD)** — ver `prompt-principal-hackathon-sdd.md`, `PLAN.md` e `AI_LOG.md` para o histórico completo de decisões.

## Especificações

Os contratos de API (endpoints, schemas, regras de negócio Given-When-Then) estão em [`specs/spec-incidents.md`](specs/spec-incidents.md). Nenhuma implementação diverge desse contrato sem atualização prévia da SPEC.

## Stack

- **Backend:** Laravel 12 (PHP 8.3), API REST, Eloquent, MySQL, PHPUnit.
- **Frontend:** Next.js, React, TypeScript.
- **Banco:** MySQL 8.

## Executando com Docker (recomendado)

Pré-requisitos: Docker e Docker Compose.

```bash
docker compose up -d --build
```

Isso sobe 3 containers:

- `mysql` — MySQL 8, dados persistidos em volume nomeado (`mysql_data`).
- `backend` — API Laravel em `http://localhost:8000` (roda `php artisan migrate --force` automaticamente ao subir).
- `frontend` — Next.js em `http://localhost:3000`.

Popule o banco com os incidentes de exemplo (executar uma vez, após o primeiro `up`):

```bash
docker compose exec backend php artisan db:seed --force
```

Rodar a suíte de testes automatizados dentro do container:

```bash
docker compose exec backend php artisan test
```

Parar os containers (mantendo os dados no volume):

```bash
docker compose down
```

## Executando localmente (sem Docker)

### Backend

```bash
cd backend
composer install
cp .env.example .env   # ajuste DB_* para seu MySQL local
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

API disponível em `http://localhost:8000/api`.

### Frontend

```bash
cd frontend
npm install
npm run dev
```

Aplicação disponível em `http://localhost:3000`.

## Testes

```bash
cd backend
php artisan test
```

Testes de contrato e de regra de negócio ficam em `backend/tests/Feature/Incidents/`, um arquivo por área do contrato (criação, listagem, detalhe, transição de status, alteração de severidade).
