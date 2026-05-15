# Backlog Operacional Executável por LLM

> Arquivo operacional complementar a [03-fullstack-executable-backlog.md](03-fullstack-executable-backlog.md)  
> Objetivo: permitir que qualquer LLM execute o backlog fullstack em ciclos pequenos, auditáveis e testáveis  
> Última atualização: 15/05/2026

---

## 1. Missão

Se você for um LLM executando trabalho neste repositório, sua missão é:

1. ler [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md), [02-backend-improvement-plan.md](02-backend-improvement-plan.md) e [03-fullstack-executable-backlog.md](03-fullstack-executable-backlog.md);
2. escolher a primeira slice não concluída do arquivo `03`;
3. executar apenas essa slice, ponta a ponta;
4. validar com testes/comandos;
5. atualizar o status do backlog.

---

## 2. Arquivos de Entrada Obrigatórios

- [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md)
- [02-backend-improvement-plan.md](02-backend-improvement-plan.md)
- [03-fullstack-executable-backlog.md](03-fullstack-executable-backlog.md)
- `package.json`
- `composer.json`
- `phpunit.xml`
- `playwright.config.js`
- `playwright.config.ts`

---

## 3. Regras Não Negociáveis

- Execute **uma slice por vez**.
- Não marque nada como concluído sem validação executável.
- Não faça refatoração grande antes de ter teste mínimo cobrindo o comportamento atual.
- Não use comandos diretos de `php`, `composer` ou `npm` no host; use sempre os scripts do projeto.
- Preserve a arquitetura backend do projeto: `Controller → Service Interface → Service → Repository Interface → Repository → Model`.
- Se tocar autenticação, autorização, payload compartilhado, rotas ou contrato de dados, valide backend e frontend.
- Se encontrar divergência entre código e documentação, atualize a documentação junto com a entrega.

---

## 4. Loop Operacional Padrão

### Etapa 1 — Seleção da slice

1. Abra [03-fullstack-executable-backlog.md](03-fullstack-executable-backlog.md).
2. Localize a primeira `SLICE-*` não marcada.
3. Não pule para outra slice sem justificativa documentada.

### Etapa 2 — Roteamento mínimo

1. Localize o arquivo controlador da mudança.
2. Localize o ponto que realmente computa ou controla o comportamento.
3. Localize o teste mais barato que pode falsificar a hipótese.
4. Só depois edite.

### Etapa 3 — Implementação

1. Faça a menor mudança backend necessária.
2. Faça a menor mudança frontend correspondente.
3. Adicione ou ajuste o menor conjunto de testes que feche a fatia.

### Etapa 4 — Validação

1. Rode primeiro o teste mais específico.
2. Depois rode a suíte da camada tocada.
3. Só então rode smoke de integração, quando a slice exigir.

### Etapa 5 — Fechamento

1. Atualize [03-fullstack-executable-backlog.md](03-fullstack-executable-backlog.md).
2. Atualize este arquivo se a ordem, os comandos ou os riscos tiverem mudado.
3. Atualize [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md) ou [02-backend-improvement-plan.md](02-backend-improvement-plan.md) se o estado real do código mudou.

---

## 5. Matriz de Comandos Oficiais

### Frontend

```bash
./scripts/npm.sh install
./scripts/npm.sh run build
./scripts/npm.sh run lint
./scripts/npm.sh run test:unit
./scripts/npm.sh run test:e2e
```

### Backend

```bash
./scripts/composer.sh install
./scripts/artisan.sh test
./scripts/artisan.sh test --testsuite=Unit
./scripts/artisan.sh test --testsuite=Feature
./scripts/php-cs-fixer.sh fix
```

### Ambiente

```bash
./start.sh dev
./start.sh status
./start.sh logs
```

---

## 6. Ordem Executável das Tasks

### TASK-00 — Preparar a base de testes fullstack
- [ ] Referência: `SLICE-0.1` e `SLICE-0.2`.
- Objetivo: deixar frontend e backend com base previsível de testes antes de mudanças funcionais maiores.
- Passos:
1. Consolidar Playwright em uma configuração única.
2. Adicionar `Vitest + @vue/test-utils + jsdom` e scripts de teste no `package.json`.
3. Ativar ambiente previsível de testes backend (`sqlite` em memória ou `.env.testing`).
4. Criar factories mínimas do domínio principal.
5. Adicionar uma dupla de testes iniciais: 1 unit frontend e 1 unit backend.
- Validação mínima:
  - `./scripts/npm.sh run test:unit`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:e2e`
- Saída esperada:
  - scripts de teste documentados;
  - configuração única de Playwright;
  - `vitest.config.js` criado;
  - base de factories do domínio iniciada.

### TASK-01 — Fechar bugs críticos e baseline de segurança
- [ ] Referência: `SLICE-1.1`, `SLICE-1.2` e `SLICE-1.3`.
- Objetivo: remover erros críticos e garantir o mínimo seguro de autenticação/autorização antes de expandir features.
- Passos:
1. Corrigir bugs críticos do backend listados no arquivo `02`.
2. Corrigir o bug crítico frontend ainda pendente em `PrepaidCard/Index.vue`.
3. Proteger rotas com `auth`/`verified`.
4. Reduzir payload compartilhado do usuário no Inertia.
5. Adicionar testes de guest redirect e ownership/IDOR.
6. Garantir smoke E2E de login/dashboard.
- Validação mínima:
  - `./scripts/artisan.sh test --testsuite=Feature`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

### TASK-02 — Consolidar contratos compartilhados
- [ ] Referência: `SLICE-2.1`, `SLICE-2.2` e `SLICE-2.3`.
- Objetivo: alinhar validação, tags, descrições, people e share users antes das slices centrais.
- Passos:
1. Introduzir Form Requests e começar a remover casting manual.
2. Alinhar `useForm`, `useTagSearch`, `useDescriptionSearch` e encode de URLs.
3. Migrar `PeopleController` para a arquitetura padrão.
4. Consolidar carregamento de share users.
5. Adicionar testes unitários e de feature/component para esses contratos.
- Validação mínima:
  - `./scripts/artisan.sh test --testsuite=Feature`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:unit`

### TASK-03 — Fechar a slice Budget
- [ ] Referência: `SLICE-3.1`, `SLICE-3.2` e `SLICE-3.3`.
- Objetivo: estabilizar o domínio principal antes de atacar refatorações pesadas.
- Passos:
1. Fechar create/edit/clone de Budget com Requests/DTOs iniciais.
2. Estabilizar `BudgetShowData` e o contrato da tela `Budget/Show.vue`.
3. Fechar subfluxos de expenses, incomes e provisions.
4. Cobrir com feature tests, unit tests, component tests e smoke E2E.
- Validação mínima:
  - `./scripts/artisan.sh test --testsuite=Feature`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

### TASK-04 — Fechar a slice Credit Card/Invoice
- [ ] Referência: `SLICE-4.1` e `SLICE-4.2`.
- Objetivo: estabilizar o fluxo de cartão de crédito e invoice expense antes da componentização.
- Passos:
1. Fechar CRUD de cartão e invoices.
2. Cobrir `InvoiceExpense` nos fluxos de create, update, delete, portions e importação.
3. Proteger o comportamento atual do componente grande com testes antes de quebrá-lo.
- Validação mínima:
  - `./scripts/artisan.sh test --testsuite=Feature`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

### TASK-05 — Fechar a slice Prepaid Card/Extract
- [ ] Referência: `SLICE-5.1` e `SLICE-5.2`.
- Objetivo: manter paridade de comportamento entre cartão de crédito e cartão pré-pago.
- Passos:
1. Fechar CRUD de prepaid card e extracts.
2. Cobrir `ExtractExpense` antes da refatoração estrutural.
3. Garantir equivalência de validação e smoke E2E em relação ao fluxo de invoice.
- Validação mínima:
  - `./scripts/artisan.sh test --testsuite=Feature`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

### TASK-06 — Fechar domínios de suporte e modelos
- [ ] Referência: `SLICE-6.1` e `SLICE-6.2`.
- Objetivo: limpar supporting CRUDs e modelagem do domínio sem quebrar slices já estabilizadas.
- Passos:
1. Fechar `FixExpense`, `Provision` e `Financing` com DI, validação e testes.
2. Adicionar casts e relacionamentos corretos nos models principais.
3. Ajustar payloads consumidos pelo frontend se a serialização mudar.
- Validação mínima:
  - `./scripts/artisan.sh test`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

### TASK-07 — Refatorar com rede de segurança
- [ ] Referência: `SLICE-7.1`.
- Objetivo: quebrar monólitos apenas depois da cobertura mínima estar estável.
- Passos:
1. Dividir `InvoiceExpense.vue`.
2. Dividir `BudgetExpense.vue`.
3. Dividir `ExtractExpense.vue`.
4. Extrair componentes e helpers backend planejados em `BudgetShowData` e `BudgetService`.
- Validação mínima:
  - `./scripts/npm.sh run test:unit`
  - `./scripts/artisan.sh test --testsuite=Unit`
  - smoke E2E das telas afetadas.

### TASK-08 — Otimizar performance e fechar DX/acessibilidade
- [ ] Referência: `SLICE-7.2` e `SLICE-7.3`.
- Objetivo: encerrar o programa com performance, acessibilidade e governança de qualidade.
- Passos:
1. Aplicar otimizações de queries, deleções e recálculos no backend.
2. Aplicar tree-shaking, lazy loading e revisão de dependências no frontend.
3. Fechar lint, Husky, acessibilidade e padronização remanescente.
- Validação mínima:
  - `./scripts/artisan.sh test`
  - `./scripts/npm.sh run lint`
  - `./scripts/npm.sh run test:unit`
  - `./scripts/npm.sh run test:e2e`

---

## 7. Template de Atualização de Status

Sempre que concluir uma task, registre:

```text
Task concluída: TASK-XX
Slices fechadas: SLICE-X.X, SLICE-X.X
Arquivos principais alterados: ...
Testes executados: ...
Pendências restantes: ...
```

---

## 8. Critério de Parada

Pare somente quando uma destas condições ocorrer:

1. a task selecionada estiver totalmente concluída e validada;
2. houver bloqueio real de ambiente, dependência ou requisito ausente;
3. a documentação exigir atualização antes de seguir.

Se parar por bloqueio, registre o bloqueio com causa objetiva, impacto e próximo passo recomendado.
