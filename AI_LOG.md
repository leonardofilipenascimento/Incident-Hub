# AI_LOG.md

Registro da evolução das especificações e decisões técnicas tomadas durante o desenvolvimento do Incident Hub, conforme exigido pela Seção 10 de `prompt-principal-hackathon-sdd.md`.

---

## [01] Implementação da metodologia SDD e harmonização dos padrões

**Data:** 2026-09-05

**Contexto:**
Leitura e adoção integral do `prompt-principal-hackathon-sdd.md` como prompt principal do projeto, estabelecendo Spec-Driven Development (SDD) como metodologia obrigatória: nenhuma linha de código ou teste é escrita antes da SPEC correspondente estar definida e validada.

**Decisões e ações tomadas:**
- Confirmada a hierarquia de referências: Challenge Pack → SPEC → PLAN.md → PadraoDeCodigo.md → padroes-nomenclatura.md → conventional-commits.md → Código existente → Testes → Implementação.
- Identificada e corrigida divergência de nomenclatura dos arquivos internos em relação ao prompt principal: `PadraoDePCodigo.md` → `PadraoDeCodigo.md`, `Plano.md` → `PLAN.md`, `Todo.md` → `TODO.md`, `Start.md` → `START.md`. Renomeação autorizada pelo desenvolvedor.
- `padroes-nomenclatura.md` estava vazio (ou continha conteúdo de outro projeto/contexto, "Capere", não relacionado ao Incident Hub); foi substituído por um esboço com os padrões aprovados para este projeto:
  - snake_case para tabelas/colunas de banco de dados e chaves de payload JSON;
  - camelCase para variáveis, métodos e funções em PHP e TypeScript;
  - PascalCase para Classes, Models, Controllers, Requests, Resources e types/interfaces;
  - kebab-case para segmentos de rota de API;
  - verbos descritivos e claros para nomes de regras de validação e mensagens de erro.
- `START.md` preenchido com: Nome (Leonardo Filipe), horário de início (8:25) e ferramentas de IA utilizadas (Claude Code + Gemini), e metodologia (SDD).
- Primeiro commit (`Initial commit`) contendo `START.md`, seguido de commit `docs: planejamento-projeto` com os demais documentos de planejamento (PLAN.md, PadraoDeCodigo.md, TODO.md, conventional-commits.md, padroes-nomenclatura.md, prompt-principal-hackathon-sdd.md), conforme Conventional Commits.

**Pendências identificadas:**
- Challenge Pack ainda não recebido — bloqueia o mapeamento definitivo de requisitos em módulos/SPECs no `PLAN.md` e a criação de `specs/spec-incidents.md`.
- `TODO.md` segue vazio até que a primeira SPEC seja definida (Seção 7 exige que o TODO seja derivado da SPEC).

**Nenhum desvio de padrão foi rejeitado nesta etapa** (apenas planejamento e organização documental, sem código ou testes envolvidos).

---

## [02] SPEC validada, PLAN/TODO finalizados e scaffold do projeto

**Data:** 2026-09-05

**Contexto:**
Challenge Pack (`CHALLENGE_PACK.md`) recebido e analisado. SPEC do módulo de Incidentes elaborada e validada pelo desenvolvedor. Estrutura inicial do projeto criada para viabilizar o próximo passo do ciclo SDD (CONTRACT/TEST FIRST).

**Decisões e ações tomadas:**
- Criado `specs/spec-incidents.md` cobrindo RF01–RF04 e RT01–RT04: 5 endpoints (`POST/GET /incidents`, `GET /incidents/{id}`, `PATCH /incidents/{id}/status`, `PATCH /incidents/{id}/severity`), schemas de `Incident` e `IncidentStatusHistory`, e 7 regras de negócio em Given-When-Then.
- **Desvio/decisão assumida (fora do texto literal do Challenge Pack):** adicionado o endpoint `PATCH /incidents/{id}/severity`, pois RF03 exige rastrear "alteração de status **ou severidade**" no histórico, mas o Challenge Pack não especifica um contrato explícito para alterar severidade. Decisão validada pelo desenvolvedor antes de prosseguir.
- Histórico (`IncidentStatusHistory`) desenhado como tabela única e imutável, com colunas nulas para status e severidade dependendo do tipo de alteração, evitando duas tabelas separadas (RF03 + Seção 22, anti-overengineering).
- `PLAN.md` finalizado com mapeamento completo Challenge Pack → módulo único "Incidentes" (sem módulos extras não exigidos, ex.: autenticação).
- `TODO.md` gerado a partir da SPEC (migrations, models, testes por regra de negócio, requests, controller, seeder, frontend).
- Commit `docs(spec): especifica contratos de incidentes e finaliza plano` com PLAN.md, TODO.md, specs/spec-incidents.md, CHALLENGE_PACK.md.
- **Scaffold do backend:** `composer create-project laravel/laravel backend` (Laravel 12, PHP 8.3). Banco configurado para MySQL (RT02), banco `incident_hub` criado manualmente pelo desenvolvedor via `sudo mysql`. Usuário dedicado `incident_hub`@`localhost` criado (em vez de usar `root`, que só autentica via socket local/auth_socket e não via TCP) e configurado em `backend/.env` e `.env.example`. Migrations base do Laravel validadas com sucesso contra o banco real.
- **Scaffold do frontend:** `create-next-app` com TypeScript, App Router, `src/` e alias `@/*` (RT01).
- Ignorado o arquivo `backend/CLAUDE.md` gerado automaticamente pelo skeleton do Laravel, que sugere instalar `laravel/boost`: decisão de **não instalar** essa dependência extra por não ser exigida pelo Challenge Pack nem pela SPEC (Seção 22 — regra contra overengineering).
- Commits separados por responsabilidade: `chore(backend): inicializa projeto Laravel` e `chore(frontend): inicializa projeto Next.js`.

**Pendências identificadas:**
- Escrever testes de contrato e de regra de negócio (PHPUnit) a partir de `specs/spec-incidents.md`, antes de qualquer implementação (próximo passo do ciclo SDD — TEST FIRST).
- Migrations específicas do domínio (`incidents`, `incident_status_histories`, `incident_affected_systems`) ainda não criadas.

**Nenhuma sugestão foi rejeitada nesta etapa**, além da decisão de não instalar `laravel/boost` (auto-sugerido pelo scaffold, não solicitado pelo desenvolvedor).

---

## [03] Schema de domínio, testes de contrato/regra de negócio (TEST FIRST)

**Data:** 2026-09-05

**Contexto:**
Passo TEST FIRST do ciclo SDD (Seção 4): criação do schema de domínio (migrations, models, enums, factory) diretamente derivado da SPEC, seguido dos testes de contrato e de regra de negócio, **antes** de qualquer Request/Controller/rota.

**Decisões e ações tomadas:**
- `php artisan install:api` instalou por padrão o Laravel Sanctum (rotas de token) e uma migration `personal_access_tokens`. **Revertido**: rollback da migration, `composer remove laravel/sanctum`, remoção de `config/sanctum.php` e limpeza de `routes/api.php` — o Challenge Pack não exige autenticação, então manter Sanctum seria overengineering (Seção 22).
- Criadas migrations `incidents`, `incident_affected_systems` (relação 1:N, decisão já registrada em `PLAN.md`) e `incident_status_histories` (tabela única e imutável, com colunas `previous_status`/`new_status`/`previous_severity`/`new_severity` nuláveis conforme o tipo de alteração — decisão da entrada [02]).
- Criados enums nativos PHP `IncidentStatus` e `IncidentSeverity` (backed string), conforme recomendação de `PadraoDeCodigo.md` (Seções 6 e 7) para evitar strings literais espalhadas pelo código.
- **Desvio identificado e resolvido a favor do Challenge Pack:** `PadraoDeCodigo.md` (Seção 6) lista os status oficiais como `Open`, `In Progress`, `Resolved` — sem `Closed`. O Challenge Pack (fonte primária de verdade, Seção 1 do prompt principal) exige o status `Closed` (RF02, regras 3 e 4). O enum `IncidentStatus` foi implementado com os 4 status do Challenge Pack; `PadraoDeCodigo.md` deveria ser atualizado para refletir isso (pendência abaixo).
- Testes de banco de dados configurados para rodar contra MySQL real (`incident_hub_test`), não SQLite em memória, pois o ambiente não possui a extensão `pdo_sqlite` instalada e não há acesso `sudo` para instalá-la. Banco de teste criado manualmente pelo desenvolvedor.
- Criados 24 testes de feature em `tests/Feature/Incidents/` (`IncidentCreationTest`, `IncidentListingTest`, `IncidentDetailTest`, `IncidentStatusTransitionTest`, `IncidentSeverityUpdateTest`), cobrindo os 5 endpoints e as 7 regras de negócio da SPEC, com nomes de teste em `snake_case` descrevendo o comportamento (`PadraoDeCodigo.md`, Seção 17), usando o atributo `#[Test]` do PHPUnit 12.
- Suíte executada e validada em **estado RED**: 24/24 testes falham com `404` (rotas ainda não existem), confirmando que a infraestrutura (banco, migrations, models, factory) está correta e que os testes realmente exercitam o contrato antes da implementação (Seção 28 — "TESTES DE CONTRATO (FALHANDO)").
- `TODO.md` atualizado marcando os itens de migration/model/testes como concluídos.

**Pendências identificadas:**
- Atualizar `PadraoDeCodigo.md` (Seção 6) para incluir o status `Closed`, alinhando com o Challenge Pack.
- Implementar `CreateIncidentRequest`, `UpdateIncidentStatusRequest`, `UpdateIncidentSeverityRequest`, `IncidentController`, `IncidentResource` e as rotas em `routes/api.php` para levar a suíte ao estado GREEN.
- Criar `DatabaseSeeder`/`IncidentSeeder` com no mínimo 5 incidentes (RT02).

**Nenhuma sugestão foi rejeitada nesta etapa**, além da remoção do Sanctum (auto-instalado, não solicitado).

---

## [04] Implementação (IMPLEMENT) e correção do desvio no PadraoDeCodigo.md

**Data:** 2026-09-05

**Contexto:**
Passo IMPLEMENT do ciclo SDD: construção de Requests, Service, Controller, Resources e rotas para satisfazer os 24 testes de contrato/regra de negócio escritos na entrada [03], levando a suíte de RED para GREEN.

**Decisões e ações tomadas:**
- Corrigido o desvio identificado na entrada [03]: `PadraoDeCodigo.md` (Seção 6) atualizado para incluir o status `Closed` e alinhar o exemplo de enum PHP ao PascalCase (`IncidentStatus::Open`, `::InProgress`, `::Resolved`, `::Closed`) já usado no código, em vez do `SCREAMING_SNAKE_CASE` originalmente sugerido no documento.
- Regras de negócio (Regras 1–7 da SPEC) implementadas em `App\Services\IncidentService`, com os nomes de método sugeridos literalmente por `PadraoDeCodigo.md` (Seções 5, 8 e 9): `validateStatusTransition()`, `canChangeStatus()`, `isCriticalIncident()`, `updateIncidentStatus()`, `updateIncidentSeverity()`, `createIncident()`, `listIncidents()`. Controllers permaneceram enxutos, delegando à service (Seção 9 do documento).
- Violações de regra de negócio (Regras 2, 3, 4) lançam `Illuminate\Validation\ValidationException::withMessages()` a partir da service, reaproveitando o mecanismo padrão do Laravel para produzir o payload `{ message, errors: { campo: [...] } }` — mesmo formato usado pelas validações de shape das Form Requests (Seção 10 da SPEC), sem necessidade de exception/response customizados.
- Comentário obrigatório em transições para `Resolved`/`Closed` (Regras 5 e 6) implementado como validação condicional (`Rule::requiredIf`) diretamente em `UpdateIncidentStatusRequest`, por depender apenas do valor de entrada (`status` do próprio request), não do estado atual do incidente.
- `IncidentResource` configurado com `$wrap = null` (sem envelope `data` em respostas de recurso único), enquanto o `IncidentController::index()` envolve a coleção manualmente em `{ "data": [...] }`, para casar exatamente com os exemplos de payload de `specs/spec-incidents.md` (único objeto sem envelope vs. listagem com `data`). **Desvio leve assumido:** o `IncidentResource` retorna `description` também na listagem, embora o exemplo ilustrativo da SPEC (seção 5) não incluísse esse campo — optou-se por uma única classe de Resource para os dois casos em vez de duas variações, evitando duplicação sem valor funcional (Seção 22).
- 404 customizado ("Incidente nao encontrado.") exigiu registrar o `render()` para `Symfony\Component\HttpKernel\NotFoundHttpException` (verificando `getPrevious() instanceof ModelNotFoundException`), e não para `ModelNotFoundException` diretamente — o Handler padrão do Laravel já converte essa exceção antes de despachar para os renderers customizados.
- `IncidentSeeder` criado com 5 incidentes cobrindo as 4 severidades e os 4 status (`Open`, `In Progress`, `Resolved`, `Closed`), construído via `IncidentService` (reaproveitando as mesmas regras de transição, garantindo seeds sempre válidos). `DatabaseSeeder` atualizado para chamar `IncidentSeeder` no lugar do usuário de demonstração padrão do Laravel (fora do escopo do Challenge Pack).
- Suíte completa validada em **estado GREEN**: 26/26 testes passando (24 de incidentes + 2 padrão do Laravel), rodando contra MySQL real (`incident_hub_test`).
- `TODO.md` atualizado marcando todos os itens de backend do módulo de Incidentes como concluídos.

**Pendências identificadas:**
- Nenhuma pendência de backend para o módulo de Incidentes. Próximo passo: frontend (Next.js) consumindo os contratos de `specs/spec-incidents.md`, ou revisão/aceite do desenvolvedor antes de avançar.

**Nenhuma sugestão foi rejeitada nesta etapa.**
