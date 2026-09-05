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
