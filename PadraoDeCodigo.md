# Padrões de Código — IncidentHub

Documento de referência para os padrões de código utilizados no projeto **IncidentHub**.

O objetivo é manter o código claro, consistente, legível e fácil de manter.

Estas convenções devem ser seguidas durante a implementação do backend Laravel/PHP e, quando aplicável, adaptadas para o frontend Next.js/React/TypeScript.

---

# 1. Princípios gerais

Priorize:

1. Clareza.
2. Legibilidade.
3. Consistência.
4. Manutenibilidade.
5. Simplicidade.

Evite complexidade desnecessária.

Não crie abstrações, classes, métodos ou estruturas apenas para seguir um padrão de arquitetura quando isso não trouxer benefício real para o projeto.

Ao modificar código existente, observe primeiro o padrão utilizado no módulo e mantenha a consistência.

---

# 2. Variáveis e parâmetros — PHP

## Nomes claros

Evite nomes de uma letra:

```php
$l
$q
$n
$x
```

Exceto em situações realmente triviais, como um contador simples em um loop pequeno.

Prefira:

```php
$incident
$incidents
$status
$severity
$owner
$incidentStatus
$filteredIncidents
```

em vez de:

```php
$i
$list
$s
$o
$f
```

## Evite abreviações obscuras

Prefira:

```php
$incidentStatus
$incidentRepository
$validationErrors
```

em vez de:

```php
$is
$ir
$ve
```

## Evite nomes excessivamente longos

Prefira nomes que expressem claramente a responsabilidade sem transformar a variável em uma frase.

Bom:

```php
$queryIncidents
$criticalIncidents
$validationErrors
$incidentStatus
```

Evitar:

```php
$queryContainingAllIncidentsThatMatchTheSelectedStatus
```

---

# 3. Classes

Utilize **PascalCase** para nomes de classes.

Exemplos:

```php
Incident
IncidentController
IncidentService
IncidentRepository
IncidentRequest
IncidentResource
IncidentStatus
IncidentSeverity
```

Classes devem possuir responsabilidades claras.

Evite classes genéricas como:

```php
Helper
Utils
Manager
Common
GeneralService
```

quando um nome específico for possível.

---

# 4. Métodos e funções

Utilize **camelCase**.

Prefira nomes que expressem uma ação ou intenção.

Exemplos:

```php
createIncident()
updateIncident()
updateIncidentStatus()
listIncidents()
findIncident()
deleteIncident()
validateStatusTransition()
canChangeStatus()
isCriticalIncident()
```

Evite nomes genéricos:

```php
process()
handle()
execute()
doSomething()
run()
```

quando for possível descrever exatamente o comportamento.

---

# 5. Convenções para IncidentHub

Sempre que possível, mantenha uma nomenclatura consistente para as operações relacionadas a incidentes.

## Criação

```php
createIncident()
```

## Busca de um incidente

```php
findIncident()
```

ou, quando a aplicação utilizar o padrão de busca pelo identificador:

```php
findIncidentById()
```

## Listagem

```php
listIncidents()
```

## Atualização

```php
updateIncident()
```

## Atualização de status

```php
updateIncidentStatus()
```

## Validação de transição

```php
validateStatusTransition()
```

## Verificação de transição permitida

```php
canChangeStatus()
```

## Verificação de incidente Critical

```php
isCriticalIncident()
```

Esses nomes são referências de nomenclatura. Antes de criar um novo método, verifique se já existe uma responsabilidade equivalente no projeto para evitar duplicação.

---

# 6. Status do incidente

Os status oficiais do domínio são:

```text
Open
In Progress
Resolved
Closed
```

No código, mantenha uma representação consistente.

Quando utilizar enum em PHP, prefira (PascalCase nos nomes dos cases, conforme `padroes-nomenclatura.md`):

```php
IncidentStatus::Open
IncidentStatus::InProgress
IncidentStatus::Resolved
IncidentStatus::Closed
```

Evite espalhar strings literais pelo código:

```php
if ($status === 'Open') {
    ...
}
```

quando existir uma enum ou constante apropriada para representar o valor.

---

# 7. Severidade

As severidades oficiais são:

```text
Low
Medium
High
Critical
```

Quando utilizar enum, prefira uma representação como:

```php
IncidentSeverity::LOW
IncidentSeverity::MEDIUM
IncidentSeverity::HIGH
IncidentSeverity::CRITICAL
```

Evite duplicar strings de severidade em diversos pontos da aplicação.

---

# 8. Regras de negócio

Regras de negócio devem possuir nomes que expressem claramente sua intenção.

Por exemplo:

```php
canChangeStatus()
validateStatusTransition()
isCriticalIncident()
```

Uma regra como:

```text
Critical não pode passar diretamente de Open para Resolved
```

não deve ficar escondida dentro de uma condição extensa e difícil de entender.

Evite:

```php
if ($incident->severity === 'Critical' && $incident->status === 'Open' && $newStatus === 'Resolved' && ...) {
    ...
}
```

quando for possível encapsular a intenção em um método ou objeto de domínio claro.

Exemplo:

```php
if (! $this->canChangeStatus($incident, $newStatus)) {
    ...
}
```

O código deve deixar evidente **qual regra está sendo aplicada**, não apenas como ela é implementada.

---

# 9. Controllers

Controllers devem permanecer enxutos.

O controller deve ser responsável principalmente por:

* receber a requisição;
* validar ou delegar validação;
* chamar a camada responsável pela operação;
* retornar a resposta HTTP.

Evite colocar regras de negócio complexas diretamente no controller.

Evite:

```php
class IncidentController
{
    public function updateStatus(...)
    {
        // dezenas de linhas de regras de negócio
        // validações
        // consultas
        // alterações
        // regras de transição
    }
}
```

Prefira delegar responsabilidades para classes apropriadas.

Exemplo:

```php
public function updateStatus(UpdateIncidentStatusRequest $request, Incident $incident)
{
    $this->incidentService->updateIncidentStatus(
        $incident,
        $request->status
    );

    ...
}
```

A estrutura exata pode variar conforme a arquitetura definida no `PLAN.md`.

---

# 10. Requests e validação

Quando uma operação possuir regras de entrada relevantes, utilize mecanismos de validação apropriados do Laravel.

Exemplos:

```text
StoreIncidentRequest
UpdateIncidentRequest
UpdateIncidentStatusRequest
```

Os nomes devem representar claramente a finalidade da requisição.

Evite colocar todas as regras de validação diretamente dentro do controller quando o Laravel oferecer uma estrutura mais adequada.

---

# 11. Responses e Resources

Quando utilizar API Resources, siga uma nomenclatura consistente.

Exemplo:

```php
IncidentResource
IncidentCollection
```

A resposta da API deve possuir estrutura previsível.

Evite que endpoints semelhantes retornem formatos completamente diferentes sem motivo.

---

# 12. Repositórios e serviços

Se a arquitetura definida no `PLAN.md` utilizar repositories ou services, os nomes devem refletir a responsabilidade.

Exemplos:

```php
IncidentRepository
IncidentService
```

Métodos:

```php
findById()
findAll()
create()
update()
delete()
```

ou métodos mais específicos quando isso melhorar a clareza:

```php
findIncidentById()
listIncidents()
createIncident()
updateIncidentStatus()
```

Não crie Repository ou Service apenas porque "toda entidade precisa ter um".

A estrutura deve ser justificada pela arquitetura escolhida.

---

# 13. Callbacks e funções anônimas

Evite funções anônimas longas.

Evite:

```php
$incidents->filter(function ($incident) {
    // várias linhas
});
```

quando isso prejudicar a leitura ou quando uma abordagem mais clara estiver disponível.

Em callbacks simples, funções anônimas são aceitáveis quando tornam o código mais legível.

O objetivo não é proibir closures, mas evitar blocos complexos escondidos dentro de chamadas de métodos.

Quando um callback possuir lógica significativa, considere extrair essa lógica para um método com nome descritivo.

---

# 14. Valores intermediários

Quando uma expressão for complexa ou seu significado não for óbvio, extraia o valor para uma variável.

Em vez de:

```php
$this->service->process(array_values($response->data));
```

quando isso prejudicar a compreensão, prefira:

```php
$incidentData = array_values($response->data);

$this->service->process($incidentData);
```

O objetivo é facilitar a leitura e permitir que o desenvolvedor compreenda rapidamente o fluxo.

---

# 15. Banco de dados

Utilize nomes consistentes para tabelas e colunas.

Para a entidade principal:

```text
incidents
```

Exemplos de campos:

```text
id
title
description
severity
owner
status
created_at
updated_at
```

Mantenha os nomes das colunas em `snake_case`.

Evite misturar:

```text
incidentTitle
incident_title
IncidentTitle
```

na mesma estrutura.

---

# 16. Migrations

Os nomes das migrations devem descrever a alteração realizada.

Exemplos:

```text
create_incidents_table
add_index_to_incidents_status
add_owner_to_incidents_table
```

Evite nomes genéricos como:

```text
update_database
fix_table
change_data
migration_1
```

---

# 17. Testes

Os nomes dos testes devem explicar o comportamento que está sendo protegido.

Prefira:

```text
it_creates_an_incident_with_open_status
it_requires_title_when_creating_an_incident
it_requires_description_when_creating_an_incident
it_filters_incidents_by_status
it_filters_incidents_by_severity
critical_incident_cannot_move_directly_from_open_to_resolved
critical_incident_can_move_from_open_to_in_progress
critical_incident_can_move_from_in_progress_to_resolved
```

O teste deve deixar claro:

**dado determinado cenário → quando determinada ação acontece → então determinado resultado deve ocorrer.**

---

# 18. Regras críticas devem possuir testes

Regras de negócio importantes devem possuir testes automatizados.

Especialmente as regras de transição de status.

Exemplo:

```text
Open → In Progress → Resolved
```

deve ser permitido quando aplicável.

Já:

```text
Critical + Open → Resolved
```

deve ser rejeitado.

Não dependa exclusivamente de testes manuais ou da interface para proteger regras de negócio.

---

# 19. Frontend — Next.js / React / TypeScript

Utilize:

* PascalCase para componentes;
* camelCase para variáveis;
* camelCase para funções;
* nomes descritivos;
* TypeScript para tipagem.

Exemplos:

```text
IncidentList
IncidentForm
IncidentDetails
IncidentFilters
IncidentStatusBadge
IncidentSeverityBadge
```

Funções:

```typescript
createIncident()
fetchIncidents()
fetchIncident()
updateIncidentStatus()
filterIncidents()
```

Hooks:

```typescript
useIncidents()
useIncident()
```

Evite nomes genéricos:

```text
Component
Data
Helper
Utils
Handler
Thing
```

quando for possível utilizar um nome que represente a responsabilidade.

---

# 20. Consistência entre Backend e Frontend

Quando uma entidade existir nos dois lados da aplicação, mantenha conceitos equivalentes.

Backend:

```php
Incident
IncidentStatus
IncidentSeverity
```

Frontend:

```typescript
Incident
IncidentStatus
IncidentSeverity
```

Backend:

```php
updateIncidentStatus()
```

Frontend:

```typescript
updateIncidentStatus()
```

Não crie nomes diferentes para a mesma operação sem necessidade.

Isso facilita a comunicação entre frontend e backend e reduz confusão durante a manutenção.

---

# 21. API

Os endpoints devem seguir uma convenção consistente.

Exemplo esperado:

```text
GET    /api/incidents
POST   /api/incidents
GET    /api/incidents/{id}
PUT    /api/incidents/{id}
PATCH  /api/incidents/{id}/status
DELETE /api/incidents/{id}
```

A implementação final deve seguir o desenho definido no `PLAN.md`.

Não crie endpoints diferentes para a mesma finalidade.

---

# 22. Tratamento de erros

Mensagens e estruturas de erro devem ser claras.

Evite mensagens genéricas como:

```text
Error
Something went wrong
Invalid data
```

quando for possível informar o problema.

Prefira mensagens que expliquem o motivo da rejeição.

Exemplo:

```text
A Critical incident must move to In Progress before it can be Resolved.
```

A mensagem apresentada ao usuário deve ser compreensível, mas a regra também deve ser protegida no backend.

---

# 23. Código existente

Antes de criar uma nova função, classe ou componente:

1. Procure se já existe algo equivalente.
2. Verifique como o projeto resolve problemas semelhantes.
3. Reutilize quando fizer sentido.
4. Evite duplicação.
5. Preserve a convenção já utilizada.

Não introduza uma nova convenção apenas porque uma alternativa parece mais elegante.

A consistência do projeto é mais importante do que preferências individuais.

---

# 24. Comentários

Não escreva comentários para explicar código óbvio.

Evite:

```php
// Incrementa o contador
$count++;
```

Prefira código autoexplicativo.

Comentários devem explicar principalmente:

* decisões não óbvias;
* regras de negócio;
* limitações;
* decisões temporárias;
* motivos de uma implementação incomum.

Não utilize comentários para compensar nomes ruins.

---

# 25. Princípio de simplicidade

Para o hackathon, prefira a solução mais simples que:

* atende ao requisito;
* mantém a regra de negócio correta;
* pode ser testada;
* é fácil de entender;
* pode ser executada localmente;
* pode ser mantida posteriormente.

Não implemente complexidade antecipadamente.

---

# 26. Regra para a IA

Ao gerar ou modificar código, a IA deve:

1. Consultar este documento.
2. Consultar o código existente.
3. Identificar as convenções já utilizadas.
4. Seguir as convenções existentes.
5. Evitar introduzir novos padrões sem necessidade.
6. Priorizar clareza e simplicidade.
7. Não criar funções ou classes duplicadas.
8. Manter nomenclatura consistente entre backend e frontend.

Quando houver conflito entre este documento e uma convenção já consolidada em determinado módulo, analisar o contexto antes de alterar o padrão.

Se uma decisão diferente for necessária, registrar a justificativa no `AI_LOG.md` quando for uma decisão relevante.

---

# 27. Regra final

Antes de considerar uma alteração de código concluída, verifique:

```text
Nome claro?
↓
Responsabilidade clara?
↓
Consistente com o projeto?
↓
Sem duplicação?
↓
Sem complexidade desnecessária?
↓
Regra de negócio protegida?
↓
Testes adequados?
↓
Validado?
```

Código bom para este projeto não é o código mais sofisticado.

É o código que **expressa claramente a intenção, atende ao requisito e pode ser facilmente compreendido e validado**.
