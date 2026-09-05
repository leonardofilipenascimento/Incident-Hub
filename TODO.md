# TODO.md

Derivado de `specs/spec-incidents.md`, que reflete o `CHALLENGE_PACK.md` oficial.

## Backend

- [x] Definir SPEC e contratos de API (`specs/spec-incidents.md`)
- [x] Migration `incidents` (title, description, severity, owner, status)
- [x] Migration `incident_status_histories` (previous_status, new_status, created_at)
- [x] Models `Incident` e `IncidentStatusHistory` + enums `IncidentStatus`/`IncidentSeverity`
- [x] `IncidentService`: `createIncident`, `listIncidents`, `updateIncidentStatus`, contagens do dashboard
- [x] `POST /incidents` (criação, valida title/description/severity/owner)
- [x] `GET /incidents` (filtro por severity/status)
- [x] `GET /incidents/{id}` (com histórico)
- [x] `PATCH /incidents/{id}/status` (regra: Critical não pula Open→Resolved)
- [x] `GET /dashboard` (open/critical não resolvidos/resolved)
- [x] Seeder com os 3 incidentes exigidos (Ana/Bruno/Carla)
- [x] Testes automatizados (23/23 GREEN): criação, listagem, detalhe, transição de status, dashboard
- [x] Auditoria manual de casos-limite (404, enum inválido, 405, etc.)

## Frontend

- [x] Listagem com filtros por severidade/status
- [x] Criação de incidente (title, description, severity, owner)
- [x] Detalhe do incidente com histórico
- [x] Ação de transição de status com feedback de erro (422) inline
- [x] Dashboard (contagens open/critical não resolvidos/resolved) na home
- [x] Validado end-to-end no navegador (build de produção via Docker e `npm run dev`)

## Infraestrutura e documentação

- [x] Docker Compose (mysql + backend + frontend), persistência validada na prática
- [x] README.md com as seções exigidas (Pré-requisitos, Instalação, Execução, Dados iniciais, Testes, Arquitetura, Limitações conhecidas)
- [x] PLAN.md com a estrutura exigida (Entendimento, Escopo, Decisões técnicas, Decomposição, Critérios de aceite, Riscos, Estratégia de IA)
- [x] AI_LOG.md com o histórico de decisões e desvios
- [x] FINAL_REPORT.md respondendo as 15 perguntas do Challenge Pack
- [x] Collection Postman atualizada
