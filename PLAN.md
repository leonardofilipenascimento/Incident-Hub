# PLAN.md

## 1. Entendimento do problema

O sistema tem como objetivo permitir o registro e acompanhamento
de incidentes, permitindo que o usuário visualize o estado atual,
acompanhe seu histórico e realize as operações previstas no desafio.




## 3. Decisões técnicas

### Backend

- Laravel
- API REST
- PHP
- Eloquent ORM
- PHPUnit/Pest para testes

### Frontend

- Next.js
- React
- TypeScript

### Banco

- MySQL

### Arquitetura

Frontend → API REST → Laravel → MySQL

incident-hub/
│
├── backend/
│   └── Laravel
│
├── frontend/
│   └── Next.js
│
├── START.md
├── PLAN.md
├── TODO.md
├── AI_LOG.md
├── README.md
└── FINAL_REPORT.md