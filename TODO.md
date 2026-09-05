# TODO.md

Derivado de `specs/spec-incidents.md`. Nenhum item de implementação deve ser iniciado antes da SPEC estar validada pelo desenvolvedor.

## Backend — Feature: Gestão de Incidentes (conforme specs/spec-incidents.md)

- [x] Definir SPEC e Contratos de API de incidentes (`specs/spec-incidents.md`)
- [ ] Validar SPEC com o desenvolvedor
- [ ] Criar Migration `incidents` conforme Schema da SPEC (seção 2)
- [ ] Criar Migration `incident_status_histories` conforme Schema da SPEC (seção 3)
- [ ] Criar Migration `incident_affected_systems` (relação 1:N para `affected_systems`)
- [ ] Criar Models `Incident` e `IncidentStatusHistory` (Eloquent)
- [ ] Criar Testes de Contrato para `POST /incidents` (201 + payload)
- [ ] Criar Testes de Contrato para `GET /incidents` (200 + filtros + ordenação)
- [ ] Criar Testes de Contrato para `GET /incidents/{id}` (200 e 404)
- [ ] Criar Testes de Regra de Negócio — Regra 1 (Open → In Progress livre)
- [ ] Criar Testes de Regra de Negócio — Regra 2 (trava Critical Open → Resolved)
- [ ] Criar Testes de Regra de Negócio — Regra 3 (Closed exige Resolved antes)
- [ ] Criar Testes de Regra de Negócio — Regra 4 (Closed é imutável)
- [ ] Criar Testes de Regra de Negócio — Regra 5 (comentário obrigatório em Resolved)
- [ ] Criar Testes de Regra de Negócio — Regra 6 (comentário obrigatório em Closed)
- [ ] Criar Testes de Regra de Negócio — Regra 7 (registro automático de histórico)
- [ ] Criar Request `CreateIncidentRequest` com validações da seção 4 da SPEC
- [ ] Criar Request `UpdateIncidentStatusRequest` com validações da seção 7 da SPEC
- [ ] Criar Request `UpdateIncidentSeverityRequest` com validações da seção 8 da SPEC
- [ ] Criar `IncidentController` — endpoint `POST /incidents`
- [ ] Criar `IncidentController` — endpoint `GET /incidents` (filtros + busca textual + ordenação)
- [ ] Criar `IncidentController` — endpoint `GET /incidents/{id}` (com histórico)
- [ ] Criar `IncidentController` — endpoint `PATCH /incidents/{id}/status`
- [ ] Criar `IncidentController` — endpoint `PATCH /incidents/{id}/severity`
- [ ] Criar `IncidentResource` (transformação de resposta conforme payloads da SPEC)
- [ ] Criar Seeder com no mínimo 5 incidentes (severidades e status variados, seção 11 da SPEC)
- [ ] Validar respostas de erro e sucesso de todos os endpoints contra a SPEC
- [ ] Rodar suíte completa de testes (PHPUnit) e confirmar 100% passando
- [ ] Atualizar `AI_LOG.md` com o ciclo de implementação deste módulo

## Frontend — Feature: Gestão de Incidentes (consumo de specs/spec-incidents.md)

- [ ] Tela de listagem de incidentes (RF04): ordenação, filtros por severidade/status, busca textual
- [ ] Tela de criação de incidente (RF01)
- [ ] Tela de detalhe do incidente com timeline/histórico (RF03)
- [ ] Ação de transição de status (respeitando mensagens de erro 422 da SPEC)
- [ ] Ação de alteração de severidade
- [ ] Tratamento visual dos erros retornados pela API (422/404) conforme payloads da SPEC
