# Conventional Commits (Capere)

Mensagens de commit devem seguir a convenção **Conventional Commits**, para histórico legível, changelogs e integração com ferramentas de release.

## Formato

```
<type>[escopo opcional]: <descrição curta>

[corpo opcional]

[rodapé opcional, ex.: Breaking change, referência a ticket]
```

- Use **imperativo** na descrição (ex.: "adiciona", "corrige", não "adicionado" / "corrigido").
- A **descrição** não deve terminar com ponto final.
- **Escopo** é opcional e indica a área (ex.: `conciliacao-cartao`, `faturamento`).

## Types

O **type** indica a natureza da alteração:

| Type | Uso |
|------|-----|
| **test** | Criação ou alteração de código de teste (ex.: testes unitários). |
| **feat** | Nova funcionalidade (serviço, endpoint, fluxo de negócio, etc.). |
| **refactor** | Refatoração que **não** altera o comportamento esperado das regras de negócio (ex.: ajustes pós code review, extração de classes). |
| **style** | Formatação ou estilo apenas (lint, indentação, espaços, remoção de comentários supérfluos), sem mudança de comportamento. |
| **fix** | Correção de bug ou comportamento incorreto em produção. |
| **chore** | Tarefas de manutenção do projeto que não mudam a aplicação em runtime nem testes (ex.: eslint, prettier, `.gitignore`). |
| **docs** | Apenas documentação (README, API, guias internos). |
| **build** | Build ou dependências (npm, composer, gulp, etc.). |
| **perf** | Melhoria mensurável de desempenho (queries, algoritmos, etc.). |
| **ci** | Configuração de integração contínua (Travis, Circle, pipelines, etc.). |
| **revert** | Reversão de um commit anterior (pode referenciar o hash no corpo). |

## Exemplos

```
feat(conciliacao-cartao): adiciona filtro por adquirente no extrato

fix(baixa): trata retorno Totvs quando documento já está baixado

refactor(conciliacao-cartao): extrai pareamento já baixados para repositório

docs: documenta conventional commits e arquitetura hexagonal
```

## Tamanho recomendado de commit

Para manter histórico legível e facilitar rollback/code review, prefira commits pequenos e coesos:

- Até **12 arquivos alterados** por commit.
- Até **400 linhas** no diff total (adições + remoções).
- Um commit deve representar **uma intenção principal** (não misturar `fix`, `refactor` e `docs` no mesmo commit, salvo exceções justificadas).

Quando ultrapassar esses limites, dividir em múltiplos commits por contexto funcional.

## Referências cruzadas

- Padrões de código PHP: [padroes-nomenclatura-php.md](padroes-nomenclatura-php.md)
- Arquitetura: [arquitetura-hexagonal.md](arquitetura-hexagonal.md)