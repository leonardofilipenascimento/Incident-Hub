# Padrões de Nomenclatura — Incident Hub

Documento de referência obrigatório para nomenclatura de código, banco de dados e contratos de API do projeto **Incident Hub**. Deve ser consultado antes de escrever qualquer SPEC (contratos, schemas, validações) e antes de qualquer implementação, conforme `prompt-principal-hackathon-sdd.md`.

---

## 1. Banco de dados (MySQL / Migrations)

- **snake_case** para nomes de tabelas, colunas, índices e foreign keys.
- Tabelas no plural: `incidents`, `incident_status_histories`.
- Colunas descritivas, sem abreviações obscuras: `severity`, `status`, `resolved_at`, `assigned_to`.
- Chaves estrangeiras: `<entidade_singular>_id` (ex.: `incident_id`, `user_id`).
- Timestamps padrão Laravel: `created_at`, `updated_at` (e `deleted_at` se soft delete).

## 2. Payloads de API (JSON — request/response)

- **snake_case** em todas as chaves de JSON, tanto em requests quanto em responses.
- Espelhar os nomes das colunas do banco quando fizer sentido (ex.: `resolved_at`, `assigned_to`), evitando tradução divergente entre camadas.
- Exemplo de payload:
```json
{
  "id": 1,
  "title": "Falha no gateway de pagamento",
  "severity": "critical",
  "status": "open",
  "created_at": "2026-09-05T10:00:00Z"
}
```

## 3. PHP (Laravel) — variáveis, métodos e funções

- **camelCase** para variáveis, parâmetros, métodos e funções.
- Verbos para ações, substantivos para getters/propriedades:
  - `$incidentStatus`, `$filteredIncidents`, `$validationErrors`
  - `createIncident()`, `updateIncidentStatus()`, `resolveIncident()`
- Evite nomes de uma letra (`$i`, `$s`) exceto contadores triviais em laços curtos.
- Evite abreviações obscuras (`$ir` em vez de `$incidentRepository`).
- Propriedades de classe seguem o mesmo padrão camelCase (`protected $currentStatus`).

## 4. TypeScript (Next.js / React) — variáveis, funções e hooks

- **camelCase** para variáveis, funções, métodos e props.
- Hooks customizados prefixados com `use` (ex.: `useIncidentList`).
- Handlers de evento prefixados com `handle` (ex.: `handleStatusChange`).
- Tipos/interfaces que representam entidades usam **PascalCase** (ver seção 5), mas suas propriedades internas seguem o mesmo formato do JSON da API (**snake_case**), já que representam o contrato vindo do backend — evita mapeamento manual desnecessário entre camadas.

## 5. Classes, Models e tipos (PHP e TypeScript)

- **PascalCase** para nomes de classes, Models, Controllers, Requests, Resources, Services e interfaces/types TS.
- Nomes no singular para entidades: `Incident`, `IncidentController`, `CreateIncidentRequest`, `IncidentResource`.
- Sufixos indicam papel/responsabilidade:
  - `*Controller` — controllers
  - `*Request` — form requests / validação de entrada
  - `*Resource` — transformação de resposta (API Resources)
  - `*Service` — regra de negócio isolada (quando justificado pela SPEC; evitar criar sem necessidade real, conforme `PadraoDeCodigo.md`)
  - `*Repository` — acesso a dados (quando aplicável)

## 6. Rotas de API

- **kebab-case** para segmentos de URL compostos por mais de uma palavra (ex.: `/incident-status-history`).
- Recursos no plural: `/incidents`, `/incidents/{id}`.
- Ações que não são CRUD puro como sub-recurso do verbo de negócio: `PATCH /incidents/{id}/status`.

## 7. Regras de validação — nomenclatura e verbos claros

- Nomes de regras de validação (custom rules) e mensagens de erro devem usar **verbos claros** que descrevam a condição verificada, não o mecanismo interno:
  - `MustTransitionThroughInProgress` (regra customizada) em vez de `CheckStatus2`
  - `RequiresResolutionNote` em vez de `ValidateField3`
- Nomes de métodos de validação em Requests seguem verbo + contexto: `validateStatusTransition()`, `ensureSeverityIsKnown()`.
- Mensagens de erro devem ser explícitas e vinculadas à regra de negócio da SPEC (ex.: *"Incidentes críticos devem passar por In Progress antes de serem resolvidos."*), nunca genéricas (`"Invalid input"`).
- Na SPEC, cada regra de validação deve ser nomeada com um identificador legível em PascalCase ou frase curta (ex.: `Regra: Transição de Status de Incidente Crítico`), permitindo rastreabilidade entre SPEC → teste → implementação.

## 8. Resumo rápido

| Contexto | Convenção | Exemplo |
| :--- | :--- | :--- |
| Tabelas/colunas (DB) | snake_case | `incident_status_histories`, `resolved_at` |
| Payload JSON (API) | snake_case | `"created_at"`, `"assigned_to"` |
| Variáveis/métodos PHP | camelCase | `$incidentStatus`, `updateIncidentStatus()` |
| Variáveis/métodos TS | camelCase | `incidentList`, `handleStatusChange()` |
| Classes/Models/Types | PascalCase | `Incident`, `IncidentController`, `CreateIncidentRequest` |
| Rotas de API | kebab-case (plural) | `/incidents`, `/incidents/{id}/status` |
| Regras de validação | Verbo claro + contexto | `MustTransitionThroughInProgress` |
