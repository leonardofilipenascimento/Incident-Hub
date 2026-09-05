# SPEC — Incidentes

Contrato oficial do módulo de Incidentes do **Incident Hub**, derivado de `CHALLENGE_PACK.md` (RF01–RF04, RT01–RT04). Nenhuma implementação ou teste deve divergir deste contrato sem atualização prévia desta SPEC.

Convenções de nomenclatura: ver `padroes-nomenclatura.md` (snake_case em DB/JSON).

---

## 1. Contratos de API

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| POST | `/incidents` | Cria um novo incidente (RF01) |
| GET | `/incidents` | Lista incidentes com filtros e ordenação (RF04) |
| GET | `/incidents/{id}` | Detalha um incidente, incluindo seu histórico (RF03) |
| PATCH | `/incidents/{id}/status` | Transiciona o status do incidente (RF02) |
| PATCH | `/incidents/{id}/severity` | Altera a severidade do incidente (RF03) |

---

## 2. Schema — `Incident`

| Campo | Tipo | Regras |
| :--- | :--- | :--- |
| `id` | integer | gerado pelo banco |
| `title` | string | obrigatório, min 5, max 150 |
| `description` | text | obrigatório, min 10 |
| `severity` | enum | `Low` \| `Medium` \| `High` \| `Critical`, obrigatório |
| `status` | enum | `Open` \| `In Progress` \| `Resolved` \| `Closed`; sempre nasce como `Open` |
| `affected_systems` | array\<string\> | obrigatório, mínimo 1 item |
| `created_at` | datetime (ISO 8601) | gerado pelo banco |
| `updated_at` | datetime (ISO 8601) | gerado pelo banco |

## 3. Schema — `IncidentStatusHistory` (timeline, imutável)

| Campo | Tipo | Regras |
| :--- | :--- | :--- |
| `id` | integer | gerado pelo banco |
| `incident_id` | integer | FK para `incidents.id` |
| `previous_status` | enum \| null | status antes da alteração (`null` se a alteração foi só de severidade) |
| `new_status` | enum \| null | status depois da alteração (`null` se a alteração foi só de severidade) |
| `previous_severity` | enum \| null | severidade antes da alteração (`null` se a alteração foi só de status) |
| `new_severity` | enum \| null | severidade depois da alteração (`null` se a alteração foi só de status) |
| `comment` | string \| null | obrigatório apenas quando `new_status` for `Resolved` ou `Closed` |
| `created_at` | datetime (ISO 8601) | timestamp da alteração |

Registros de histórico nunca são atualizados ou apagados (`UPDATE`/`DELETE` não fazem parte do contrato deste recurso).

---

## 4. Endpoint: `POST /incidents`

**Request:**
```json
{
  "title": "Falha no gateway de pagamento",
  "description": "Gateway retornando 500 em 30% das transacoes",
  "severity": "Critical",
  "affected_systems": ["payment-gateway", "checkout-api"]
}
```

**Regras de validação:**
- `title`: obrigatório, string, min 5, max 150.
- `description`: obrigatório, string, min 10.
- `severity`: obrigatório, um de `Low`, `Medium`, `High`, `Critical`.
- `affected_systems`: obrigatório, array, mínimo 1 item, cada item string não vazia.
- `status`: não é aceito no request — sempre definido como `Open` pelo servidor.

**Sucesso — `201 Created`:**
```json
{
  "id": 1,
  "title": "Falha no gateway de pagamento",
  "description": "Gateway retornando 500 em 30% das transacoes",
  "severity": "Critical",
  "status": "Open",
  "affected_systems": ["payment-gateway", "checkout-api"],
  "created_at": "2026-09-05T10:00:00Z",
  "updated_at": "2026-09-05T10:00:00Z"
}
```

**Erro — `422 Unprocessable Entity`** (exemplo campo `title` ausente):
```json
{
  "message": "Dados invalidos.",
  "errors": {
    "title": ["O campo title e obrigatorio."]
  }
}
```

---

## 5. Endpoint: `GET /incidents`

**Query params (todos opcionais, combinaveis):**
- `severity`: filtra por `Low` \| `Medium` \| `High` \| `Critical`.
- `status`: filtra por `Open` \| `In Progress` \| `Resolved` \| `Closed`.
- `search`: busca textual em `title` e `description` (contains, case-insensitive).

**Ordenação:** sempre por `created_at` decrescente (mais recentes primeiro).

**Sucesso — `200 OK`:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Falha no gateway de pagamento",
      "severity": "Critical",
      "status": "Open",
      "affected_systems": ["payment-gateway", "checkout-api"],
      "created_at": "2026-09-05T10:00:00Z",
      "updated_at": "2026-09-05T10:00:00Z"
    }
  ]
}
```

---

## 6. Endpoint: `GET /incidents/{id}`

**Sucesso — `200 OK`:** objeto `Incident` completo + array `history` (lista de `IncidentStatusHistory`, ordenada por `created_at` crescente).

**Erro — `404 Not Found`** (id inexistente):
```json
{
  "message": "Incidente nao encontrado."
}
```

---

## 7. Endpoint: `PATCH /incidents/{id}/status`

**Request:**
```json
{
  "status": "In Progress",
  "comment": "Time de plataforma investigando causa raiz"
}
```

- `status`: obrigatório, um de `Open`, `In Progress`, `Resolved`, `Closed`.
- `comment`: obrigatório somente quando `status` destino for `Resolved` ou `Closed`; opcional nas demais transições.

**Sucesso — `200 OK`:** objeto `Incident` atualizado (com novo `status` e `updated_at`).

**Efeito colateral:** cria um registro em `IncidentStatusHistory` com `previous_status`/`new_status` preenchidos e `previous_severity`/`new_severity` nulos.

**Erros — `422 Unprocessable Entity`:** ver regras de negócio na seção 9.

---

## 8. Endpoint: `PATCH /incidents/{id}/severity`

**Request:**
```json
{
  "severity": "High",
  "comment": "Reavaliado apos mitigacao parcial"
}
```

- `severity`: obrigatório, um de `Low`, `Medium`, `High`, `Critical`.
- `comment`: opcional (RF03 exige comentário obrigatório apenas em mudanças de status para `Resolved`/`Closed`, não em mudança de severidade).

**Sucesso — `200 OK`:** objeto `Incident` atualizado.

**Efeito colateral:** cria um registro em `IncidentStatusHistory` com `previous_severity`/`new_severity` preenchidos e `previous_status`/`new_status` nulos.

**Erro — `422 Unprocessable Entity`:** se o incidente estiver `Closed` (ver Regra 4, seção 9).

---

## 9. Regras de negócio (Given-When-Then)

### Regra 1 — Transição livre de Open para In Progress
- Given: um incidente com `status="Open"` (qualquer severidade)
- When: `PATCH /incidents/{id}/status` com `status="In Progress"`
- Then: `200 OK`, incidente atualizado, histórico registrado.

### Regra 2 — Trava de incidente Critical (Open → Resolved direto)
- Given: um incidente com `severity="Critical"` e `status="Open"`
- When: `PATCH /incidents/{id}/status` com `status="Resolved"`
- Then: `422 Unprocessable Entity` com mensagem "Incidentes criticos devem passar por In Progress antes de serem resolvidos."

### Regra 3 — Closed exige passagem por Resolved
- Given: um incidente com `status` diferente de `Resolved`
- When: `PATCH /incidents/{id}/status` com `status="Closed"`
- Then: `422 Unprocessable Entity` com mensagem "Incidentes so podem ser fechados a partir do status Resolved."

### Regra 4 — Incidente Closed é imutável
- Given: um incidente com `status="Closed"`
- When: `PATCH /incidents/{id}/status` OU `PATCH /incidents/{id}/severity` com qualquer valor
- Then: `422 Unprocessable Entity` com mensagem "Incidentes fechados nao podem sofrer alteracoes."

### Regra 5 — Comentário obrigatório ao resolver
- Given: um incidente com `status="In Progress"`
- When: `PATCH /incidents/{id}/status` com `status="Resolved"` e `comment` ausente ou vazio
- Then: `422 Unprocessable Entity` com mensagem "Comentario e obrigatorio ao transicionar para Resolved."

### Regra 6 — Comentário obrigatório ao fechar
- Given: um incidente com `status="Resolved"`
- When: `PATCH /incidents/{id}/status` com `status="Closed"` e `comment` ausente ou vazio
- Then: `422 Unprocessable Entity` com mensagem "Comentario e obrigatorio ao transicionar para Closed."

### Regra 7 — Registro automático de histórico
- Given: um incidente em qualquer status válido para alteração
- When: status ou severidade é alterado com sucesso
- Then: um novo registro imutável é criado em `IncidentStatusHistory` com timestamp, valores anterior/novo e comentário (quando fornecido).

---

## 10. Erros — padrão geral

| Cenário | Status HTTP | Payload |
| :--- | :--- | :--- |
| Falha de validação de campo (criação/atualização) | `422` | `{ "message": string, "errors": { campo: [mensagens] } }` |
| Violação de regra de transição de estado | `422` | `{ "message": string, "errors": { "status": [mensagem] } }` |
| Incidente não encontrado | `404` | `{ "message": "Incidente nao encontrado." }` |

---

## 11. Seed mínimo (RT02)

Seeder deve criar no mínimo 5 incidentes cobrindo as 4 severidades e ao menos os status `Open`, `In Progress`, `Resolved`, `Closed`, com histórico coerente com as transições permitidas (nenhum seed pode violar as regras da seção 9).
