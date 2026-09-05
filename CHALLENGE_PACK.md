#  AI Engineering Hackathon — Challenge Pack

##  1. Visão Geral do Desafio

### Nome do Desafio: **Incident Hub — Sistema Inteligente de Gestão de Incidentes de TI**

A infraestrutura de TI moderna gera milhares de alertas diariamente. Em momentos de crise, equipes de engenharia enfrentam instabilidades críticas e perdem tempo precioso tentando organizar a comunicação, entender a gravidade e definir o fluxo de resolução.

O seu objetivo é construir o **Incident Hub**: uma plataforma web para registro, acompanhamento, categorização e gerenciamento do ciclo de vida de incidentes operacionais, garantindo alta confiabilidade, histórico auditável e regras de transição de estado estritas.

---

## 2. Requisitos Funcionais (RF)

### **RF01 — Gestão e Registro de Incidentes**
* O sistema deve permitir a criação de novos incidentes contendo obrigatoriamente:
  * **Título** (*string*, min: 5, max: 150)
  * **Descrição** (*text*, min: 10)
  * **Severidade** (*enum*: `Low`, `Medium`, `High`, `Critical`)
  * **Status inicial** (*enum*: deve nascer sempre como `Open`)
  * **Sistemas Afetados** (*array de strings*, mínimo 1 sistema)

### **RF02 — Máquina de Estados e Transição de Status**
O ciclo de vida de um incidente possui os status: `Open`, `In Progress`, `Resolved`, `Closed`.
* **Regras Estritas de Transição:**
  1. Qualquer incidente pode ir de `Open` para `In Progress`.
  2. Incidentes de severidade `Critical` **NÃO podem** ser transacionados diretamente de `Open` para `Resolved` (devem obrigatoriamente passar por `In Progress`).
  3. Incidentes só podem ser marcados como `Closed` se já estiverem em `Resolved`.
  4. Um incidente em estado `Closed` **não pode** sofrer alterações adicionais de status ou conteúdo.

### **RF03 — Histórico e Auditoria de Ações**
* Cada alteração de status ou severidade deve registrar automaticamente um histórico (*timeline*) imutável contendo:
  * Status anterior e Novo status
  * Data e hora da alteração (`timestamp`)
  * Justificativa/Comentário do engenheiro responsável (obrigatório em mudanças para `Resolved` ou `Closed`).

### **RF04 — Listagem e Filtros Operacionais**
* O sistema deve listar incidentes ordenados por data de criação (mais recentes primeiro).
* Deve permitir filtragem dinâmica por:
  * **Severidade** (`Low`, `Medium`, `High`, `Critical`)
  * **Status** (`Open`, `In Progress`, `Resolved`, `Closed`)
  * **Busca textual** (aplicada ao título e descrição)

---

## 🛠️ 3. Requisitos Técnicos (RT)

* **RT01 — Arquitetura Decoplada:** Backend via API RESTful (Laravel) e Frontend SPA/SSR (Next.js/React).
* **RT02 — Banco de Dados Relacional:** MySQL com migrations e seeders funcionais (mínimo de 5 incidentes pré-carregados para testes).
* **RT03 — Validações Estritas:** Todas as entradas e regras de negócio devem ser validadas e protegidas primariamente no backend com retorno estruturado de erros (`422 Unprocessable Entity`).
* **RT04 — Respostas da API:**
  * Sucesso na criação: `201 Created`
  * Sucesso em requisições de listagem/leitura: `200 OK`
  * Erro de validação/regra de negócio: `422 Unprocessable Entity` com payload indicando o campo/motivo.
  * Recurso não encontrado: `404 Not Found`

---

##  4. Critérios de Aceite e Qualidade

1. **Testabilidade:** As regras de transição de status (especialmente a trava do status `Critical`) devem possuir testes automatizados no backend (PHPUnit).
2. **Conformidade com a SPEC:** Todos os contratos de API consumidos pelo Next.js devem respeitar fielmente os esquemas especificados no diretório `specs/`.
3. **Persistência:** O sistema deve manter a consistência dos dados após a reinicialização dos containers/servidores.
