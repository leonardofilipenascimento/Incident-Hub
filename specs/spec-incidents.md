# SPEC — Incidentes

Contrato oficial do módulo de Incidentes do **Incident Hub**, derivado de `CHALLENGE_PACK.md` (Seções 3-9, 11). Nenhuma implementação ou teste deve divergir deste contrato sem atualização prévia desta SPEC.

Convenções de nomenclatura: ver `padroes-nomenclatura.md` (snake_case em DB/JSON).

---

## 1. Contratos de API

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| POST | `/incidents` | Cria um novo incidente (Seção 4) |
| GET | `/incidents` | Lista incidentes com filtros (Seção 5) |
| GET | `/incidents/{id}` | Detalha um incidente, incluindo seu histórico (Seção 6, 8) |
| PATCH | `/incidents/{id}/status` | Transiciona o status do incidente (Seção 7) |
| GET | `/dashboard` | Visão resumida (Seção 9) |

---

## 2. Schema — `Incident`

| Campo | Tipo | Regras |
| :--- | :--- | :--- |
| `id` | integer | gerado pelo banco |
| `title` | string | obrigatório, min 5, max 150 |
| `description` | text | obrigatório, min 10 |
| `severity` | enum | `Low` \| `Medium` \| `High` \| `Critical`, obrigatório |
| `owner` | string | obrigatório (responsável pelo incidente) |
| `status` | enum | `Open` \| `In Progress` \| `Resolved`; sempre nasce como `Open` |
| `created_at` | datetime (ISO 8601) | gerado pelo banco |
| `updated_at` | datetime (ISO 8601) | gerado pelo banco |

## 3. Schema — `IncidentStatusHistory` (timeline, imutável)

| Campo | Tipo | Regras |
| :--- | :--- | :--- |
| `id` | integer | gerado pelo banco |
| `incident_id` | integer | FK para `incidents.id` |
| `previous_status` | enum | status antes da alteração |
| `new_status` | enum | status depois da alteração |
| `created_at` | datetime (ISO 8601) | timestamp da alteração |

Registros de histórico nunca são atualizados ou apagados.

---

## 4. Endpoint: `POST /incidents`

**Request:**
```json
{
  "title": "Payment API instability",
  "description": "Payment API returning intermittent errors during checkout.",
  "severity": "Critical",
  "owner": "Ana"
}
```

**Regras de validação:**
- `title`: obrigatório, string, min 5, max 150.
- `description`: obrigatório, string, min 10.
- `severity`: obrigatório, um de `Low`, `Medium`, `High`, `Critical`.
- `owner`: obrigatório, string.
- `status`: não é aceito no request — sempre definido como `Open` pelo servidor.

**Sucesso — `201 Created`:**
```json
{
  "id": 1,
  "title": "Payment API instability",
  "description": "Payment API returning intermittent errors during checkout.",
  "severity": "Critical",
  "owner": "Ana",
  "status": "Open",
  "created_at": "2026-09-05T10:00:00Z",
  "updated_at": "2026-09-05T10:00:00Z"
}
```

**Erro — `422 Unprocessable Entity`:**
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

**Query params (opcionais, combináveis):**
- `severity`: filtra por `Low` \| `Medium` \| `High` \| `Critical`.
- `status`: filtra por `Open` \| `In Progress` \| `Resolved`.

**Ordenação:** por `created_at` decrescente (mais recentes primeiro).

**Sucesso — `200 OK`:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Payment API instability",
      "severity": "Critical",
      "owner": "Ana",
      "status": "Open",
      "created_at": "2026-09-05T10:00:00Z",
      "updated_at": "2026-09-05T10:00:00Z"
    }
  ]
}
```

---

## 6. Endpoint: `GET /incidents/{id}`

**Sucesso — `200 OK`:** objeto `Incident` completo + array `history` (lista de `IncidentStatusHistory`, ordenada por `created_at` crescente).

**Erro — `404 Not Found`:**
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
  "status": "In Progress"
}
```

- `status`: obrigatório, um de `Open`, `In Progress`, `Resolved`.

**Sucesso — `200 OK`:** objeto `Incident` atualizado (com novo `status`, `updated_at` e `history`).

**Efeito colateral:** cria um registro em `IncidentStatusHistory`.

**Erro — `422 Unprocessable Entity`:** ver regra de negócio na seção 9.

---

## 8. Endpoint: `GET /dashboard`

**Sucesso — `200 OK`:**
```json
{
  "open_incidents": 3,
  "unresolved_critical_incidents": 1,
  "resolved_incidents": 2
}
```

- `open_incidents`: quantidade de incidentes com `status=Open`.
- `unresolved_critical_incidents`: quantidade de incidentes com `severity=Critical` e `status != Resolved`.
- `resolved_incidents`: quantidade de incidentes com `status=Resolved`.

---

## 9. Regra de negócio (Given-When-Then)

### Regra 1 — Trava de incidente Critical
- Given: um incidente com `severity="Critical"` e `status="Open"`
- When: `PATCH /incidents/{id}/status` com `status="Resolved"`
- Then: `422 Unprocessable Entity` com mensagem "Um incidente Critical nao pode passar diretamente de Open para Resolved. E necessario passar por In Progress."

### Regra 2 — Demais transições livres
- Given: um incidente em qualquer status
- When: `PATCH /incidents/{id}/status` com um `status` válido que não viole a Regra 1
- Then: `200 OK`, incidente atualizado, histórico registrado.

### Regra 3 — Registro automático de histórico
- Given: um incidente com status alterado com sucesso
- When: a transição é aplicada
- Then: um novo registro imutável é criado em `IncidentStatusHistory` com `previous_status`, `new_status` e `created_at`.

---

## 10. Erros — padrão geral

| Cenário | Status HTTP | Payload |
| :--- | :--- | :--- |
| Falha de validação de campo | `422` | `{ "message": string, "errors": { campo: [mensagens] } }` |
| Violação da regra de transição de status | `422` | `{ "message": string, "errors": { "status": [mensagem] } }` |
| Incidente não encontrado | `404` | `{ "message": "Incidente nao encontrado." }` |

---

## 11. Seed mínimo (Seção 11 do Challenge Pack)

O seeder deve criar, no mínimo, os 3 incidentes de exemplo exigidos:

| Title | Severity | Owner | Status |
| :--- | :--- | :--- | :--- |
| Payment API instability | Critical | Ana | Open |
| Reconciliation delay | High | Bruno | In Progress |
| Incorrect customer notification | Medium | Carla | Resolved |
