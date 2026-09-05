# Incident Hub

Aplicação web para registro e acompanhamento de incidentes operacionais: cadastro, listagem com filtros, detalhe, transição de status com regra de negócio, histórico de alterações e dashboard resumo.

Contratos de API completos em [`specs/spec-incidents.md`](specs/spec-incidents.md). Histórico de decisões em [`AI_LOG.md`](AI_LOG.md) e [`PLAN.md`](PLAN.md).

## Pré-requisitos

- **Com Docker (recomendado):** Docker e Docker Compose.
- **Sem Docker:** PHP 8.3+, Composer, Node.js 20+, MySQL 8.

## Instalação

### Com Docker

Nenhuma instalação manual de dependências é necessária — o build da imagem já roda `composer install` e `npm install`/`npm run build`.

### Sem Docker

```bash
cd backend && composer install
cd ../frontend && npm install
```

## Execução

### Com Docker

```bash
docker compose up -d --build
```

Sobe 3 containers:
- `mysql` — MySQL 8, dados persistidos em volume nomeado (`mysql_data`), sobrevivem a `docker compose restart`/`down` (sem `-v`).
- `backend` — API Laravel em `http://localhost:8000/api` (roda `php artisan migrate --force` automaticamente ao subir).
- `frontend` — Next.js em `http://localhost:3000`.

### Sem Docker

```bash
# backend
cd backend
cp .env.example .env   # ajuste DB_* para seu MySQL local
php artisan key:generate
php artisan migrate
php artisan serve

# frontend (outro terminal)
cd frontend
npm run dev
```

## Dados iniciais

O seeder cria os 3 incidentes de exemplo exigidos (Payment API instability/Critical/Ana/Open, Reconciliation delay/High/Bruno/In Progress, Incorrect customer notification/Medium/Carla/Resolved).

Popular o banco (executar uma vez, após subir a aplicação):

```bash
# Docker
docker compose exec backend php artisan db:seed --force

# Sem Docker
cd backend && php artisan db:seed
```

Para resetar tudo e repopular do zero:

```bash
# Docker
docker compose exec backend php artisan migrate:fresh --seed --force

# Sem Docker
cd backend && php artisan migrate:fresh --seed
```

## Testes

```bash
# Docker
docker compose exec backend ./vendor/bin/phpunit

# Sem Docker
cd backend && ./vendor/bin/phpunit
```

Testes de contrato e de regra de negócio em `backend/tests/Feature/` — um arquivo por área (criação, listagem, detalhe, transição de status, dashboard), com foco na regra crítica: um incidente `Critical` não pode ir de `Open` direto para `Resolved`.

> Use `./vendor/bin/phpunit` em vez de `php artisan test`: o runner de saída "bonita" do `artisan test` (Collision) emite warnings cosméticos de `file_get_contents` nesta imagem, sem afetar o resultado dos testes.

## Arquitetura

```text
Next.js / React (frontend/)
       ↓ HTTP / REST (specs/spec-incidents.md)
Laravel API (backend/)
       ↓ Eloquent
MySQL
```

- `backend/app/Services/IncidentService.php` concentra a regra de negócio (transição de status) e as contagens do dashboard.
- `backend/app/Http/Controllers/` fica enxuto, delegando para a service.
- `incident_status_histories` é uma tabela imutável (sem update/delete) — cada transição de status gera uma linha.
- Frontend consome a API diretamente do navegador via `fetch` (hooks `useIncidents`/`useIncident`/`useDashboard` em `frontend/src/hooks/`), sem camada de Server Components para manter um único padrão de data-fetching.

## Limitações conhecidas

- Sem autenticação/autorização (deliberado, fora do escopo do desafio).
- Sem edição de título/descrição/severidade após a criação — só a transição de status é suportada, conforme pedido.
- Sem paginação na listagem — adequado ao volume de dados esperado para este desafio.
- Sem testes automatizados de frontend (validação end-to-end foi feita manualmente/via script descartável durante o desenvolvimento; ver `AI_LOG.md`).
