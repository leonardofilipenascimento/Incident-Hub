# FINAL_REPORT.md

### 1. O que foi entregue?

- Cadastro de incidente (título, descrição, severidade, responsável), status sempre nascendo `Open`.
- Listagem de incidentes com filtro por status e por severidade.
- Tela de detalhe com todos os campos exigidos e histórico de alterações de status.
- Alteração de status com a regra de negócio crítica (incidente `Critical` não pode ir direto de `Open` para `Resolved`) e feedback compreensível na tela quando a transição é inválida.
- Histórico de status persistido (status anterior, novo status, timestamp).
- Dashboard com as 3 contagens exigidas (abertos, `Critical` não resolvidos, resolvidos).
- Persistência real via MySQL, com volume Docker nomeado — dados sobrevivem a restart de containers (validado na prática).
- Seed com os 3 incidentes de exemplo exigidos (Payment API instability/Critical/Ana/Open, Reconciliation delay/High/Bruno/In Progress, Incorrect customer notification/Medium/Carla/Resolved).
- 23 testes automatizados de backend (PHPUnit), cobrindo contratos de API e a regra de negócio crítica.
- Docker Compose completo (MySQL + backend + frontend) e README com instruções de execução com e sem Docker.

### 2. O que não foi entregue?

- Testes automatizados de frontend (a validação da UI foi feita manualmente e com um script Playwright descartável durante o desenvolvimento, não uma suíte de testes permanente no repositório).
- Pipeline de CI.
- Vídeo de demonstração (a ser gravado pelo candidato entre o code freeze e a entrega, conforme Seção 24 do Challenge Pack — fora do escopo do que a IA produz).

### 3. O que você deliberadamente decidiu não fazer?

- **Autenticação/autorização** — explicitamente dispensada pelo Challenge Pack (Seção 2).
- **Edição de título/descrição/severidade/responsável após a criação** — o Challenge Pack só pede alteração de status; editar outros campos não foi pedido.
- **Exclusão de incidentes** — avaliado e rejeitado propositalmente (ver pergunta 10): não exigido, e remover um incidente contradiz o objetivo de manter histórico auditável.
- **Paginação e busca textual na listagem** — não exigidas; a listagem ordenada por data de criação já atende ao requisito de identificar rapidamente os incidentes.
- **Campos e endpoints que haviam sido adicionados por engano** (sistemas afetados, endpoint de alteração de severidade, status `Closed`, comentário obrigatório ao resolver) foram **removidos** ao final, quando ficou claro que não faziam parte do documento oficial do desafio (ver pergunta 5).

### 4. Quais foram as três principais decisões técnicas?

1. **Laravel (API REST) + Next.js (SPA) desacoplados**, comunicando via contrato HTTP documentado em `specs/spec-incidents.md` — permite testar o backend isoladamente e trocar o frontend sem tocar na API.
2. **Regras de negócio concentradas em `IncidentService`**, não nos controllers — a trava do `Critical` fica em um único método testável (`validateStatusTransition`), evitando duplicação e facilitando cobertura de teste.
3. **Docker Compose com volume nomeado para o MySQL** — persistência real e reproduzível: qualquer avaliador roda `docker compose up -d --build` e os dados sobrevivem a reinícios, sem depender de configuração manual de banco local.

### 5. Qual foi o maior erro produzido pela IA durante o desenvolvimento?

O projeto foi construído por boa parte do dia em cima de um arquivo `CHALLENGE_PACK.md` que **não correspondia ao documento oficial do desafio**. Esse arquivo continha requisitos que nunca existiram (um campo de "sistemas afetados", um status `Closed`, comentário obrigatório ao resolver/fechar, um endpoint dedicado para alterar severidade) e **omitia dois requisitos reais e centrais**: o campo "responsável" (owner) em cada incidente e o Dashboard com as 3 contagens. A IA tratou esse arquivo como fonte de verdade (conforme a própria metodologia adotada manda) sem nunca contrastá-lo contra o texto oficial, e construiu uma superfície de API inteira — schema, validações, testes, telas — sobre requisitos que não existiam, ao mesmo tempo em que dois requisitos obrigatórios reais ficaram completamente ausentes por horas.

### 6. Como você identificou esse erro?

Ao final do desenvolvimento, o candidato colou o texto completo e oficial do Challenge Pack no chat e pediu para a IA conferir a aderência da solução já construída contra ele. A comparação direta, seção por seção, revelou as divergências: campo `owner` inexistente no schema/API/UI, ausência total do Dashboard, dados de seed completamente diferentes dos exigidos, e um status (`Closed`) e regras (comentário obrigatório, endpoint de severidade) que simplesmente não estavam no documento real.

### 7. Como você corrigiu e validou a correção?

Backend: migrations, models, enums, `IncidentService`, Requests, Resources, Controllers, rotas e seeder foram reescritos para remover o que não era pedido e adicionar o que faltava (`owner`, `GET /dashboard`). A suíte de testes foi reescrita na mesma direção (removidos os testes das regras inexistentes, adicionados testes de `owner` e do dashboard). Validação: `migrate:fresh --seed` reconstruindo o banco do zero, `./vendor/bin/phpunit` passando 23/23, e chamadas diretas à API confirmando que o seed batia exatamente com os 3 incidentes exigidos e que o dashboard retornava as contagens corretas.

Frontend: tipos, formulário de criação, listagem, tela de detalhe e uma nova tela de dashboard foram atualizados/criados na mesma lógica. Validação: build de produção reconstruído no container Docker e testado com um script Playwright (sem `chromium-cli` disponível no ambiente) navegando pelas telas reais e conferindo o conteúdo renderizado contra os dados do backend.

### 8. Houve alguma regressão?

Sim. Durante a correção, um `migrate:fresh` executado dentro do container Docker do backend continuou criando uma tabela que já havia sido removida do código-fonte no host. Causa: o serviço `backend` do `docker-compose.yml` não monta o código como volume — a imagem é uma cópia estática feita no build, então edições no host só chegam ao container após um rebuild explícito (`docker compose up -d --build backend`). Isso já havia causado confusão antes no projeto (testes rodando código desatualizado dentro do container) e se repetiu aqui. Identificado ao notar que uma tabela já deletada continuava sendo migrada; corrigido reconstruindo a imagem antes de cada validação subsequente.

### 9. Em qual parte houve mais retrabalho?

A modelagem de domínio do backend (migrations, models, `IncidentService`) e sua suíte de testes foram escritas duas vezes por completo: uma vez seguindo o `CHALLENGE_PACK.md` incorreto, e novamente do zero após a correção de escopo. O mesmo aconteceu, em menor escala, com os componentes de frontend que dependiam desses mesmos campos (formulário, listagem, detalhe).

### 10. Cite uma situação em que você rejeitou ou alterou uma abordagem sugerida pela IA.

A IA sugeriu implementar um endpoint `DELETE /incidents/{id}` para completar o CRUD. O candidato pediu uma análise antes de aceitar; a IA apontou que o requisito não existia no Challenge Pack e que excluir um incidente contradiz o objetivo de manter um histórico auditável (o próprio requisito de histórico do desafio). Decisão: não implementar — nem como hard delete, nem como soft delete.

### 11. Qual parte da aplicação você considera menos confiável?

O frontend, por não ter uma suíte de testes automatizados própria. Toda a validação da interface foi feita manualmente ou por um script de verificação descartável durante o desenvolvimento; uma alteração futura no frontend pode introduzir uma regressão visual ou funcional sem que nenhum teste automatizado a detecte.

### 12. Se tivesse mais duas horas, quais seriam suas três prioridades?

1. Escrever testes automatizados de frontend (ao menos os fluxos críticos: criar incidente, transicionar status, ver a regra do `Critical` bloqueando na tela).
2. Refinar estados de carregamento/erro da UI e revisar acessibilidade básica (labels, foco, contraste).
3. Configurar um pipeline de CI simples (rodar `./vendor/bin/phpunit` e o build do frontend a cada push) para pegar regressões automaticamente.

### 13. Como você avalia sua estratégia inicial?

**Manteria:** o ciclo Spec-Driven (SPEC validada → testes → implementação), a decisão de concentrar regras de negócio em uma service testável, e o uso de Docker Compose com volume nomeado para garantir persistência real e reprodutibilidade para o avaliador.

**Mudaria:** validar o documento de requisitos contra a fonte oficial **antes** de investir horas de implementação em cima dele. O maior retrabalho do dia (pergunta 9) só existiu porque um arquivo de requisitos incorreto foi aceito como verdade sem contraste — um passo de verificação de 5 minutos no início teria evitado praticamente todo o retrabalho do backend.

### 14. Aproximadamente quantas interações relevantes com IA foram necessárias?

Dezenas de interações ao longo do dia — desde o planejamento inicial (SPEC, PLAN, padrões de código) até o scaffold de backend/frontend, o ciclo TDD do módulo de incidentes, a containerização, a auditoria de segurança (vazamento de stack trace corrigido), a implementação do frontend, e por fim o realinhamento completo de escopo relatado nesta seção.

### 15. Quais ferramentas de IA foram utilizadas?

Apenas **Claude Code** (Anthropic) foi utilizado do início ao fim do desafio. Não houve necessidade de trocar de ferramenta.
