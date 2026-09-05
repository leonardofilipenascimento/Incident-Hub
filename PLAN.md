# PLAN.md

## 1. Entendimento do problema

O **Incident Hub** é uma plataforma web para registro, acompanhamento, categorização e gerenciamento do ciclo de vida de incidentes operacionais de TI, com histórico auditável e regras estritas de transição de estado (ver `CHALLENGE_PACK.md`).

## 2. Mapeamento Challenge Pack → Módulos/SPECs

| Requisito do Challenge Pack | Módulo | SPEC |
| :--- | :--- | :--- |
| RF01 — Gestão e Registro de Incidentes | Incidentes | `specs/spec-incidents.md` |
| RF02 — Máquina de Estados e Transição de Status | Incidentes (regras de estado) | `specs/spec-incidents.md` |
| RF03 — Histórico e Auditoria de Ações | Histórico de Incidentes (timeline) | `specs/spec-incidents.md` (seção de histórico) |
| RF04 — Listagem e Filtros Operacionais | Incidentes (listagem) | `specs/spec-incidents.md` (endpoint de listagem) |
| RT01 — Arquitetura Desacoplada | Infraestrutura/Arquitetura | (não gera SPEC de domínio — decisão técnica, seção 4 deste PLAN) |
| RT02 — Banco de Dados Relacional (migrations + seeders) | Persistência | `specs/spec-incidents.md` (seção Schema) + seeders |
| RT03/RT04 — Validações e Respostas de API | Incidentes (contratos) | `specs/spec-incidents.md` (seções Validações e Erros) |
| Critério de Aceite 3 — Persistência após reinicialização de containers | Infraestrutura/Containerização | (não gera SPEC de domínio — decisão técnica, seção 4.1 deste PLAN) |

Todo o escopo funcional do Challenge Pack (RF01–RF04) está concentrado em um único módulo de domínio, **Incidentes**, incluindo seu histórico — não há necessidade de módulos adicionais (ex.: autenticação) pois o Challenge Pack não os exige. Evita-se assim overengineering (Seção 22 do prompt principal).

## 3. Escopo obrigatório

- Criação de incidentes com validação estrita dos campos (RF01).
- Máquina de estados com as 4 regras de transição descritas em RF02.
- Histórico imutável de mudanças de status/severidade (RF03).
- Listagem com ordenação e filtros por severidade, status e busca textual (RF04).
- Migrations + seeders com no mínimo 5 incidentes pré-carregados (RT02).
- Testes automatizados cobrindo as regras de transição de status, especialmente a trava do `Critical` (Critério de Aceite 1).

## 4. Decisões técnicas e justificativa da stack

### Backend
- **Laravel** — API REST, conforme stack obrigatória (Seção 5 do prompt principal) e RT01.
- **Eloquent** — ORM para persistência dos models `Incident` e `IncidentHistory`.
- **Laravel Migrations/Seeders** — atende RT02 (schema relacional + massa mínima de teste).
- **PHPUnit** — testes de contrato e de regra de negócio (Critério de Aceite 1).

### Frontend
- **Next.js / React / TypeScript** — SPA/SSR consumindo a API REST, conforme RT01.

### Banco
- **MySQL** — relacional, conforme RT02.

### Arquitetura

```text
Next.js / React
      ↓ (HTTP / REST contratado na SPEC)
Laravel
      ↓
Eloquent
      ↓
MySQL
```

### 4.1 Containerização (Docker)

O Challenge Pack menciona explicitamente, no Critério de Aceite 3, que "o sistema deve manter a consistência dos dados após a reinicialização dos containers/servidores" — decisão de containerizar via Docker/Docker Compose para atender esse critério de forma verificável, ainda que não estivesse detalhada como requisito técnico explícito (RT).

- Um `Dockerfile` por aplicação (`backend/Dockerfile`, `frontend/Dockerfile`), orquestrados por um único `docker-compose.yml` na raiz do repositório.
- **backend**: `php:8.3-cli-alpine` + extensão `pdo_mysql`, Composer instalado com dependências completas (inclui `require-dev`, necessário para rodar PHPUnit dentro do container e evitar descompasso entre o manifest de pacotes cacheado e os pacotes instalados). Entrypoint roda `php artisan migrate --force` (idempotente) antes de subir o servidor embutido do Laravel (`artisan serve`).
- **frontend**: build multi-stage com `node:22-alpine`, compilando com `npm run build` e servindo com `next start`.
- **mysql**: imagem oficial `mysql:8.0`, com volume nomeado (`mysql_data`) garantindo persistência entre reinicializações — validado manualmente restartando os containers e confirmando que os dados seedados permanecem.
- Seed **não** roda automaticamente no entrypoint (evita duplicar dados a cada restart); é executado manualmente uma vez via `docker compose exec backend php artisan db:seed`.
- Porta do MySQL mapeada para `3307` no host (em vez de `3306`) para não colidir com uma instância local de MySQL já em uso na máquina de desenvolvimento; a comunicação interna entre os containers `backend`↔`mysql` continua na porta `3306` padrão, via rede Docker.

### Estrutura de pastas

```text
incident-hub/
│
├── backend/
│   └── Laravel
│
├── frontend/
│   └── Next.js
│
├── specs/
│   └── spec-incidents.md
│
├── START.md
├── PLAN.md
├── TODO.md
├── AI_LOG.md
├── README.md
└── FINAL_REPORT.md
```

## 5. Estratégia de persistência

- Tabela `incidents`: dados centrais do incidente (título, descrição, severidade, status, sistemas afetados, timestamps).
- Tabela `incident_status_histories`: uma linha por alteração de status ou severidade, imutável (sem update/delete), referenciando `incident_id`.
- Sistemas afetados (`affected_systems`) persistidos como relação 1:N (`incident_affected_systems`) para permitir busca/consulta estruturada, evitando serialização opaca em coluna única.
- Seeder cria no mínimo 5 incidentes cobrindo as 4 severidades e pelo menos os status `Open`, `In Progress`, `Resolved`, `Closed`, para exercitar filtros e regras de transição manualmente.

## 6. Estratégia de testes de contrato e integração

- **Testes de contrato**: para cada endpoint de `specs/spec-incidents.md`, validar status HTTP, estrutura e tipos do payload de resposta (sucesso e erro).
- **Testes de regra de negócio**: um teste por cenário Given-When-Then da SPEC, com foco obrigatório na trava de transição `Critical: Open → Resolved` (Critério de Aceite 1).
- Testes escritos **antes** da implementação (Seção 4 — TEST FIRST), a partir da SPEC validada.

## 7. Riscos e uso de IA

- **Risco:** ambiguidade sobre quem pode alterar severidade e se isso é feito por endpoint dedicado ou embutido na criação/atualização — mitigado tratando como endpoint explícito e mínimo em `specs/spec-incidents.md`, sujeito a validação do desenvolvedor antes da implementação.
- **Risco:** divergência entre nomenclatura de campos no banco/API — mitigado seguindo `padroes-nomenclatura.md` (snake_case uniforme em DB e JSON).
- **Uso de IA:** Claude Code conduz o ciclo SDD (SPEC → testes → implementação); todas as decisões e desvios são registrados em `AI_LOG.md`.

## 8. Decomposição de tarefas

Ver `TODO.md`, derivado de `specs/spec-incidents.md`.
