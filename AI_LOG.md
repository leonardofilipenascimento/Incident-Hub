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

---

## [05] Containerização (Docker) — requisito não mapeado originalmente no PLAN.md

**Data:** 2026-09-05

**Contexto:**
O desenvolvedor apontou que o planejamento inicial não incluía containerização. Ao reverificar `CHALLENGE_PACK.md`, o Critério de Aceite 3 já exigia explicitamente: "O sistema deve manter a consistência dos dados após a reinicialização dos containers/servidores" — ou seja, Docker não é uma adição fora de escopo, é um requisito do Challenge Pack (fonte primária de verdade) que não havia sido mapeado no `PLAN.md` original.

**Decisões e ações tomadas:**
- `PLAN.md` atualizado (nova seção 4.1) com a decisão de containerização e sua justificativa, e a tabela de mapeamento Challenge Pack → Módulos (seção 2) passou a referenciar o Critério de Aceite 3.
- Criados `backend/Dockerfile` (PHP 8.3 CLI Alpine + `pdo_mysql`, Composer com dependências completas, `artisan serve` como servidor), `backend/docker-entrypoint.sh` (roda `migrate --force` de forma idempotente antes de subir o servidor) e `backend/.dockerignore`.
- **Desvio corrigido durante a validação:** a primeira versão do Dockerfile usava `composer install --no-dev`, mas o `bootstrap/cache/packages.php` gerado localmente (com dependências de desenvolvimento, ex. `laravel/pail`) foi copiado para dentro da imagem, causando `Class "Laravel\Pail\PailServiceProvider" not found` e crash-loop do container. Corrigido instalando também as dependências de desenvolvimento na imagem (mais simples que gerenciar cache de manifest de pacotes) e adicionando `bootstrap/cache/*.php` ao `.dockerignore` para evitar recorrência. Efeito colateral aceito: a imagem de produção inclui PHPUnit e outras dev deps — decisão consciente para um contexto de hackathon, favorecendo simplicidade e permitindo ao avaliador rodar os testes dentro do container.
- Criados `frontend/Dockerfile` (build multi-stage `node:22-alpine`, `npm run build` + `next start`) e `frontend/.dockerignore`.
- Criado `docker-compose.yml` na raiz com 3 serviços (`mysql`, `backend`, `frontend`), volume nomeado `mysql_data` para persistência, healthcheck do MySQL controlando a ordem de subida (`depends_on: condition: service_healthy`).
- Porta do MySQL mapeada para `3307:3306` no host, para não colidir com a instância local de MySQL já usada no ambiente de desenvolvimento (a comunicação interna `backend↔mysql` permanece em `3306` via rede Docker).
- Seed não roda automaticamente no entrypoint (evitaria duplicar dados a cada restart); documentado como passo manual único (`docker compose exec backend php artisan db:seed`).
- **Validado de ponta a ponta:** `docker compose up -d --build` sobe os 3 containers; API respondendo em `:8000`, frontend em `:3000`; seed executado com sucesso via `exec`; **persistência confirmada na prática** — reiniciados os containers `mysql` e `backend` (`docker compose restart`) e os 5 incidentes seedados continuaram presentes, satisfazendo literalmente o Critério de Aceite 3.
- Criado `README.md` (Seção 17 do prompt principal, pendente desde o início do projeto) com instruções de execução via Docker e localmente, e onde encontrar a SPEC.

**Pendências identificadas:**
- Nenhuma relacionada a este ciclo. Containers seguem rodando localmente para inspeção do desenvolvedor.

**Nenhuma sugestão foi rejeitada nesta etapa**, além da correção do próprio erro de build (`--no-dev` revertido).

---

## [06] Correção: testes não rodavam dentro do container

**Data:** 2026-09-05

**Contexto:**
Ao validar "está tudo certo na API?", tentei rodar a suíte de testes dentro do container (`docker compose exec backend php artisan test`) e encontrei dois problemas reais que não haviam sido verificados na entrada [05].

**Decisões e ações tomadas:**
- `backend/.dockerignore` excluía o diretório `tests/` inteiro (herdado de um padrão genérico de "não copiar coisa de dev para produção"), impedindo qualquer execução de teste dentro da imagem — contradição direta com o que o `README.md` da entrada [05] já prometia. Corrigido removendo `tests` do `.dockerignore`.
- `phpunit.xml` fixava `DB_HOST=127.0.0.1`, `DB_PORT`, `DB_USERNAME` e `DB_PASSWORD` como valores hardcoded — funciona fora do container (MySQL local), mas dentro do container `backend` não existe MySQL em `127.0.0.1` (o MySQL é outro container, acessível pelo nome de serviço `mysql` na rede Docker). Corrigido: `phpunit.xml` agora só força (`force="true"`) `DB_CONNECTION` e `DB_DATABASE=incident_hub_test` — garantindo que os testes **nunca** rodem contra o banco de desenvolvimento/demo, mas deixando `DB_HOST`/`DB_PORT`/`DB_USERNAME`/`DB_PASSWORD` herdarem do ambiente real (`.env` local ou variáveis do `docker-compose.yml`), que já usam as mesmas credenciais do usuário `incident_hub` nos dois contextos.
- O banco `incident_hub_test` não existia no MySQL do container (só no MySQL local do host) e o usuário `incident_hub` não tinha permissão sobre ele. Criado `mysql/init/01-test-database.sql`, montado em `/docker-entrypoint-initdb.d` no `docker-compose.yml`, para que qualquer volume novo já nasça com o banco de teste configurado. Como o volume atual já existia (scripts de init só rodam em volume vazio), apliquei a mesma criação manualmente no container já rodando, para não exigir `docker compose down -v` (que apagaria os dados seedados) só para validar.
- **Achado não corrigido, documentado como comportamento conhecido:** `php artisan test` (runner do Collision/Pest-style) produz warnings `file_get_contents(...)` para quase todos os testes dentro desta imagem Alpine, embora as asserções passem (`proc_open` e `sys_get_temp_dir()` verificados e funcionais — causa raiz não identificada, possivelmente relacionada a como o printer do Collision lida com arquivos temporários em Alpine). `./vendor/bin/phpunit` roda a mesma suíte de forma limpa (26/26 OK), tanto local quanto no container. Decisão: documentar e usar `./vendor/bin/phpunit` como comando oficial de teste em vez de investigar/consertar o printer do `artisan test` — não afeta a corretude da suíte, só a apresentação do output, e não vale o tempo de investigação adicional num contexto de hackathon.
- `README.md` atualizado: comando de testes trocado para `./vendor/bin/phpunit` (local e Docker), e adicionado passo de criação do banco `incident_hub_test` na seção de execução local (que não estava documentado antes, apesar de ser um pré-requisito real desde a entrada [03]).
- Validado: 26/26 testes passando tanto localmente quanto dentro do container `backend`, sem afetar os dados de demonstração já seedados no banco `incident_hub`.

**Pendências identificadas:**
- Nenhuma. Causa raiz do warning do `artisan test` em Alpine não foi investigada a fundo por não bloquear nenhum critério de aceite do Challenge Pack.

**Nenhuma sugestão foi rejeitada nesta etapa.**

---

## [07] Sugestão avaliada e rejeitada: endpoint DELETE de incidente

**Data:** 2026-09-05

**Contexto:**
Desenvolvedor perguntou se deveria existir um método DELETE para remover incidente por id.

**Análise:**
`CHALLENGE_PACK.md` (RF01–RF04, RT01–RT04, Critérios de Aceite) não menciona exclusão de incidentes. Além disso, o domínio é de histórico/auditoria (RF03 exige registro imutável de mudanças), e apagar um incidente conflitaria com esse objetivo — sistemas de auditoria tipicamente não removem registros, apenas os encerram (`status=Closed`). Apresentadas três opções ao desenvolvedor: não adicionar, adicionar DELETE simples (hard delete) ou soft delete.

**Decisão do desenvolvedor:** não adicionar. Escopo permanece fiel ao `CHALLENGE_PACK.md`; nenhuma alteração em `specs/spec-incidents.md`, `PLAN.md` ou código.

**Sugestão rejeitada nesta etapa:** endpoint `DELETE /incidents/{id}` (hard ou soft delete).

---

## [08] Auditoria manual pós-teste do desenvolvedor — vazamento de stack trace corrigido

**Data:** 2026-09-05

**Contexto:**
Desenvolvedor testou manualmente todos os endpoints via Postman e pediu para eu também testar, em busca de algo que tivesse passado despercebido. Rodei a suíte automatizada (26/26 OK) e depois uma bateria de 16 casos-limite manuais: 404 em PATCH de status/severidade para id inexistente, valores fora do enum, campos ausentes, `title` só com espaços (trim), item vazio em `affected_systems`, tipo errado em `severity`, campo desconhecido no payload (mass assignment), id não numérico na URL, métodos HTTP não suportados (`DELETE`, `PUT`), busca case-insensitive, filtros combinados sem resultado, e persistência do `comment` no histórico de severidade.

**Achado real (falha de segurança, não de regra de negócio):** com `APP_DEBUG=true` no `docker-compose.yml`, qualquer exceção não tratada explicitamente (ex.: `405 Method Not Allowed` ao chamar `DELETE`/`PUT` em rotas que não suportam o método) retornava o corpo padrão de debug do Laravel: stack trace completo, caminhos absolutos de arquivos do servidor (`/app/vendor/...`) e nomes de classes internas. Isso é exposição de informação sensível (OWASP A05:2021 — Security Misconfiguration), mesmo não fazendo parte dos cenários de erro documentados na SPEC (que cobre apenas 201/200/422/404).

**Correção:** `docker-compose.yml` alterado para `APP_DEBUG: "false"` no serviço `backend`, tornando o ambiente containerizado equivalente a produção nesse aspecto. Erros não tratados agora retornam apenas `{"message": "..."}` sem trace. Logs completos continuam disponíveis via `docker compose logs backend` para depuração. Mantido `APP_DEBUG=true` no `backend/.env` local (fora do Docker), por não ser um ambiente exposto publicamente e ser útil durante desenvolvimento.

**Demais casos testados:** todos os 15 restantes se comportaram conforme a SPEC — nenhum outro desvio encontrado. Suíte automatizada (26/26) revalidada após a correção, banco resetado para o estado seedado limpo.

**Pendências identificadas:** nenhuma.

**Nenhuma sugestão foi rejeitada nesta etapa** (achado corrigido, não uma sugestão em aberto).
