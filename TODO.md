# TODO.md

Derivado de `specs/spec-incidents.md`. Nenhum item de implementação deve ser iniciado antes da SPEC estar validada pelo desenvolvedor.

## Backend — Feature: Gestão de Incidentes (conforme specs/spec-incidents.md)

- [x] Definir SPEC e Contratos de API de incidentes (`specs/spec-incidents.md`)
- [x] Validar SPEC com o desenvolvedor
- [x] Criar Migration `incidents` conforme Schema da SPEC (seção 2)
- [x] Criar Migration `incident_status_histories` conforme Schema da SPEC (seção 3)
- [x] Criar Migration `incident_affected_systems` (relação 1:N para `affected_systems`)
- [x] Criar Models `Incident`, `IncidentAffectedSystem` e `IncidentStatusHistory` (Eloquent) + Enums `IncidentStatus`/`IncidentSeverity`
- [x] Criar Testes de Contrato para `POST /incidents` (201 + payload)
- [x] Criar Testes de Contrato para `GET /incidents` (200 + filtros + ordenação)
- [x] Criar Testes de Contrato para `GET /incidents/{id}` (200 e 404)
- [x] Criar Testes de Regra de Negócio — Regra 1 (Open → In Progress livre)
- [x] Criar Testes de Regra de Negócio — Regra 2 (trava Critical Open → Resolved)
- [x] Criar Testes de Regra de Negócio — Regra 3 (Closed exige Resolved antes)
- [x] Criar Testes de Regra de Negócio — Regra 4 (Closed é imutável, status e severidade)
- [x] Criar Testes de Regra de Negócio — Regra 5 (comentário obrigatório em Resolved)
- [x] Criar Testes de Regra de Negócio — Regra 6 (comentário obrigatório em Closed)
- [x] Criar Testes de Regra de Negócio — Regra 7 (registro automático de histórico, status e severidade)
- [x] Confirmar suíte em estado RED (24 testes falhando por ausência de rotas/controllers, antes da implementação)
- [x] Criar Request `StoreIncidentRequest` com validações da seção 4 da SPEC
- [x] Criar Request `UpdateIncidentStatusRequest` com validações da seção 7 da SPEC
- [x] Criar Request `UpdateIncidentSeverityRequest` com validações da seção 8 da SPEC
- [x] Criar `IncidentService` com as regras de negócio (`validateStatusTransition`, `canChangeStatus`, `isCriticalIncident`, conforme `PadraoDeCodigo.md`)
- [x] Criar `IncidentController` — endpoint `POST /incidents`
- [x] Criar `IncidentController` — endpoint `GET /incidents` (filtros + busca textual + ordenação)
- [x] Criar `IncidentController` — endpoint `GET /incidents/{id}` (com histórico)
- [x] Criar `IncidentController` — endpoint `PATCH /incidents/{id}/status`
- [x] Criar `IncidentController` — endpoint `PATCH /incidents/{id}/severity`
- [x] Criar `IncidentResource` e `IncidentStatusHistoryResource` (transformação de resposta conforme payloads da SPEC)
- [x] Criar Seeder (`IncidentSeeder`) com 5 incidentes cobrindo as 4 severidades e os 4 status (seção 11 da SPEC)
- [x] Validar respostas de erro e sucesso de todos os endpoints contra a SPEC
- [x] Rodar suíte completa de testes (PHPUnit) e confirmar 100% passando (26/26 GREEN)
- [x] Atualizar `AI_LOG.md` com o ciclo de implementação deste módulo

## Frontend — Feature: Gestão de Incidentes (consumo de specs/spec-incidents.md)

- [x] Tela de listagem de incidentes (RF04): ordenação, filtros por severidade/status, busca textual
- [x] Tela de criação de incidente (RF01)
- [x] Tela de detalhe do incidente com timeline/histórico (RF03)
- [x] Ação de transição de status (respeitando mensagens de erro 422 da SPEC)
- [x] Ação de alteração de severidade
- [x] Tratamento visual dos erros retornados pela API (422/404) conforme payloads da SPEC
- [x] Validado end-to-end no navegador (Playwright): listagem, filtro, detalhe+historico, criação, erro 422 inline, transição de status com atualização de timeline — build de produção (`docker compose`) e `npm run dev` local, zero erros de console
