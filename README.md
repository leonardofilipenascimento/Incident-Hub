# Incident Hub

Aplicação web para registro e acompanhamento de incidentes operacionais: cadastro, listagem com filtros, detalhe, transição de status com regra de negócio, histórico de alterações e dashboard resumo.

Contratos de API completos em [`specs/spec-incidents.md`](specs/spec-incidents.md) e em formato OpenAPI/Swagger em [`openapi.yaml`](openapi.yaml). Histórico de decisões em [`AI_LOG.md`](AI_LOG.md) e [`PLAN.md`](PLAN.md).

## Deploy

Demonstração pública (frontend + backend rodando na nuvem, independente de qualquer máquina local):

- **Aplicação:** https://incident-hub-indol.vercel.app/ (Vercel)
- **API:** https://incident-hub-production-ef21.up.railway.app/api (Railway, Laravel + MySQL)

O ambiente de deploy é só para demonstração — não é exigido pelo desafio (Seção 12 do `CHALLENGE_PACK.md` só pede execução local). As instruções abaixo cobrem a execução local/Docker.

## Documentos do repositório

Entregáveis exigidos pelo desafio:

- [`START.md`](START.md) — nome, horário de início e ferramentas de IA.
- [`PLAN.md`](PLAN.md) — planejamento inicial (entendimento, escopo, decisões técnicas, riscos).
- [`AI_LOG.md`](AI_LOG.md) — histórico das interações relevantes com IA durante o desenvolvimento.
- [`FINAL_REPORT.md`](FINAL_REPORT.md) — relatório final (as 15 perguntas do desafio).
- `README.md` — este arquivo.

Documentos adicionais (processo interno de Spec-Driven Development, não exigidos pelo desafio, mantidos para transparência):

- [`CHALLENGE_PACK.md`](CHALLENGE_PACK.md) — cópia do enunciado oficial do desafio.
- [`specs/spec-incidents.md`](specs/spec-incidents.md) — contrato técnico da API derivado do Challenge Pack.
- [`openapi.yaml`](openapi.yaml) + [`docs/swagger.html`](docs/swagger.html) — mesmo contrato em formato OpenAPI, com visualizador Swagger UI (ver seção "Documentação interativa da API").
- [`TODO.md`](TODO.md) — checklist de tarefas derivado da spec.
- `prompt-principal-hackathon-sdd.md`, `PadraoDeCodigo.md`, `padroes-nomenclatura.md`, `conventional-commits.md` — guias internos de metodologia e padrão de código usados para conduzir o desenvolvimento com IA; não fazem parte do enunciado do desafio.

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

## Documentação interativa da API (Swagger)

O contrato completo também está disponível em [`openapi.yaml`](openapi.yaml) (OpenAPI 3.0), com um visualizador Swagger UI em [`docs/swagger.html`](docs/swagger.html).

Como o navegador bloqueia o `fetch` de arquivos locais abertos direto via `file://`, sirva a raiz do projeto com um servidor estático simples:

```bash
python3 -m http.server 8090
# ou: npx serve .
```

Depois abra `http://localhost:8090/docs/swagger.html` — lista os 5 endpoints, schemas e permite testar cada rota direto na tela (contra `http://localhost:8000/api` ou a API de produção, selecionável no dropdown "Servers").

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
