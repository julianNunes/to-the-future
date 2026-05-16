# Plano de Melhorias Backend — Laravel 10 (PHP)

> Documento gerado em: 20/02/2026  
> Escopo: Análise completa do backend (Controllers, Services, Repositories, Models, Routes, Middleware, Providers)  
> Total de arquivos analisados: ~90+ arquivos PHP

---

## Índice

1. [Resumo Executivo](#1-resumo-executivo)
2. [Bugs Críticos (Correção Imediata)](#2-bugs-críticos-correção-imediata)
3. [Fase 1 — Segurança e Autorização](#3-fase-1--segurança-e-autorização)
4. [Fase 2 — Integridade de Dados (Transactions e Exception Handling)](#4-fase-2--integridade-de-dados-transactions-e-exception-handling)
5. [Fase 3 — Models (Casts, Relationships, Naming)](#5-fase-3--models-casts-relationships-naming)
6. [Fase 4 — Form Requests e Validação](#6-fase-4--form-requests-e-validação)
7. [Fase 5 — DTOs e Redução de Parâmetros](#7-fase-5--dtos-e-redução-de-parâmetros)
8. [Fase 6 — Refatoração de God Classes](#8-fase-6--refatoração-de-god-classes)
9. [Fase 7 — Eliminação de Duplicação nos Services](#9-fase-7--eliminação-de-duplicação-nos-services)
10. [Fase 8 — Performance (N+1, Queries)](#10-fase-8--performance-n1-queries)
11. [Fase 9 — Padronização e Qualidade de Código](#11-fase-9--padronização-e-qualidade-de-código)
12. [Fase 10 — Testes Automatizados](#12-fase-10--testes-automatizados)
13. [Resumo de Impacto por Fase](#13-resumo-de-impacto-por-fase)
14. [Apêndice A — Inventário de Problemas por Arquivo](#14-apêndice-a--inventário-de-problemas-por-arquivo)
15. [Apêndice B — Mapa de Duplicação no Backend](#15-apêndice-b--mapa-de-duplicação-no-backend)

---

## 1. Resumo Executivo

A análise identificou **75+ pontos de melhoria** no backend, incluindo **vulnerabilidades de segurança**, **bugs de dados** e **problemas arquiteturais**. Os mais críticos:

| Categoria | Severidade | Qtd |
|-----------|:----------:|:---:|
| Segurança (auth missing, mass assignment) | 🔴 Crítica | 4 |
| Bugs em código (params errados, variável undefined) | 🔴 Crítica | 5 |
| Integridade de dados (transactions, exceptions) | 🔴 Alta | 4 |
| Models (casts, relationships, naming) | 🟠 Alta | 8 |
| Validação (sem Form Requests) | 🟠 Média | 3 |
| God Classes e complexidade | 🟠 Média | 4 |
| Performance (N+1 queries) | 🟡 Média | 6 |
| Duplicação de código | 🟡 Média | 8 |
| Padronização e qualidade | 🟡 Baixa | 15+ |

---

## 2. Bugs Críticos (Correção Imediata)

### 2.1 BUG: Ordem de Parâmetros Errada — `BudgetService::clone()`

**Arquivo:** `app/Services/BudgetService.php` (~L362-L369)  
**Problema:** Ao clonar incomes, a chamada `budgetIncomeService->create()` passa os parâmetros na ordem errada:

```php
// Assinatura do BudgetIncomeService::create():
create(int $budgetId, string $description, string $date, float $value, ...)

// Chamada no clone() — ERRADA:
$this->budgetIncomeService->create(
    $income->description,   // ← String passada como int $budgetId
    Carbon::parse(...)...,   // ← Date passada como $description
    $income->value,          // ← Value passada como $date
    $income->remarks,        // ← Remarks passado como $value
    $new_budget->id,         // ← BudgetId passado na posição 5
    $income->tags
);
```

**Impacto:** Clone de budget com incomes pode salvar dados corrompidos ou causar erro.  
**Correção:** Reordenar para `create($new_budget->id, $income->description, Carbon::parse(...), $income->value, ...)`

### 2.2 BUG: Variável `$installments` Indefinida — `BudgetShowData`

**Arquivo:** `app/Helpers/Budget/BudgetShowData.php` (~L215)  
**Problema:** A variável `$installments` é referenciada no array de retorno mas nunca é definida. O código que a populava (L80-L92) foi comentado/removido.  
**Impacto:** `Undefined variable` warning/error ao exibir página do budget.  
**Correção:** Remover do retorno ou restaurar a lógica.

### 2.3 BUG: Rotas Duplicadas

**Arquivo:** `routes/web.php`
- `Route::delete('/credit-card/invoice/{id}', 'delete')` definida **duas vezes** (~L73 e L75)
- `Route::delete('/prepaid-card/extract/{id}', 'delete')` definida **duas vezes** (~L96 e L98)

**Impacto:** A segunda definição sobrescreve a primeira silenciosamente.  
**Correção:** Remover as duplicatas.

### 2.4 BUG: `AppRepository::delete()` com Argumento Inválido

**Arquivo:** `app/Repositories/AppRepository.php` (~L134)

```php
return $this->model->where('id', $id)->delete($id);
// delete() do query builder NÃO aceita argumentos
```

**Impacto:** Funciona "acidentalmente" porque o Eloquent Builder ignora o argumento, mas é código incorreto.  
**Correção:** `return $this->model->where('id', $id)->delete();`

### 2.5 BUG: `BudgetExpenseTagOptionRepository` Não Registrado

**Arquivo:** `app/Providers/RepositoryServiceProvider.php`  
**Problema:** A interface `BudgetExpenseTagOptionRepositoryInterface` não está registrada no provider, mas é usada por `BudgetExpenseTagOptionService`.  
**Impacto:** Possível erro de resolução em runtime.  
**Correção:** Adicionar o binding:
```php
$this->app->bind(BudgetExpenseTagOptionRepositoryInterface::class, BudgetExpenseTagOptionRepository::class);
```

---

## 3. Fase 1 — Segurança e Autorização

**Esforço:** ~4-6 horas | **Impacto:** Crítico | **Risco:** Baixo

### 3.1 VULNERABILIDADE: Rotas Sem Autenticação

**Problema:** As rotas de `web.php` para budget, credit card, financing, tag, provision, fix-expense, prepaid-card e dashboard **não possuem middleware `auth`**. Apenas `PeopleController` tem `auth` (via construtor).

**Todas essas rotas estão acessíveis sem login:**
```
GET  /dashboard
GET  /budget
POST /budget
PUT  /budget/{id}
GET  /credit-card
POST /credit-card
... (todas as ~50 rotas de domínio)
```

**Correção urgente — envolver todas as rotas em middleware `auth`:**

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::controller(BudgetController::class)->group(function () {
        // ... todas as rotas budget
    });
    
    // ... todas as outras rotas de domínio
});
```

### 3.2 VULNERABILIDADE: Sem Verificação de Propriedade (IDOR)

**Problema:** Nenhum controller/service verifica se o recurso pertence ao usuário autenticado. Qualquer usuário autenticado pode acessar/editar/deletar recursos de outros usuários via ID.

**Exemplo:** `GET /budget/show/123` — o ID 123 pode pertencer a outro usuário.

**Status em 15/05/2026:** baseline de mitigação entregue na camada `Service/Helper`, com `AuthorizationException` explícita para acesso cruzado, escopo por usuário nos searches críticos e cobertura por feature tests de guest redirect e ownership/IDOR. A adoção formal de Policies continua recomendada como evolução de longo prazo.

**Correção 1 — Scoping por usuário nos services:**
```php
// Em todos os services, adicionar verificação:
public function show(int $id): Budget
{
    $budget = $this->budgetRepository->show($id);
    if (!$budget || $budget->user_id !== auth()->user()->id) {
        throw new NotFoundException('budget.not-found');
    }
    return $budget;
}
```

**Correção 2 — Criar Policies (recomendado a longo prazo):**
```php
// app/Policies/BudgetPolicy.php
class BudgetPolicy
{
    public function view(User $user, Budget $budget): bool
    {
        return $user->id === $budget->user_id;
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->id === $budget->user_id;
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->id === $budget->user_id;
    }
}
```

### 3.3 VULNERABILIDADE: Mass Assignment no PeopleController

**Arquivo:** `app/Http/Controllers/PeopleController.php` (~L42)

**Status em 15/05/2026:** corrigido. O `PeopleController` agora valida a entrada no controller, delega persistência ao `PeopleService` e não usa mais `$request->all()`.

```php
$people = People::create($request->all());  // ❌ Aceita QUALQUER campo
```

**Correção:**
```php
$people = People::create($request->only(['name', 'phone', 'email']));
```

### 3.4 Dados Compartilhados via Inertia

**Arquivo:** `app/Http/Middleware/HandleInertiaRequests.php`  
**Problema:** Compartilha o objeto `$request->user()` inteiro com todas as páginas. Campos como `email`, `phone`, etc. estão acessíveis no JavaScript.

**Correção:**
```php
'auth' => [
    'user' => $request->user() ? $request->user()->only(['id', 'name']) : null,
],
```

---

## 4. Fase 2 — Integridade de Dados (Transactions e Exception Handling)

**Esforço:** ~6-8 horas | **Impacto:** Crítico | **Risco:** Médio

### 4.1 Refatorar Exception Handler

**Arquivo:** `app/Exceptions/Handler.php`  
**Problema:** O handler captura **TODOS os `Throwable`** e converte em `back()->withErrors()`. Isso:
- Engole erros 404 (`ModelNotFoundException`)
- Engole erros de validação (`ValidationException`)
- Engole erros de autorização (`AuthorizationException`)
- Impede handling adequado de erros Inertia

**Correção:**
```php
public function register(): void
{
    $this->renderable(function (Throwable $e, Request $request) {
        // Não interceptar exceções do framework
        if ($e instanceof ValidationException ||
            $e instanceof AuthenticationException ||
            $e instanceof NotFoundHttpException ||
            $e instanceof ModelNotFoundException) {
            return null; // Deixa o Laravel tratar
        }

        // Exceções de negócio
        DB::rollBack();
        
        if ($request->inertia()) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
        
        return back()->withErrors(['error' => $e->getMessage()]);
    });
}
```

### 4.2 Criar Custom Exceptions

**Problema:** Todas as exceções são `throw new Exception('translation.key')` genéricas. Impossível distinguir 404 de 409 de 422.

**Solução — Hierarquia de exceções:**

```php
// app/Exceptions/BusinessException.php
class BusinessException extends \RuntimeException
{
    public function __construct(string $translationKey, int $code = 422)
    {
        parent::__construct($translationKey, $code);
    }
}

// app/Exceptions/NotFoundException.php
class NotFoundException extends BusinessException
{
    public function __construct(string $translationKey = 'default.not-found')
    {
        parent::__construct($translationKey, 404);
    }
}

// app/Exceptions/DuplicateResourceException.php
class DuplicateResourceException extends BusinessException
{
    public function __construct(string $translationKey = 'default.already-exists')
    {
        parent::__construct($translationKey, 409);
    }
}
```

**Uso nos services:**
```php
// Antes:
throw new Exception('budget.not-found');
throw new Exception('budget.already-exists');

// Depois:
throw new NotFoundException('budget.not-found');
throw new DuplicateResourceException('budget.already-exists');
```

### 4.3 Consolidar Transaction no Middleware

**Arquivo:** `app/Http/Middleware/DbTransaction.php`  
**Problema:** A transação é aberta no middleware mas o rollback depende do Exception Handler. Se algo escapar do handler, a transação fica aberta.

**Correção — usar DB::transaction() no middleware:**
```php
public function handle(Request $request, Closure $next): Response
{
    if ($request->isMethod('GET')) {
        return $next($request);
    }

    return DB::transaction(function () use ($request, $next) {
        return $next($request);
    });
}
```

**Adicional:** Mover o middleware de `global` para o grupo `web`:
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... existentes
        DbTransaction::class,  // Mover para cá
    ],
];
```

### 4.4 Adicionar Transactions Explícitas em Operações Complexas

**Operações críticas sem transaction:**
- `BudgetService::createComplete()` — cria budget + 12 meses + expenses + provisions + invoices
- `BudgetService::clone()` — clona budget inteiro com relações
- `BudgetService::delete()` — cascading delete de todas as relações
- `CreditCardService::delete()` — cascading delete de card + invoices + expenses + divisions
- `BudgetExpenseService::createWithPortions()` — cria expense + N budgets futuros
- `CreditCardInvoiceExpenseService::createWithPortions()` — cria expense + N invoices futuras

> Se o DbTransaction middleware estiver funcionando corretamente como global, isso já está coberto. Mas é frágil e implícito. Considerar adicionar `DB::transaction()` explícito nos services para operações complexas como backup de segurança.

---

## 5. Fase 3 — Models (Casts, Relationships, Naming)

**Esforço:** ~4-6 horas | **Impacto:** Alto | **Risco:** Baixo

### 5.1 Adicionar `$casts` em Todos os Models

**Problema:** 19 de 23 models não têm `$casts`. Valores retornam como strings do MySQL, causando comparações incorretas e problemas de serialização JSON.

**Exemplo para `BudgetExpense`:**
```php
protected $casts = [
    'value' => 'decimal:2',
    'share_value' => 'decimal:2',
    'date' => 'date',
    'paid' => 'boolean',
];
```

**Tabela completa de casts necessários:**

| Model | Campos para Cast |
|-------|-----------------|
| Budget | `closed` (boolean), `total_expense`, `total_income` (decimal:2), week dates × 8 (date) |
| BudgetExpense | `value`, `share_value` (decimal:2), `date` (date), `paid` (boolean) |
| BudgetExpenseTagOption | `value` (decimal:2), `count_share` (boolean) |
| BudgetGoal | `value` (decimal:2), `count_share` (boolean) |
| BudgetIncome | `value` (decimal:2), `date` (date) |
| BudgetProvision | `value`, `share_value` (decimal:2) |
| CreditCard | `is_active` (boolean) |
| CreditCardInvoice | `total`, `total_paid` (decimal:2), `closed` (boolean), `due_date`, `closing_date` (date) |
| CreditCardInvoiceExpense | `value`, `share_value` (decimal:2), `date` (date) |
| CreditCardInvoiceExpenseDivision | `value`, `share_value` (decimal:2) |
| Financing | `total`, `fees_monthly` (decimal:2), `start_date` (date) |
| FinancingInstallment | `value`, `paid_value` (decimal:2), `date`, `payment_date` (date), `paid` (boolean) |
| FixExpense | `value`, `share_value` (decimal:2) |
| PrepaidCard | `is_active` (boolean) |
| PrepaidCardExtract | `credit` (decimal:2), `credit_date` (date) |
| PrepaidCardExtractExpense | `value`, `share_value` (decimal:2), `date` (date) |
| Provision | `value`, `share_value` (decimal:2) |

### 5.2 Corrigir `HasOne` → `BelongsTo` em 12 Models

**Problema:** 12 models usam `HasOne` para relações `user()` e `shareUser()` quando deveriam usar `BelongsTo`. Funciona porque as foreign keys são especificadas manualmente, mas é semanticamente errado e impede funcionalidades de `BelongsTo` como `associate()`.

**Antes:**
```php
public function user() {
    return $this->hasOne(User::class, 'id', 'user_id');
}
```

**Depois:**
```php
public function user() {
    return $this->belongsTo(User::class);
}
```

**Models afetados:**
Budget, BudgetExpense, BudgetProvision, CreditCard, CreditCardInvoiceExpense, CreditCardInvoiceExpenseDivision, Financing, FixExpense, PrepaidCard, PrepaidCardExtractExpense, Provision, ShareUser

### 5.3 Adicionar Relationships no Model User

**Problema:** O model `User` não define nenhum relacionamento de domínio.

```php
// app/Models/User.php — adicionar:
public function budgets(): HasMany
{
    return $this->hasMany(Budget::class);
}

public function creditCards(): HasMany
{
    return $this->hasMany(CreditCard::class);
}

public function financings(): HasMany
{
    return $this->hasMany(Financing::class);
}

public function tags(): HasMany
{
    return $this->hasMany(Tag::class);
}

// ... etc.
```

### 5.4 Corrigir Naming do Model `People` → `Person`

**Problema:** O model se chama `People` (plural), violando a convenção Eloquent de models singulares.

**Impacto:** Renomear requer atualizar controller, rotas e referências. O table name `peoples` (inferido) ou o que estiver definido na migration também precisaria ser verificado.

**Estratégia:** Manter `$table = 'people'` explícito no model, mas renomear a classe para `Person`.

### 5.5 Corrigir Table Name com Typo — `bugdet_incomes`

**Arquivo:** `app/Models/BudgetIncome.php` — `$table = 'bugdet_incomes'`  
**Problema:** Typo no nome da tabela que está persistido no banco. Corrigir requer migration de rename.

```php
// Nova migration:
Schema::rename('bugdet_incomes', 'budget_incomes');
```

### 5.6 Remover Model Órfão `Product`

**Arquivo:** `app/Models/Product.php`  
**Problema:** Model com campos de e-commerce (sku, brand, stock_quantity) completamente fora do domínio de finanças pessoais. Sem controller, service, repository ou rota.  
**Ação:** Remover o arquivo e sua migration (se houver).

### 5.7 Corrigir `$fillable` do User

**Problema:** `User::$fillable` lista apenas `name`, `email`, `password`, mas o banco tem campos `phone`, `gender`, `address` (conforme anotações de PHPDoc).  
**Ação:** Adicionar campos faltantes ou remover os que não são usados.

---

## 6. Fase 4 — Form Requests e Validação

**Esforço:** ~8-10 horas | **Impacto:** Médio-Alto | **Risco:** Baixo

### 6.1 Criar Form Requests para Todos os Controllers

**Problema:** Todos os 16 controllers de domínio usam validação inline `$this->validate()`. Isso:
- Duplica regras entre `store()` e `update()`
- Mistura validação com lógica do controller
- Não permite reutilização ou teste isolado
- Tem regras comentadas criando ruído

**Padrão proposto:**

```php
// app/Http/Requests/Budget/StoreBudgetRequest.php
class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou policy check
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'string', 'size:2'],
            // ... todas as regras
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => __('budget.year-required'),
        ];
    }
}
```

**Controller atualizado:**
```php
public function store(StoreBudgetRequest $request): RedirectResponse
{
    $this->budgetService->createComplete($request->validated());
    return redirect()->back()->with('success', 'default.sucess-save');
}
```

### 6.2 Estrutura de Diretórios

```
app/Http/Requests/
├── Auth/
│   └── LoginRequest.php (existente)
├── Budget/
│   ├── StoreBudgetRequest.php
│   ├── UpdateBudgetRequest.php
│   └── CloneBudgetRequest.php
├── BudgetExpense/
│   ├── StoreBudgetExpenseRequest.php
│   └── UpdateBudgetExpenseRequest.php
├── CreditCard/
│   ├── StoreCreditCardRequest.php
│   └── UpdateCreditCardRequest.php
├── Financing/
│   ├── StoreFinancingRequest.php
│   └── UpdateFinancingRequest.php
├── FixExpense/
│   ├── StoreFixExpenseRequest.php
│   └── UpdateFixExpenseRequest.php
├── PrepaidCard/
│   ├── StorePrepaidCardRequest.php
│   └── UpdatePrepaidCardRequest.php
├── Provision/
│   ├── StoreProvisionRequest.php
│   └── UpdateProvisionRequest.php
├── Tag/
│   ├── StoreTagRequest.php
│   └── UpdateTagRequest.php
├── CreditCardInvoiceExpense/
│   ├── StoreInvoiceExpenseRequest.php
│   ├── UpdateInvoiceExpenseRequest.php
│   └── ImportExcelRequest.php
├── ... (para cada entidade)
```

### 6.3 Remover Regras de Validação Comentadas

**Arquivos afetados:**
- `BudgetController.php` — regra de `closed` comentada
- `BudgetExpenseController.php` — regras de `paid` e `budget_id` comentadas
- `BudgetIncomeController.php` — regra de `budget_id` comentada

**Ação:** Remover linhas comentadas ou reativar caso sejam necessárias.

---

## 7. Fase 5 — DTOs e Redução de Parâmetros

**Esforço:** ~6-8 horas | **Impacto:** Alto | **Risco:** Médio

### 7.1 Problema: Métodos com 10-14 Parâmetros

| Método | Parâmetros | Service |
|--------|:----------:|---------|
| `BudgetService::createComplete()` | 14 | Pior caso |
| `CreditCardInvoiceExpenseService::update()` | 14 | |
| `CreditCardInvoiceExpenseService::createWithPortions()` | 13 | |
| `CreditCardInvoiceExpenseService::create()` | 13 | |
| `BudgetExpenseService::createWithPortions()` | 12 | |
| `PrepaidCardExtractExpenseService::update()` | 11 | |
| `BudgetService::create()` | 11 | |
| `BudgetExpenseService::create()` | 10 | |

### 7.2 Solução: Data Transfer Objects (DTOs)

```php
// app/DTOs/Budget/CreateBudgetDTO.php
readonly class CreateBudgetDTO
{
    public function __construct(
        public int $userId,
        public string $year,
        public string $month,
        public bool $closed,
        public WeekDatesDTO $weekDates,
        public bool $generateYear,
        public bool $hasFixExpenses,
        public bool $hasProvisions,
    ) {}

    public static function fromRequest(StoreBudgetRequest $request): self
    {
        return new self(
            userId: auth()->id(),
            year: $request->validated('year'),
            month: $request->validated('month'),
            closed: false,
            weekDates: WeekDatesDTO::fromRequest($request),
            generateYear: (bool) $request->validated('generate_year'),
            hasFixExpenses: (bool) $request->validated('fix_expenses'),
            hasProvisions: (bool) $request->validated('provisions'),
        );
    }
}

// app/DTOs/Budget/WeekDatesDTO.php
readonly class WeekDatesDTO
{
    public function __construct(
        public string $startWeek1,
        public string $endWeek1,
        public string $startWeek2,
        public string $endWeek2,
        public string $startWeek3,
        public string $endWeek3,
        public string $startWeek4,
        public string $endWeek4,
    ) {}
}
```

**Service atualizado:**
```php
// Antes: 14 parâmetros individuais
public function createComplete(int $userId, string $year, string $month, ...) 

// Depois: 1 DTO
public function createComplete(CreateBudgetDTO $dto): Budget
```

### 7.3 DTOs Necessários

| DTO | Substitui | Service |
|-----|-----------|---------|
| `CreateBudgetDTO` | 14 params | BudgetService |
| `CreateBudgetExpenseDTO` | 10-12 params | BudgetExpenseService |
| `CreateInvoiceExpenseDTO` | 13-14 params | CreditCardInvoiceExpenseService |
| `CreateExtractExpenseDTO` | 11 params | PrepaidCardExtractExpenseService |
| `ShareDataDTO` | Padrão repetido `share_value` + `share_user_id` | Vários services |

---

## 8. Fase 6 — Refatoração de God Classes

**Esforço:** ~10-14 horas | **Impacto:** Alto | **Risco:** Médio-Alto

### 8.1 Dividir `BudgetShowData` (1206 linhas)

**Maior arquivo do backend.** Responsabilidades misturadas:
- Preparação de dados de exibição do budget
- Cálculo de resumos semanais
- Montagem de gráficos de goals
- Matching de tags entre entidades
- Formatação de dados para frontend

**Proposta de divisão:**

```
app/Helpers/Budget/
├── BudgetShowData.php          ← Orchestrator (~100 linhas)
├── BudgetResumeBuilder.php     ← Monta resumo semanal
├── BudgetGoalsChartBuilder.php ← Monta gráfico de metas
├── BudgetTagMatcher.php        ← Lógica de matching de tags (reutilizável)
├── BudgetExpenseAggregator.php ← Agrega expenses + invoices + extracts
└── Interfaces/
    ├── BudgetShowDataInterface.php (mantém)
    └── ... novas interfaces
```

### 8.2 Extrair `BudgetTagMatcher` (Eliminar Duplicação Massiva)

**Problema:** O pattern de matching de tags é repetido **~20 vezes** dentro de `BudgetShowData`:

```php
$goal->tags->every(fn ($item) => $entity->tags->contains('id', $item->id))
```

**Combinado com iteração idêntica sobre:**
```
provisions → expenses → invoices → invoice.expenses → divisions → extracts → extract.expenses
```

**Extrair para classe dedicada:**
```php
class BudgetTagMatcher
{
    public function findMatchingExpenses(Collection $tags, Budget $budget): Collection
    {
        // Lógica centralizada de matching
    }

    public function calculateTagTotal(Collection $tags, Budget $budget): float
    {
        // Soma valores de todas as entidades com tags matching
    }
}
```

### 8.3 Dividir `BudgetService` (675 linhas, 14 dependências)

**Problema:** `BudgetService` é um God Service com 14 injeções de dependência.

**Propostas:**
1. Extrair `BudgetCloneService` — lógica de clone (~100 linhas)
2. Extrair `BudgetCreationService` — lógica de `createComplete()` (~140 linhas)
3. Manter `BudgetService` para CRUD simples

### 8.4 Dividir `CreditCardInvoiceExpenseService` (393 linhas, 8 dependências)

**Propostas:**
1. Extrair `InvoiceExpensePortionService` — lógica de parcelamento
2. Extrair `InvoiceExpenseImportService` — lógica de importação Excel

---

## 9. Fase 7 — Eliminação de Duplicação nos Services

**Esforço:** ~6-8 horas | **Impacto:** Médio | **Risco:** Baixo

### 9.1 Extrair `ShareUserLoader` (Trait ou Service)

**Código duplicado em 5 arquivos:**
```php
$shareUsers = $this->shareUserRepository->get(
    ['user_id' => auth()->user()->id], [], [], ['shareUser']
);
if ($shareUsers && $shareUsers->count()) {
    $shareUsers = $shareUsers->map(function ($item) {
        return [
            'share_user_id' => $item->share_user_id,
            'share_user_name' => $item->shareUser->name,
        ];
    });
}
```

**Localizado em:**
- `CreditCardInvoiceService` (L195-202)
- `FixExpenseService` (L33-39)
- `ProvisionService` (L33-39)
- `PrepaidCardExtractService` (L163-169)
- `BudgetShowData` (L100-108)

**Solução:**
```php
// app/Services/Traits/LoadsShareUsers.php
trait LoadsShareUsers
{
    private function loadShareUsers(): Collection
    {
        $shareUsers = $this->shareUserRepository->get(
            ['user_id' => auth()->user()->id], [], [], ['shareUser']
        );
        
        return $shareUsers?->map(fn ($item) => [
            'share_user_id' => $item->share_user_id,
            'share_user_name' => $item->shareUser->name,
        ]) ?? collect();
    }
}
```

### 9.2 Extrair Pattern de Cascading Delete

**Código duplicado em 4 services:**
```php
foreach ($entity->children as $child) {
    $this->tagRepository->saveTagsToModel($child, $child->tags);
    $this->childRepository->delete($child->id);
}
```

**Localizado em:** `CreditCardService`, `CreditCardInvoiceService`, `PrepaidCardService`, `PrepaidCardExtractService`

### 9.3 Eliminar Facade `TagService` e Usar DI

**Problema:** `FixExpenseService` e `ProvisionService` usam `TagService::saveTagsToModel()` via Facade, enquanto todos os outros services usam `TagRepositoryInterface` via DI.

**Correção:** Injetar `TagRepositoryInterface` nos dois services e remover o uso da Facade:
```php
// Antes (FixExpenseService):
use TagService;
TagService::saveTagsToModel($entity, $tags);

// Depois:
public function __construct(
    private FixExpenseRepositoryInterface $fixExpenseRepository,
    private TagRepositoryInterface $tagRepository,  // ← Adicionar
) {}

$this->tagRepository->saveTagsToModel($entity, $tags);
```

### 9.4 Remover Facades Não Utilizadas

- `app/Services/Facades/BudgetService.php` — Facade importada mas nunca usada em `BudgetIncomeService`
- Considerar remover ambas as Facades (`TagService` e `BudgetService`) se toda a comunicação migrar para DI

### 9.5 Consolidar Type Casting do Controller para Service/DTO

**Padrão duplicado em 10+ controllers:**
```php
floatval($request->value)
intval($request->budget_id)
$request->paid == 1 ? true : false
$request->share_value ? floatval($request->share_value) : null
collect($request->tags)
```

**Com Form Requests + DTOs (Fases 4 e 5)**, esse casting será centralizado e não mais duplicado.

---

## 10. Fase 8 — Performance (N+1, Queries)

**Esforço:** ~6-8 horas | **Impacto:** Médio-Alto | **Risco:** Médio

### 10.1 `recalculate()` Chamado em Loops

**Problema:** O método `BudgetCalculate::recalculate()` executa ~8 queries internas. Quando chamado dentro de loops (criação de 12 meses, parcelas), o número de queries explode.

**Localização:**
| Service | Contexto | Queries Estimadas |
|---------|----------|:-----------------:|
| `BudgetService::createComplete()` | Loop de 12 meses × recalculate | ~96 queries |
| `BudgetExpenseService::createWithPortions()` | Loop de N parcelas × recalculate | ~8N queries |
| `CreditCardInvoiceExpenseService::createWithPortions()` | Loop de N parcelas × recalculate | ~8N queries |

**Solução:** Recalcular apenas uma vez após o loop:
```php
// Antes:
foreach ($months as $month) {
    $this->createExpense(...);
    $this->budgetCalculate->recalculate($budgetId); // ❌ Em cada iteração
}

// Depois:
$budgetIds = [];
foreach ($months as $month) {
    $budgetIds[] = $this->createExpense(...);
}
// Recalcular todos de uma vez após o loop:
foreach (array_unique($budgetIds) as $budgetId) {
    $this->budgetCalculate->recalculate($budgetId);
}
```

### 10.2 Delete Cascading Um-a-Um

**Problema:** `CreditCardService::delete()` itera invoices → expenses → divisions deletando individualmente.

**Solução:** Usar cascade delete no banco de dados ou batch delete:
```php
// Batch delete via query builder:
CreditCardInvoiceExpense::whereIn('credit_card_invoice_id', $invoiceIds)->delete();
```

### 10.3 `TagRepository::saveTagsToModel()` — Query por Tag

**Problema:** Itera sobre tags fazendo `getOne()` para cada uma.

**Solução:** Buscar todos de uma vez:
```php
$existingTags = $this->model->whereIn('id', $tagIds)->get()->keyBy('id');
```

### 10.4 Habilitar `preventLazyLoading()` em Development

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    Model::preventLazyLoading(!app()->isProduction());
}
```

Isso lançará exceções quando N+1 queries forem detectadas em dev.

### 10.5 `auth()->user()->id` Chamado Repetidamente

**Problema:** `auth()->user()->id` é chamado ~25 vezes nos services. Cada chamada resolve o guard e busca o user.

**Solução:** Armazenar em variável local ou passar como parâmetro:
```php
$userId = auth()->id(); // Mais eficiente, cached
```

---

## 11. Fase 9 — Padronização e Qualidade de Código

**Esforço:** ~4-6 horas | **Impacto:** Baixo-Médio | **Risco:** Baixo

### 9.1 Adicionar Return Types em Todos os Controllers

**Problema:** Zero controllers de domínio declaram return types.

```php
// Antes:
public function index(Request $request)

// Depois:
public function index(Request $request): Response|ResponseFactory
```

Tipos comuns: `Response` (Inertia::render), `RedirectResponse` (redirect), `JsonResponse` (search).

### 9.2 Renomear `delete()` → `destroy()` nos Controllers

**Problema:** 16 controllers usam `delete()` em vez do padrão Laravel `destroy()`.

**Impacto:** Requer atualizar rotas em `web.php` também.

### 9.3 Corrigir Typos em Nomes de Propriedades

| Controller | Atual | Correto |
|-----------|-------|---------|
| `BudgetExpenseController` | `$budgetExpenseSevice` | `$budgetExpenseService` |
| `BudgetIncomeController` | `$budgetIncomeSevice` | `$budgetIncomeService` |

### 9.4 Corrigir Typo nas Chaves de Tradução

**Todas as flash messages usam `sucess` (sem 'c'):  **
- `'default.sucess-save'` → `'default.success-save'`
- `'default.sucess-update'` → `'default.success-update'`
- `'default.sucess-delete'` → `'default.success-delete'`

> **Atenção:** Isso requer atualizar os arquivos de tradução (`pt.json`, `en.json`) também.

### 9.5 Remover Imports Não Utilizados

| Controller | Import Não Utilizado |
|-----------|---------------------|
| `BudgetIncomeController` | `use App\Services\BudgetIncomeService` (classe concreta, só usa interface) |
| `BudgetProvisionController` | `use App\Services\BudgetProvisionService` |
| `CreditCardInvoiceController` | `use Illuminate\Support\Facades\Log` |
| `BudgetExpenseTagOption` (model) | `use Attribute`, `use MorphOne` |
| `BudgetGoal` (model) | `use Attribute`, `use MorphOne` |
| `BudgetIncome` (model) | `use HasOne` |

### 9.6 Extrair Constantes/Enums para Grupos

**Strings hardcoded em services e models:**
```php
'MONTHLY', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4'
'GENERAL', 'INVESTIMENT', 'TRIP', 'OTHERS'  // Nota: "INVESTIMENT" está errado → "INVESTMENT"
'PORTION'
```

**Solução — PHP 8.1 Enums:**
```php
// app/Enums/ExpenseGroup.php
enum ExpenseGroup: string
{
    case Monthly = 'MONTHLY';
    case Week1 = 'WEEK_1';
    case Week2 = 'WEEK_2';
    case Week3 = 'WEEK_3';
    case Week4 = 'WEEK_4';
}

// app/Enums/ProvisionGroup.php
enum ProvisionGroup: string
{
    case General = 'GENERAL';
    case Investment = 'INVESTIMENT';  // Manter valor existente no DB
    case Trip = 'TRIP';
    case Others = 'OTHERS';
}
```

### 9.7 Nomear Todas as Rotas

**Problema:** ~50 rotas sem nome, impedindo uso de `route()` helper.

```php
// Antes:
Route::get('/budget', 'index');

// Depois:
Route::get('/budget', 'index')->name('budget.index');
Route::post('/budget', 'store')->name('budget.store');
Route::put('/budget/{id}', 'update')->name('budget.update');
Route::delete('/budget/{id}', 'delete')->name('budget.destroy');
```

### 9.8 Usar Route Model Binding

```php
// Antes:
Route::get('/budget/show/{id}', 'show');
// Controller: $budget = $this->service->show($id);

// Depois:
Route::get('/budget/{budget}', 'show');
// Controller: public function show(Budget $budget): Response
```

### 9.9 PeopleController — Migrar para Arquitetura Padrão

**Único controller que não segue o padrão Service/Repository:**
1. Criar `PeopleService` + `PeopleServiceInterface`
2. Criar `PeopleRepository` + `PeopleRepositoryInterface`
3. Migrar lógica de queries e CRUD do controller para service
4. Registrar bindings em `AppServiceProvider` e `RepositoryServiceProvider`
5. Usar chaves de tradução em vez de strings inglês hardcoded

**Status em 15/05/2026:** concluído com `PeopleService`, `PeopleRepository`, interfaces e bindings registrados, controller magro e cobertura feature para index com filtros e CRUD completo.

### 9.10 Docblocks — Corrigir Copy-Paste

**Exemplos:**
- `BudgetGoalService::delete()` — docblock diz "Delete a new Goal" (deveria ser "Delete a Goal")
- `BudgetIncomeService::delete()` — docblock diz "Delete a new Income"
- Vários services com `/** */` vazios

---

## 12. Fase 10 — Testes Automatizados

**Esforço:** ~20-40 horas | **Impacto:** Alto (longo prazo) | **Risco:** Baixo

### 10.1 Estado Atual

- PHPUnit 10 já está configurado em `phpunit.xml`.
- A suíte de feature foi alinhada aos fluxos realmente expostos pelo produto; testes legados do Breeze acoplados ao fluxo inexistente de `profile` foram removidos da suíte ativa.
- A suíte de unit tests já possui um teste real de service em `tests/Unit/Services/BudgetGoalServiceTest.php` além do placeholder inicial.
- O projeto usa `RefreshDatabase`, `actingAs()` e assertions HTTP, com `VerifyCsrfToken` desativado no bootstrap de testes para permitir os POSTs esperados pela suíte feature.
- O domínio principal agora possui factories para `Budget`, `CreditCard`, `CreditCardInvoice`, `PrepaidCard` e `PrepaidCardExtract`.
- `E2ESmokeSeeder` fornece massa previsível para autenticação e navegação básica entre backend feature e frontend E2E.
- A suíte feature já cobre ownership no search de tags e o fluxo de `PeopleController`; a suíte unit/frontend cobre os contratos compartilhados de busca no lado Vue.
- O `phpunit.xml` ainda mantém comentada a configuração de SQLite em memória; por enquanto a execução previsível segue no caminho MySQL/container do projeto.
- Em resumo: a base de testes existe, a fundação da onda 0 ficou executável e a cobertura real do domínio financeiro começou a sair do zero.

### 10.2 Ferramentas Recomendadas

**Recomendação principal para o backend:** manter PHPUnit 10 como padrão do repositório e complementar com Mockery para isolamento de services e helpers.

```text
Backend unit/feature: PHPUnit 10 + Mockery + utilitários de teste do Laravel
Frontend unit/componente: Vitest + @vue/test-utils + jsdom
Frontend E2E: Playwright
```

**Por que manter PHPUnit aqui:**

- já está instalado e configurado no projeto;
- os testes atuais já seguem esse estilo;
- a documentação do Laravel cobre muito bem esse fluxo;
- introduzir Pest agora criaria duas convenções de teste sem ganho proporcional imediato.

> Pest continua sendo uma opção válida no ecossistema Laravel, mas para este repositório a escolha mais pragmática e consolidada é continuar em PHPUnit.

### 10.3 Matriz de Testes Integrada com o Frontend

| Camada | Ferramenta | Objetivo | Primeiros alvos |
|:------:|------------|----------|-----------------|
| Backend Unit | PHPUnit + Mockery | Regras de negócio isoladas | services CRUD pequenos, validações de exceção, helpers, DTOs com lógica |
| Backend Feature | PHPUnit + Laravel TestCase | Rotas, autenticação, autorização, persistência | auth, ownership/IDOR, CRUD principal |
| Frontend Unit/Component | Vitest + `@vue/test-utils` | Composables, componentes e layout | `useShareCalculation`, `useTagSearch`, `useCrudOperations`, `AuthenticatedLayout` |
| Frontend E2E | Playwright | Fluxos completos do usuário | login, budget, invoice, extract |

### 10.4 Prioridade de Testes Backend

| Prioridade | Tipo | Cobertura |
|:----------:|------|-----------|
| 1 | Feature Tests | Autenticação, middleware `auth` e cenários de IDOR/ownership |
| 2 | Unit Tests | `BudgetService` (`clone()`, `createComplete()`), `BudgetExpenseService`, `CreditCardInvoiceExpenseService` |
| 3 | Unit Tests | Helpers e classes de cálculo (`BudgetCalculate`, `BudgetShowData`, futuros extratos como `BudgetTagMatcher`) |
| 4 | Feature Tests | CRUDs principais com flash messages, validação e autorização |
| 5 | Unit/Integration Tests | Repositories, tags, transações e factories do domínio |

### 10.5 Configuração Recomendada

```php
// phpunit.xml — Usar SQLite in-memory para testes:
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Recomendação prática para o repositório:**

- descomentar a configuração de SQLite em memória no `phpunit.xml` ou mover isso para um `.env.testing` dedicado;
- criar factories para as entidades centrais do domínio antes de expandir a cobertura;
- separar a execução por suíte para feedback rápido;
- manter todos os comandos via scripts do projeto.

```bash
./scripts/artisan.sh test
./scripts/artisan.sh test --testsuite=Unit
./scripts/artisan.sh test --testsuite=Feature
```

### 10.6 Exemplo de Unit Test Sugerido

```php
use App\Repositories\Interfaces\BudgetGoalRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\BudgetGoalService;
use Exception;
use Mockery;
use Tests\TestCase;

class BudgetGoalServiceTest extends TestCase
{
    public function test_delete_throws_when_goal_does_not_exist(): void
    {
        $budgetRepository = Mockery::mock(BudgetRepositoryInterface::class);
        $goalRepository = Mockery::mock(BudgetGoalRepositoryInterface::class);
        $tagRepository = Mockery::mock(TagRepositoryInterface::class);

        $goalRepository->shouldReceive('show')->once()->with(10)->andReturn(null);
        $tagRepository->shouldNotReceive('saveTagsToModel');
        $goalRepository->shouldNotReceive('delete');

        $service = new BudgetGoalService($budgetRepository, $goalRepository, $tagRepository);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('budget-goal.not-found');

        $service->delete(10);
    }
}
```

Esse padrão é o que deve crescer junto com o frontend: regras de negócio isoladas no backend e comportamento/interação no frontend.

### 10.7 Exemplo de Feature Test Sugerido

```php
class BudgetAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_trying_to_access_budget_index(): void
    {
        $this->get('/budget/2026')->assertRedirect('/login');
    }
}
```

### 10.8 Factories Necessárias

Criar factories para todos os models de domínio:
- `BudgetFactory`, `BudgetExpenseFactory`, `CreditCardFactory`, `CreditCardInvoiceFactory`, etc.
- `UserFactory` e `PeopleFactory` já existem.

### 10.9 Ordem Recomendada Para Caminhar Junto com o Frontend

1. Cobrir no backend os services menores e cenários de autorização mais críticos.
2. Cobrir no frontend os composables e componentes que dependem dessas mesmas regras.
3. Fechar o ciclo com Playwright nos fluxos completos já existentes (`login`, `budget`, `invoice`, `extract`).
4. Só depois expandir para os monólitos maiores e cenários complexos de importação, parcelamento e clone.

---

## 13. Resumo de Impacto por Fase

| Fase | Descrição | Esforço | Impacto | Risco | Dependências |
|:----:|-----------|:-------:|:-------:|:-----:|:------------:|
| 🔴 | Bugs Críticos | 1-2h | Crítico | Baixo | Nenhuma |
| 1 | Segurança e Autorização | 4-6h | Crítico | Baixo | Nenhuma |
| 2 | Transactions e Exceptions | 6-8h | Crítico | Médio | Nenhuma |
| 3 | Models (casts, relationships) | 4-6h | Alto | Baixo | Nenhuma |
| 4 | Form Requests | 8-10h | Médio-Alto | Baixo | Nenhuma |
| 5 | DTOs | 6-8h | Alto | Médio | Fase 4 (recomendado) |
| 6 | God Classes | 10-14h | Alto | Médio-Alto | Fase 5 (recomendado) |
| 7 | Eliminação de Duplicação | 6-8h | Médio | Baixo | Nenhuma |
| 8 | Performance (N+1) | 6-8h | Médio-Alto | Médio | Nenhuma |
| 9 | Padronização | 4-6h | Baixo-Médio | Baixo | Nenhuma |
| 10 | Testes | 20-40h | Alto (longo prazo) | Baixo | Fases 1-5 (ideal) |

**Ordem recomendada:** Bugs → Fase 1 (Segurança!) → Fase 2 → Fase 3 → Fase 9 → Fase 4 → Fase 5 → Fase 7 → Fase 8 → Fase 6 → Fase 10

**Tempo total estimado:** ~75-115 horas de desenvolvimento

---

## 14. Apêndice A — Inventário de Problemas por Arquivo

### Controllers

| Controller | Linhas | Problemas |
|-----------|:------:|-----------|
| BudgetController | 161 | Sem return types, 14 params no store, sem auth check |
| BudgetExpenseController | 103 | Typo `$budgetExpenseSevice`, sem return types |
| BudgetExpenseTagOptionController | 68 | Sem return types |
| BudgetGoalController | 79 | Sem return types |
| BudgetIncomeController | 73 | Typo `$budgetIncomeSevice`, import morto, sem return types |
| BudgetProvisionController | 88 | Import morto, sem return types |
| CreditCardController | 85 | Sem return types |
| CreditCardInvoiceController | 103 | Import `Log` não usado, sem return types |
| CreditCardInvoiceExpenseController | 139 | Sem return types |
| DashboardController | 19 | `$this->middleware('auth')` (deprecated), sem service |
| FinancingController | 99 | Import morto, sem return types |
| FinancingInstallmentController | 52 | Sem return types |
| FixExpenseController | 86 | Sem return types |
| PeopleController | 80 | Bypasses architecture (!), `$request->all()`, strings EN hardcoded |
| PrepaidCardController | 75 | Sem return types |
| PrepaidCardExtractController | 103 | Sem return types |
| PrepaidCardExtractExpenseController | 104 | Sem return types |
| ProvisionController | 83 | Import morto, sem return types |
| TagController | 74 | Sem return types |

### Services

| Service | Linhas | Dependências | Problemas |
|---------|:------:|:------------:|-----------|
| BudgetService | 675 | 14 | God Service, N+1 em loops, duplicação em createComplete |
| BudgetShowData (Helper) | 1206 | 4 | God Class, bug $installments, duplicação massiva tag matching |
| CreditCardInvoiceExpenseService | 393 | 8 | 14 params, N+1 em portions |
| BudgetExpenseService | 291 | 4 | N+1 em portions |
| PrepaidCardExtractExpenseService | 241 | 6 | 11 params |
| CreditCardInvoiceService | 210 | 8 | - |
| BudgetProvisionService | 163 | 4 | - |
| BudgetCalculate (Helper) | 161 | 4 | Query complexa mas funcional |
| CreditCardService | 165 | 6 | N+1 em cascading delete |
| PrepaidCardExtractService | 178 | 6 | - |
| FixExpenseService | 140 | 2 | Usa Facade TagService |
| ProvisionService | 140 | 2 | Usa Facade TagService |
| FinancingService | 138 | 2 | - |
| BudgetIncomeService | 122 | 4 | Import BudgetService Facade morto |
| TagService | 112 | 1 | - |
| BudgetGoalService | 105 | 3 | Docblock errado no delete |
| BudgetExpenseTagOptionService | 97 | 3 | - |
| FinancingInstallmentService | 72 | 2 | - |

### Models

| Model | Problemas |
|-------|-----------|
| Budget | Sem casts, user() como HasOne |
| BudgetExpense | Sem casts, shareUser() como HasOne |
| BudgetIncome | Typo no `$table`, sem casts |
| CreditCard | Sem casts, user() como HasOne |
| Financing | Sem casts, user() como HasOne |
| FixExpense | Sem casts, user() e shareUser() como HasOne |
| People | Nome plural, sem relações, accessor antigo |
| Product | Órfão, fora do domínio |
| User | $fillable incompleto, sem relações de domínio |
| *19 outros models* | Sem casts para decimal/boolean/date |

---

## 15. Apêndice B — Mapa de Duplicação no Backend

```
┌─────────────────────────────────┬──────────────────────────────────────────────────┐
│ Código Duplicado                 │ Arquivos Afetados                                │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ shareUsers loading pattern      │ Extraído para `ShareUserOptions`; a duplicação   │
│                                 │ original em CreditCardInvoiceService,            │
│                                 │ FixExpenseService, ProvisionService,             │
│                                 │ PrepaidCardExtractService e BudgetShowData foi   │
│                                 │ removida em 15/05/2026                           │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ Cascading delete pattern        │ CreditCardService, CreditCardInvoiceService,     │
│                                 │ PrepaidCardService, PrepaidCardExtractService (4) │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ Invoice/Extract linking logic   │ BudgetService.createComplete() (duplicado 2x     │
│                                 │ dentro do mesmo método: L114-L131 e L195-L216)   │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ Tag matching pattern            │ BudgetShowData (duplicado ~20x internamente)      │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ CRUD template create/update     │ Todos os 12 services CRUD                        │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ floatval/intval casting         │ Todos os 16 controllers                          │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ Flash message keys (com typo)   │ Todos os 16 controllers                          │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ $this->validate() rules         │ Todos os 16 controllers (store duplica update)   │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ search() controller method      │ TagController, BudgetProvisionController,        │
│                                 │ CreditCardInvoiceExpenseController,              │
│                                 │ PrepaidCardExtractExpenseController (4)           │
├─────────────────────────────────┼──────────────────────────────────────────────────┤
│ downloadTemplate() method       │ CreditCardInvoiceController,                     │
│                                 │ PrepaidCardExtractController (2)                 │
└─────────────────────────────────┴──────────────────────────────────────────────────┘
```

---

*Este documento complementa o [Plano de Melhorias Frontend](frontend-improvement-plan.md). As fases podem ser executadas independentemente, porém a ordem recomendada maximiza os benefícios e reduz retrabalho.*
