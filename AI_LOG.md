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

---

## [09] Implementação do frontend (Next.js) consumindo specs/spec-incidents.md

**Data:** 2026-09-05

**Contexto:**
Passo IMPLEMENT do frontend, cobrindo os 6 itens de `TODO.md`: listagem com filtros/busca, criação, detalhe com histórico, transição de status, alteração de severidade e tratamento visual de erros 422/404. Sem SPEC nova — reaproveita integralmente `specs/spec-incidents.md`.

**Decisões e ações tomadas:**
- Estrutura: `src/types/incident.ts` (tipos espelhando os schemas da SPEC), `src/lib/api.ts` (`fetchIncidents`, `fetchIncident`, `createIncident`, `updateIncidentStatus`, `updateIncidentSeverity`, todos com nomes literais sugeridos por `PadraoDeCodigo.md` Seção 19), `src/hooks/useIncidents`/`useIncident` (hooks sugeridos na mesma seção), componentes `IncidentList`, `IncidentFilters`, `IncidentForm`, `IncidentDetails`, `IncidentTimeline`, `IncidentStatusBadge`, `IncidentSeverityBadge` — nomenclatura idêntica aos exemplos do documento.
- Optado por Client Components (`"use client"`) em vez de Server Components para toda a parte interativa, já que o próprio `PadraoDeCodigo.md` sugere hooks de data-fetching client-side (`useIncidents()`/`useIncident()`) em vez do padrão `async function Page()` de Server Component. Mantém um único padrão de fetching em todo o app (Seção 25 — simplicidade).
- **Ajuste de infraestrutura necessário:** variáveis `NEXT_PUBLIC_*` do Next.js são embutidas em **build time**, não runtime; o `docker-compose.yml` original passava `NEXT_PUBLIC_API_URL` via `environment:` (efeito nenhum, pois o build já tinha terminado). Corrigido: `frontend/Dockerfile` recebe `ARG NEXT_PUBLIC_API_URL` e `docker-compose.yml` passa via `build.args`. Adicionado `frontend/.env.example` (e exceção no `.gitignore`, que antes ignorava `.env.example` também por engano) para desenvolvimento local.
- **Erro de lint real corrigido, não suprimido às cegas:** `eslint-plugin-react-hooks` (regra nova `set-state-in-effect`, parte do conjunto de regras do React Compiler, vinda com Next.js 16/React 19.2) reprova `setLoading(true)`/`setError(null)` síncronos no corpo de um `useEffect` de data-fetching — o mesmo padrão documentado oficialmente em react.dev ("Synchronizing with Effects — Fetching data"). Decisão: suprimir pontualmente com `eslint-disable`/`eslint-enable` bem comentado, em vez de reescrever com `useReducer`/refs só para satisfazer a regra sem ganho real (Seção 22 — anti-overengineering).
- Formulário de criação usa um único campo de texto para `affected_systems` (separado por vírgula) em vez de uma UI dinâmica de adicionar/remover itens — solução mais simples que atende ao contrato (array de strings) sem complexidade extra de estado (Seção 25).
- **Validação end-to-end real no navegador** (não só `npm run build`/`tsc`): como não havia `chromium-cli` disponível no ambiente, usei Playwright diretamente (script descartável) para: build de produção + `docker compose up --build frontend`, e também `npm run dev` local. Fluxos verificados com screenshot: listagem com os 5 seeds, filtro por severidade, detalhe com histórico, criação de incidente com redirecionamento, erro 422 exibido inline no formulário de transição de status (mensagem exata da API), e transição de status bem-sucedida atualizando badge e timeline em tempo real. Zero erros de console em todos os fluxos.
- **Achado incidental durante a validação (ambiente, não código):** o banco de desenvolvimento (`incident_hub`) estava com 0 registros ao iniciar esta etapa, apesar do registro na entrada [08] de tê-lo resetado — schema intacto, dados ausentes; causa exata não identificada com certeza (hipótese mais provável: o container MySQL foi recriado em algum momento anterior, ao ter sua configuração de volumes alterada, e a reinicialização subsequente do volume nomeado — ainda que sem indício de recriação nos logs — não deixou rastro conclusivo). Sem impacto real: dado de seed é descartável por definição. Resolvido com `php artisan db:seed` e, ao final, `migrate:fresh --seed` para deixar os ids previsíveis (1–5) nesta situação.
- Container `frontend` do `docker-compose.yml` reconstruído com o novo código (antes rodava o template padrão do `create-next-app`, pois nunca havia sido rebuildado desde a entrada [05]).

**Pendências identificadas:**
- Nenhuma pendência de escopo. Frontend cobre os 6 itens do TODO. Possível melhoria futura não solicitada: UI dedicada para múltiplos `affected_systems` em vez de texto separado por vírgula — não implementada por não ser exigida e por manter a simplicidade.

**Nenhuma sugestão foi rejeitada nesta etapa.**

---

## [10] Realinhamento crítico de escopo — CHALLENGE_PACK.md estava incorreto

**Objetivo:** Verificar aderência do projeto ao Challenge Pack oficial e corrigir qualquer divergência.

**Contexto:** O desenvolvedor colou no chat o texto completo e oficial do Challenge Pack (com seções numeradas 1-26, horários de início/checkpoint/code freeze/entrega, e as 15 perguntas obrigatórias do `FINAL_REPORT.md`). Esse documento **diverge significativamente** do `CHALLENGE_PACK.md` que já estava no repositório e vinha sendo usado como fonte de verdade desde o início do projeto.

**Instrução:** "Esses são os requisitos que me passaram... verifique se tudo está atendido" e, em seguida, "Pode corrigir tudo".

**Resultado — divergências encontradas:**
- Campo obrigatório **`owner` (responsável)** não existia em nenhuma camada (schema, API, UI). Real e obrigatório desde a criação até a listagem e o detalhe.
- **Dashboard** (contagem de `Open`, `Critical` não resolvidos, `Resolved`) não existia — requisito explícito e completo, nunca implementado.
- **Dados de seed** completamente diferentes dos 3 incidentes exigidos (Payment API instability/Critical/Ana/Open; Reconciliation delay/High/Bruno/In Progress; Incorrect customer notification/Medium/Carla/Resolved).
- Status real tem **apenas 3 valores** (`Open`/`In Progress`/`Resolved`) — o `Closed` implementado não existe no documento oficial.
- Campo `affected_systems`, endpoint `PATCH /severity` e regra de comentário obrigatório ao resolver/fechar — **nenhum desses existe** no documento oficial; foram construídos em cima do `CHALLENGE_PACK.md` incorreto.
- `FINAL_REPORT.md`, `PLAN.md` e `README.md` não seguiam a estrutura/seções exatamente exigidas pelo documento oficial.

Correção aplicada: `CHALLENGE_PACK.md` substituído pelo texto oficial; `specs/spec-incidents.md` reescrita; migrations/models/enums/`IncidentService`/Requests/Resources/Controllers/rotas/seeder do backend reescritos (adicionado `owner` e `GET /dashboard`; removidos `affected_systems`, `PATCH /severity`, status `Closed`, regra de comentário obrigatório); suíte de testes de backend reescrita; frontend (`types`, `IncidentForm`, `IncidentList`, `IncidentDetails`, `IncidentTimeline`, novo `DashboardSummary`) atualizado na mesma direção; `PLAN.md`, `README.md` e `FINAL_REPORT.md` reescritos seguindo exatamente as seções/perguntas exigidas pelo documento oficial; `postman/Incident-Hub.postman_collection.json` atualizada.

**Regressão encontrada durante a correção:** o serviço `backend` do `docker-compose.yml` não monta o código como volume — a imagem Docker é uma cópia estática do host feita em build time. Editar arquivos no host não reflete no container até um `docker compose up -d --build backend` explícito. Isso já havia acontecido antes no projeto (execução de testes contra código desatualizado) e se repetiu aqui: um `migrate:fresh` rodou contra a imagem antiga e recriou a tabela `incident_affected_systems`, que já havia sido removida do código-fonte. Identificado ao ver a migration antiga aparecer no output; corrigido rebuildando a imagem antes de cada validação subsequente.

**Validação:** `docker compose up -d --build backend` + `migrate:fresh --seed --force` reconstruindo o schema do zero; `./vendor/bin/phpunit` → 23/23 GREEN; `curl /api/incidents` confirmando os 3 incidentes exatos exigidos; `curl /api/dashboard` confirmando as contagens corretas (1/1/1 com o seed); frontend reconstruído no Docker (`docker compose up -d --build frontend`) e validado com script Playwright — dashboard, listagem (título/severidade/responsável/status), detalhe, e a regra crítica bloqueando corretamente com a mensagem exata do documento oficial.

**Decisão:** Manter o realinhamento completo — nenhuma funcionalidade fora do escopo oficial foi preservada, mesmo já estando implementada e testada, seguindo a Seção 22 do Challenge Pack ("funcionalidades extras não compensam requisitos obrigatórios que não funcionam"). Este episódio é registrado como o maior erro do projeto em `FINAL_REPORT.md` (pergunta 5).

---

## [11] Bug crítico: rodar a suíte de testes apagava o banco de demonstração

**Objetivo:** Auditar o projeto completo contra `CHALLENGE_PACK.md` e `prompt-principal-hackathon-sdd.md` a pedido do desenvolvedor.

**Contexto:** Durante a auditoria, o banco `incident_hub` (dev/demo, dentro do container Docker) apareceu vazio outra vez — o mesmo sintoma "misterioso" registrado (e nunca totalmente explicado) desde etapas anteriores do projeto. Desta vez foi investigado até a causa raiz, em vez de apenas re-executar o seed.

**Instrução:** Investigação própria, disparada pela auditoria solicitada pelo desenvolvedor.

**Resultado — causa raiz confirmada:** Dentro do container Docker, `docker-compose.yml` define `DB_DATABASE=incident_hub` como variável de ambiente real do processo, o que popula `$_SERVER['DB_DATABASE']` na inicialização do PHP. O override `<env name="DB_DATABASE" ... force="true"/>` do `phpunit.xml` só atualiza `$_ENV`/`putenv()` — nunca `$_SERVER`. O helper `env()` do Laravel lê `$_SERVER` com prioridade, então o override do PHPUnit era **silenciosamente ignorado dentro do Docker**: `config('database.connections.mysql.database')` continuava resolvendo `incident_hub` mesmo com `$_ENV`/`getenv()` corretamente mostrando `incident_hub_test`. Resultado prático: toda vez que a suíte completa rodava dentro do container (via `RefreshDatabase`, que executa `migrate:fresh`), ela **apagava o banco de desenvolvimento/demonstração** em vez do banco de teste. Localmente (fora do Docker) o bug não se manifestava, porque não há variável de ambiente real do SO conflitando — `DB_DATABASE` vem só do `.env` via Dotenv, sem popular `$_SERVER` antes do PHPUnit rodar.

Diagnosticado com um teste descartável que imprimia `config()`, `$_ENV`, `$_SERVER` e `getenv()` lado a lado dentro de uma execução real do PHPUnit — a divergência entre `$_SERVER` (`incident_hub`, errado) e os demais (`incident_hub_test`, corretos) isolou a causa exata.

**Correção:** `backend/tests/TestCase.php` agora sobrescreve `createApplication()` e muta `config('database.connections.mysql.database')` diretamente para `incident_hub_test` (mais `DB::purge('mysql')` para descartar qualquer conexão já resolvida), **antes** do `RefreshDatabase` migrar o schema. Essa mutação em PHP puro é imune ao problema de precedência `$_SERVER`/`$_ENV`, pois não depende de `env()`/variável de ambiente nenhuma. Removido do `phpunit.xml` o override de `DB_CONNECTION`/`DB_DATABASE` que não funcionava dentro do Docker (mantido um comentário explicando o porquê, para não ser reintroduzido por engano).

**Validação:** banco `incident_hub` seedado com os 3 incidentes exigidos; suíte completa rodada (`docker compose exec backend ./vendor/bin/phpunit`, 23/23 GREEN); banco `incident_hub` conferido **imediatamente depois** — os 3 incidentes continuavam lá. Confirmado também que `incident_hub_test` tem o schema correto (sem a tabela `incident_affected_systems`, já removida) e 0 linhas após a suíte (esperado, pois `RefreshDatabase` roda cada teste em uma transação revertida). Repetido local (fora do Docker) para garantir que a correção não quebrou esse caminho: 23/23 GREEN.

**Decisão:** Correção crítica para a entrega — sem ela, um avaliador que seguisse o `README.md` (`docker compose exec backend ./vendor/bin/phpunit`) apagaria os dados de demonstração exigidos pela Seção 11 do Challenge Pack sem perceber. Imagem Docker reconstruída com a correção definitiva.

---

## [12] Deploy de demonstração (Vercel + Railway) e documentação OpenAPI/Swagger

**Objetivo:** Publicar uma demonstração pública da aplicação (não exigida pelo Challenge Pack, decisão do desenvolvedor) e adicionar documentação interativa da API em formato OpenAPI/Swagger.

**Contexto:** Desenvolvedor pediu ajuda para publicar o frontend no Vercel. Vercel não roda o backend Laravel/MySQL (é serverless) — só o frontend foi publicado lá.

**Instrução:** Deploy guiado passo a passo pela interface do Vercel e do Railway (contas do próprio desenvolvedor); depois, pedido para atualizar `README.md` com os links e criar documentação Swagger na raiz do projeto.

**Resultado:**
- `backend/Dockerfile`/`docker-entrypoint.sh` ajustados para respeitar a variável `PORT` em runtime (`${PORT:-8000}`), necessário para hospedar em serviços como Railway que atribuem a porta dinamicamente — mantendo o fallback 8000 para uso local/Docker Compose.
- Frontend publicado no Vercel (Root Directory `frontend`, preset Next.js).
- Backend publicado no Railway (Root Directory `backend`, build via Dockerfile detectado automaticamente) com um MySQL gerenciado no mesmo projeto, variáveis `DB_*` referenciando o serviço MySQL via `${{MySQL.MYSQLHOST}}` etc.
- Etapa intermediária com túnel `ngrok` (`localhost:8000` → URL pública) usada para validar a integração Vercel↔backend antes do Railway estar pronto; descontinuada após o Railway entrar no ar.
- Adicionado header `ngrok-skip-browser-warning` em todas as chamadas do frontend (`frontend/src/lib/api.ts`) — necessário enquanto a API esteve atrás do túnel ngrok; inofensivo contra qualquer outro backend.
- Criados `openapi.yaml` (contrato OpenAPI 3.0 dos 5 endpoints, espelhando `specs/spec-incidents.md`) e `docs/swagger.html` (Swagger UI standalone via CDN, sem dependências novas no projeto).
- `README.md` atualizado com seção "Deploy" (links de produção) e seção "Documentação interativa da API (Swagger)".

**Validação:** testado com Playwright contra a URL pública do Vercel (`incident-hub-indol.vercel.app`) consumindo a API pública do Railway — listagem, dashboard e criação de incidente funcionando, zero erros de console. `docs/swagger.html` testado servindo a raiz do projeto com `python3 -m http.server`, confirmando os 5 endpoints e todos os schemas renderizados corretamente pelo Swagger UI.

**Decisão:** Manter o deploy como extra de demonstração, deixando claro no README que não é exigido pelo desafio (evita a impressão de escopo inflado, conforme Seção 22 do Challenge Pack). Confirmado com o desenvolvedor que, sem esse deploy, a aplicação já atendia 100% ao requisito de execução local.
