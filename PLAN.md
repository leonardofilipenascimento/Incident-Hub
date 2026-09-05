# PLAN.md

## Entendimento

O **Incident Hub** resolve um problema de coordenação: hoje incidentes operacionais são comunicados por mensagens informais, o que dificulta saber quais estão abertos, quais são mais graves, quem é responsável, o que já foi feito e quais já foram resolvidos. A solução é uma aplicação web simples de registro e acompanhamento de incidentes, com um fluxo de status auditável (histórico) e uma visão resumida (dashboard) para dar visibilidade rápida ao estado atual das operações. É um problema de CRUD + máquina de estados simples, não de escala ou concorrência — o foco é confiabilidade e clareza, não sofisticação.

## Escopo

### Obrigatório (Challenge Pack)
- Cadastro de incidente: título, descrição, severidade, responsável (owner); status sempre nasce `Open`.
- Listagem com filtro por status e por severidade.
- Tela de detalhe com todos os campos do incidente.
- Alteração de status, com a regra: incidente `Critical` não pode ir direto de `Open` para `Resolved` (precisa passar por `In Progress`), com feedback compreensível ao usuário em caso de transição inválida.
- Histórico de alterações de status (status anterior, novo status, timestamp), persistido e associado ao incidente.
- Dashboard com: quantidade de incidentes `Open`, quantidade de `Critical` não resolvidos, quantidade de `Resolved`.
- Persistência real (sobrevive a refresh/restart).
- Dados de exemplo pré-carregados (os 3 incidentes exigidos na Seção 11 do Challenge Pack).
- Testes automatizados para a regra de negócio crítica (trava do `Critical`).
- README com instruções de execução reproduzíveis.

### Desejável (se sobrar tempo)
- Containerização (Docker) para facilitar reprodução e persistência entre reinícios.
- Interface com filtros combináveis e feedback visual de erro claro (não só um alert genérico).

### Fora de escopo (deliberadamente não implementado)
- Autenticação, permissões, recuperação de senha, múltiplos tenants (explicitamente dispensado pelo Challenge Pack, Seção 2).
- Edição de campos do incidente após criado (título, descrição, severidade) — o Challenge Pack só pede alteração de status.
- Exclusão de incidentes — não pedido; conflitaria com o objetivo de manter histórico auditável.
- Paginação, busca textual, ordenação configurável — não exigidos; a listagem ordenada por data de criação decrescente já atende ao requisito.

## Decisões técnicas

### Stack
- **Backend:** Laravel (PHP) — API REST simples, produtivo para CRUD + validação + migrations em pouco tempo.
- **Frontend:** Next.js (React + TypeScript) — SPA leve, suficiente para as 3 telas exigidas (lista, detalhe/criação, dashboard).
- **Banco:** MySQL — relacional, adequado para o modelo simples (incidente 1:N histórico) e para a exigência de persistência real.

### Persistência
Duas tabelas: `incidents` (dados centrais) e `incident_status_histories` (uma linha por transição de status, imutável). Persistência real garantida via volume Docker nomeado (dados sobrevivem a restart de containers).

### Estrutura geral
Monorepo com `backend/` (Laravel) e `frontend/` (Next.js) desacoplados, comunicando via API REST, orquestrados por `docker-compose.yml` (MySQL + backend + frontend). Ver `README.md` para instruções de execução.

### Estratégia de testes
Testes automatizados (PHPUnit) cobrindo os contratos de API e, com prioridade máxima, a regra de negócio crítica (`Critical` não pode pular direto para `Resolved`), conforme exigido pela Seção 12 do Challenge Pack.

## Decomposição

1. Modelagem do domínio (migrations, model `Incident`, model `IncidentStatusHistory`).
2. Endpoint de criação de incidente + validação.
3. Endpoint de listagem com filtros por status/severidade.
4. Endpoint de detalhe (incidente + histórico).
5. Endpoint de transição de status + regra de negócio crítica + registro automático de histórico.
6. Endpoint de dashboard (contagens).
7. Seed com os 3 incidentes de exemplo exigidos.
8. Testes automatizados das regras de negócio e contratos.
9. Frontend: listagem + filtros, criação, detalhe + histórico, ação de transição de status, dashboard.
10. Containerização (Docker) para persistência e reprodutibilidade.
11. Documentação final (README, AI_LOG, FINAL_REPORT).

## Critérios de aceite

- Cada requisito funcional (Seções 3-9 do Challenge Pack) tem um teste automatizado ou uma verificação manual documentada que comprova o comportamento esperado.
- A regra do incidente `Critical` tem teste automatizado cobrindo tanto o caso bloqueado (`Open` → `Resolved`) quanto o caminho permitido (`Open` → `In Progress` → `Resolved`).
- Os dados sobrevivem a um restart da aplicação/containers (validado manualmente).
- Uma pessoa sem contexto prévio consegue rodar a aplicação do zero seguindo só o `README.md`.
- O seed contém, no mínimo, os 3 incidentes exigidos com os dados exatos especificados.

## Riscos

- **Tempo limitado** (code freeze às 17:40): mitigado priorizando rigorosamente os requisitos obrigatórios antes de qualquer extra, e revisando o Challenge Pack integralmente antes de aprofundar a implementação.
- **Interpretação equivocada de um requisito**: mitigado tratando o Challenge Pack como fonte única de verdade e revalidando a implementação contra ele quando surgir dúvida ou nova informação (Seção 21 do Challenge Pack prevê mudanças/novas informações durante o hackathon).
- **Escopo inflado por sugestões da IA**: a IA pode propor campos/endpoints "razoáveis" que não foram pedidos (ex.: campos extras, regras adicionais). Mitigado revisando toda sugestão contra o texto literal do Challenge Pack antes de aceitar.

## Estratégia de IA

A IA (Claude Code) é usada para acelerar a implementação dentro da metodologia Spec-Driven: gerar a spec técnica a partir do Challenge Pack, escrever migrations/models/controllers/testes seguindo essa spec, e validar o resultado (rodando testes e testando manualmente os endpoints/telas) antes de avançar. Toda decisão relevante — especialmente desvios, erros encontrados e sugestões da IA que extrapolaram o pedido — é registrada em `AI_LOG.md` para rastreabilidade.
