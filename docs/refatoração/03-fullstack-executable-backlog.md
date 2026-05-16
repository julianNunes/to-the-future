# Backlog Executável Integrado — Frontend + Backend

> Documento derivado de: [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md) e [02-backend-improvement-plan.md](02-backend-improvement-plan.md)  
> Objetivo: fazer frontend, backend e testes caminharem juntos em fatias verticais de entrega  
> Última atualização: 15/05/2026

---

## 1. Objetivo

Este backlog transforma os planos de melhoria do frontend e do backend em uma sequência única de execução. A lógica aqui não é evoluir por camada isolada, mas por **slice funcional completa**, sempre fechando:

1. contrato backend;
2. comportamento frontend;
3. cobertura automatizada;
4. validação executável.

---

## 2. Como Usar Este Arquivo

1. Execute as ondas na ordem em que aparecem.
2. Dentro de cada onda, pegue a primeira slice não marcada.
3. Só marque a slice como concluída quando backend, frontend e testes daquela fatia estiverem fechados.
4. Se uma slice exigir refatoração maior, quebre em subtarefas no arquivo [04-llm-executable-backlog.md](04-llm-executable-backlog.md), mas mantenha o status mestre aqui.
5. Se o estado real do código divergir de [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md) ou [02-backend-improvement-plan.md](02-backend-improvement-plan.md), atualize os planos antes de avançar.

---

## 3. Regras de Sincronização

- Nenhuma mudança estrutural de frontend deve ser considerada concluída sem validar o contrato backend correspondente.
- Nenhuma mudança de regra de negócio no backend deve ser considerada concluída sem ao menos um teste automatizado no ponto de maior risco.
- Todo bug crítico corrigido deve ganhar um teste de regressão no lado mais barato de manter.
- Rotas, payloads, autenticação, autorização e dados compartilhados por Inertia sempre exigem validação de backend e validação visual/comportamental no frontend.
- Refatorações grandes só entram depois que a slice funcional equivalente estiver coberta por testes mínimos.

---

## 4. Critério de Conclusão de Uma Slice

Uma slice só pode ser marcada como concluída quando atender todos os critérios abaixo:

- código backend implementado ou ajustado;
- código frontend implementado ou ajustado, se a slice tocar interface;
- ao menos um teste automatizado criado ou atualizado;
- comandos de validação executados com sucesso;
- status atualizado neste arquivo e no arquivo [04-llm-executable-backlog.md](04-llm-executable-backlog.md), se aplicável.

---

## 5. Mapa de Ondas

| Onda | Fonte principal no frontend | Fonte principal no backend | Resultado esperado |
|------|-----------------------------|----------------------------|-------------------|
| 0 | Fase 9, Fase 11 | Fase 10 | Base de testes e execução previsível |
| 1 | Bugs críticos, Fase 1 | Bugs críticos, Fase 1, Fase 2 | Correções urgentes e segurança mínima |
| 2 | Fase 2, Fase 3, Fase 7 | Fase 4, Fase 7, Fase 9 | Contratos compartilhados e base de CRUD |
| 3 | Fase 3, Fase 4, Fase 5 | Fase 4, Fase 5, Fase 8 | Slice Budget completa |
| 4 | Fase 3, Fase 5 | Fase 5, Fase 8 | Slice Credit Card/Invoice completa |
| 5 | Fase 3, Fase 5 | Fase 5, Fase 8 | Slice Prepaid Card/Extract completa |
| 6 | Fase 1, Fase 2, Fase 5 | Fase 3, Fase 7, Fase 9 | Domínios compartilhados, supporting CRUDs e alinhamento |
| 7 | Fase 6, Fase 7, Fase 8 | Fase 6, Fase 8, Fase 9 | Refatoração, performance, DX e acessibilidade com rede de segurança |

---

## 6. Checklist Mestre

### Onda 0 — Fundação de Execução e Testes

#### SLICE-0.1 — Infra de testes fullstack
- [x] Entregar base única de testes com Playwright consolidado, Vitest configurado e scripts oficiais no `package.json`.
- Frontend: consolidar `playwright.config.js` como configuração ativa, remover ambiguidade com `playwright.config.ts`, adicionar `Vitest + @vue/test-utils + jsdom`.
- Backend: garantir execução previsível de `phpunit` via `./scripts/artisan.sh test`.
- Testes mínimos: 1 unit frontend, 1 unit backend, 1 smoke E2E.
- Validação mínima: `./scripts/npm.sh run test:unit`, `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/npm.sh run test:e2e`.
- Status em 15/05/2026: concluída com `vitest.config.js`, scripts oficiais de teste, Playwright unificado em `playwright.config.js`, smoke de login verde e runtime E2E ajustado para Alpine.

#### SLICE-0.2 — Ambiente de dados para teste
- [x] Preparar base mínima de factories, fixtures e massa de dados reutilizável para Budget, Credit Card, Invoice, Prepaid Card e Extract.
- Frontend: permitir smoke tests sem credenciais hardcoded.
- Backend: adicionar factories e seeds mínimos do domínio principal.
- Testes mínimos: 1 feature test de autenticação/navegação + 1 smoke E2E com massa previsível.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:e2e`.
- Status em 15/05/2026: concluída com factories mínimas do domínio principal, `E2ESmokeSeeder`, helper compartilhado de auth para Playwright e suíte feature alinhada ao app real.

### Onda 1 — Bugs Críticos e Segurança

#### SLICE-1.1 — Correções críticas de orçamento e rotas
- [x] Corrigir bugs críticos do backend e fechar a pendência crítica restante do frontend.
- Backend: corrigir `BudgetService::clone()`, variável `$installments`, rotas duplicadas, `AppRepository::delete()` e binding de `BudgetExpenseTagOptionRepositoryInterface`.
- Frontend: corrigir o `<Head title>` pendente em `PrepaidCard/Index.vue` e revisar os bugs críticos já marcados no checklist.
- Testes mínimos: 1 unit/backend para clone ou service afetado, 1 feature test para rota duplicada removida ou página Budget Show, 1 verificação frontend da tela afetada.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`.
- Status em 15/05/2026: concluída com correções em `BudgetService::clone()`, `BudgetShowData`, rotas duplicadas, `AppRepository::delete()`, binding de `BudgetExpenseTagOptionRepositoryInterface` e `<Head title>` de `PrepaidCard/Index.vue`, além de regressions backend dedicados.

#### SLICE-1.2 — Auth baseline, payload compartilhado e navegação autenticada
- [x] Fechar o mínimo de segurança para as rotas de domínio e alinhar o frontend ao payload autenticado.
- Backend: aplicar `auth` nas rotas de domínio, reduzir payload de `HandleInertiaRequests`, ajustar tratamento de exceções quando necessário e manter `verified` adiado até existir UX real de verificação por e-mail.
- Frontend: validar layout autenticado, menu, breadcrumbs e consumo do payload `auth.user` reduzido.
- Testes mínimos: feature tests para guest redirect e acesso autenticado; Playwright smoke de login e dashboard.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:e2e`.
- Status em 15/05/2026: concluída com rotas sensíveis protegidas por `auth`, payload `auth.user` reduzido para `id`, `name` e `email`, correção do `Handler` para preservar redirects/403 do framework e smoke E2E de login/dashboard verde.

#### SLICE-1.3 — Ownership e IDOR
- [x] Garantir que recursos do domínio não possam ser acessados por usuários errados e refletir isso nas telas.
- Backend: adicionar checagens de ownership na camada `Service/Helper` para Budget e recursos adjacentes, além de escopo por usuário nos searches críticos.
- Frontend: manter a navegação autenticada íntegra e aceitar o deny path padrão por HTTP `403` nesta baseline; UX dedicada para acesso negado permanece como refinamento futuro.
- Testes mínimos: feature tests de IDOR para Budget; smoke frontend garantindo que a navegação normal siga funcionando.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:e2e`.
- Status em 15/05/2026: concluída com guards compartilhados de ownership, cobertura de acesso cruzado para Budget, Goal, Credit Card, Invoice, Prepaid Card, Extract, Tag e side doors de expenses/search, além de smoke E2E sem regressão.

### Onda 2 — Contratos Compartilhados e Base de CRUD

#### SLICE-2.1 — Requests, casting e normalização de entrada
- [x] Introduzir a base de validação formal no backend e alinhar tipagem/transformações no frontend.
- Backend: iniciar Form Requests nos CRUDs mais usados e reduzir casting manual nos controllers.
- Frontend: avançar no uso de `useForm`, regras compartilhadas e normalização de payload.
- Testes mínimos: feature tests de validação backend + component/unit tests dos formulários/composables afetados.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`.
- Status em 15/05/2026: concluída no piloto e rollout de `Tag`, `FixExpense`, `Provision`, `Financing`, `CreditCard` e `PrepaidCard`, com Form Requests, normalização de entrada, `useForm` nas páginas alvo, regressions backend dedicados, component tests Vue e suites integradas (`Feature`, `test:unit` e smoke E2E existente) verdes.

#### SLICE-2.2 — Domínios compartilhados de tags e busca
- [x] Consolidar a busca de tags/descrições, o contrato de payload e a cobertura mínima dos fluxos de autocomplete.
- Backend: validar endpoints de busca, payloads e comportamento de tags.
- Frontend: concluir `useTagSearch`, `useDescriptionSearch`, encode de URLs e estados de erro.
- Testes mínimos: unit tests dos composables + feature tests dos endpoints de busca.
- Validação mínima: `./scripts/npm.sh run test:unit`, `./scripts/artisan.sh test --testsuite=Feature`.
- Status em 15/05/2026: concluída com `useTagSearch()` e `useDescriptionSearch()` consolidados, encode de URLs via `encodeURIComponent`, limpeza de loading manual nos consumidores principais, testes unitários dedicados dos composables e coverage backend de ownership no search de tags.

#### SLICE-2.3 — People, share users e contratos auxiliares
- [x] Alinhar entidades auxiliares usadas em múltiplos módulos antes das slices maiores.
- Backend: migrar `PeopleController` para a arquitetura padrão e consolidar carregamento de share users.
- Frontend: alinhar selects e fluxos que dependem de `shareUsers`.
- Testes mínimos: unit/backend para loader compartilhado + feature/controller tests + component tests dos selects dependentes.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`.
- Status em 15/05/2026: concluída com `PeopleController` migrado para `Service + Repository`, páginas Inertia `People/Index`, `People/Create` e `People/Edit` entregues, helper `ShareUserOptions` extraído e integrado aos serviços consumidores, além de feature tests do fluxo de `People` e regressão integrada verde.

### Onda 3 — Slice Budget

#### SLICE-3.1 — Budget create/edit/clone
- [ ] Fechar a fatia de criação, edição e clone de budget ponta a ponta.
- Backend: aplicar validação formal, DTOs iniciais e correções do fluxo `createComplete()`/`clone()`.
- Frontend: concluir `useForm` e ajustes de `Budget/Index.vue`, incluindo datas de semanas.
- Testes mínimos: feature tests de CRUD e clone; component test de `Budget/Index.vue`; smoke E2E do fluxo principal.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

#### SLICE-3.2 — Budget show, resumo e dados agregados
- [ ] Fechar a tela de visualização do budget com contrato estável e dados confiáveis.
- Backend: estabilizar `BudgetShowData`, remover variáveis inválidas e preparar o terreno para futuras extrações.
- Frontend: consolidar `Budget/Show.vue`, cálculo de semanas, tags e painéis dependentes.
- Testes mínimos: unit/backend para helper ou builder afetado + component test do `Budget/Show.vue` + smoke E2E da página.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

#### SLICE-3.3 — Expenses, incomes e provisions do budget
- [ ] Fechar os subfluxos de Budget Expense, Budget Income e Budget Provision com contrato, formulário e regressão mínima.
- Backend: Requests/DTOs parciais, correção de loops críticos e testes dos services principais.
- Frontend: avançar `useForm`, composables reutilizados e correção das rotas de delete/delete-all-portions.
- Testes mínimos: unit tests de composables e services; feature tests dos endpoints; component tests dos formulários.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`.

### Onda 4 — Slice Credit Card e Invoice

#### SLICE-4.1 — Credit Card e invoices
- [ ] Fechar o fluxo de cartão de crédito e listagem de faturas ponta a ponta.
- Backend: validação, factories e cobertura dos CRUDs de cartão e invoice.
- Frontend: concluir `useForm` em `CreditCard/Index.vue` e `CreditCardInvoice/Index.vue`.
- Testes mínimos: feature tests de CRUD, component tests de index pages e Playwright smoke de navegação.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

#### SLICE-4.2 — InvoiceExpense, portions e importação
- [ ] Fechar a fatia mais complexa de invoice expense antes de iniciar refatoração estrutural.
- Backend: cobrir parcelamento, importação e recálculo com tests.
- Frontend: proteger comportamento atual de `InvoiceExpense.vue` com testes antes da componentização.
- Testes mínimos: unit/backend dos services críticos + component tests do componente atual + E2E do fluxo principal.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

### Onda 5 — Slice Prepaid Card e Extract

#### SLICE-5.1 — Prepaid Card e extracts
- [ ] Fechar o fluxo de cartão pré-pago e listagem de extratos ponta a ponta.
- Backend: validar CRUDs, factories e contratos do domínio.
- Frontend: corrigir títulos restantes, concluir `useForm` onde necessário e manter paridade com credit card.
- Testes mínimos: feature tests de CRUD, component tests das páginas e Playwright smoke de navegação.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

#### SLICE-5.2 — ExtractExpense, importação e compartilhamento
- [ ] Fechar o fluxo de despesas de extrato e alinhá-lo ao padrão de invoice expense.
- Backend: cobrir service, importação e parcelamento equivalente, se houver.
- Frontend: proteger `ExtractExpense.vue` com testes antes de quebrar em subcomponentes.
- Testes mínimos: unit/backend + component tests do fluxo principal + E2E do detalhe do extrato.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

### Onda 6 — Domínios Compartilhados e Supporting CRUDs

#### SLICE-6.1 — FixExpense, Provision e Financing
- [ ] Fechar os supporting CRUDs que alimentam budget e planejamento financeiro.
- Backend: remover Facades onde necessário, consolidar DI, validação e cobertura mínima.
- Frontend: concluir `useForm`, padronização de props e testes de formulários/listas.
- Testes mínimos: feature tests dos CRUDs, unit/backend dos services e component tests das páginas.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:unit`.

#### SLICE-6.2 — Models, casts e relacionamentos
- [ ] Atualizar models centrais sem quebrar a UI consumidora.
- Backend: adicionar casts, corrigir relações `BelongsTo`, melhorar `User`, revisar naming e `fillable`.
- Frontend: validar formatos consumidos pelas telas após mudança de casts e payloads.
- Testes mínimos: unit/integration tests de model/serialization + smoke das páginas mais sensíveis.
- Validação mínima: `./scripts/artisan.sh test --testsuite=Unit`, `./scripts/artisan.sh test --testsuite=Feature`, `./scripts/npm.sh run test:e2e`.

### Onda 7 — Refatoração, Performance, DX e Acessibilidade

#### SLICE-7.1 — Componentização protegida por testes
- [ ] Dividir `InvoiceExpense.vue`, `BudgetExpense.vue` e `ExtractExpense.vue` somente depois de cobrir o comportamento atual.
- Backend: extrair serviços/helpers grandes apenas com testes cobrindo o comportamento já estabilizado.
- Frontend: criar subcomponentes (`FormDialog`, `DataTableToolbar`, `WeekDateInputs`, imports) preservando comportamento.
- Testes mínimos: component tests dos novos subcomponentes + unit/backend das extrações correspondentes.
- Validação mínima: `./scripts/npm.sh run test:unit`, `./scripts/artisan.sh test --testsuite=Unit`.

#### SLICE-7.2 — Performance com rede de segurança
- [ ] Aplicar melhorias de performance e N+1 somente com regressão mínima já ativa.
- Backend: reduzir `recalculate()` em loop, otimizar deleções e `saveTagsToModel()`, ativar `preventLazyLoading()`.
- Frontend: tree-shaking do Vuetify, lazy load, eventual troca de `moment`, revisão de source maps e target.
- Testes mínimos: suites existentes verdes + smoke E2E dos fluxos críticos após otimizações.
- Validação mínima: `./scripts/artisan.sh test`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

#### SLICE-7.3 — DX, lint e acessibilidade
- [ ] Fechar a última camada de qualidade transversal.
- Frontend: lint strategy, Husky/lint-staged, ARIA labels, skip link, correção de `tfoot`, logout acessível.
- Backend: return types, imports mortos, naming final, docblocks e padronização.
- Testes mínimos: component tests dos pontos acessíveis e validações finais das suites.
- Validação mínima: `./scripts/npm.sh run lint`, `./scripts/artisan.sh test`, `./scripts/npm.sh run test:unit`, `./scripts/npm.sh run test:e2e`.

---

## 7. Definição de Encerramento do Programa

O programa inteiro só pode ser considerado concluído quando:

- todas as slices deste arquivo estiverem marcadas;
- os checklists de [01-frontend-improvement-plan.md](01-frontend-improvement-plan.md) e [02-backend-improvement-plan.md](02-backend-improvement-plan.md) estiverem atualizados com o estado real do código;
- existir suíte mínima estável de testes unitários/componentes frontend, unitários/features backend e smoke E2E;
- os componentes e services mais críticos já estiverem protegidos por testes antes das refatorações grandes.
