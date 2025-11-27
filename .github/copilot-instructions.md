# To The Future - Instruções para Agentes de IA

## Visão Geral do Projeto

Aplicação de gestão financeira pessoal desenvolvida com Laravel 10, Inertia.js, Vue 3 e Vuetify. Gerencia orçamentos, despesas, cartões de crédito, faturas, provisionamentos e metas financeiras com arquitetura em camadas (Controller → Service → Repository).

## Padrão de Arquitetura

### Arquitetura em Camadas com Injeção de Dependência

**Crítico: Sempre siga este fluxo exato**
```
Controller → Service Interface → Service Implementation → Repository Interface → Repository Implementation → Model
```

Adicionalmente, **Helpers** podem ser injetados em Services para lógicas específicas e reutilizáveis.

#### Camada Controller (`app/Http/Controllers/`)
- Controllers enxutos com lógica mínima
- Injete interfaces de serviços via construtor: `private ServiceInterface $service`
- Valide requests com `$this->validate()`
- Retorne views Inertia: `Inertia::render('Page/Component', $data)`
- Mensagens flash: `->with('success', 'translation.key')`

Exemplo de padrão do `BudgetController.php`:
```php
public function __construct(private BudgetServiceInterface $budgetService) {}

public function store(Request $request) {
    $this->validate($request, ['year' => ['required'], 'month' => ['required']]);
    $this->budgetService->createComplete(/* params */);
    return redirect()->back()->with('success', 'default.sucess-save');
}
```

#### Camada Service (`app/Services/`)
- Contém TODA a lógica de negócio
- Injeção de dependência pesada (injeta múltiplos services, repositories e helpers)
- Lance `Exception('translation.key')` para erros de negócio
- Services coordenam entre repositories, outros services e helpers
- Interface em `app/Services/Interfaces/`, implementação registrada em `AppServiceProvider`

Exemplo do `BudgetService.php`:
```php
public function __construct(
    private BudgetExpenseServiceInterface $budgetExpenseService,
    private BudgetRepositoryInterface $budgetRepository,
    private BudgetCalculateInterface $budgetCalculate,  // Helper injetado
    // ... muitas outras injeções
) {}
```

#### Camada Helper (`app/Helpers/`)
- Classes especializadas que desempenham papéis específicos
- Normalmente utilizadas dentro de Services
- Possuem interface própria em `Interfaces/` subdirectory
- Registradas no `HelperServiceProvider` (não no `AppServiceProvider`)
- Exemplo: `BudgetCalculate` (recalcula orçamentos), `BudgetShowData` (prepara dados para exibição)

Padrão de Helper:
```php
class BudgetCalculate implements BudgetCalculateInterface {
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        // ... outros repositories necessários
    ) {}
    
    public function recalculate(int $id, bool $calculateShareUser = false): bool {
        // Lógica específica de recálculo
    }
}
```

#### Camada Repository (`app/Repositories/`)
- Estende `AppRepository` (fornece métodos CRUD base)
- Interface estende `AppRepositoryInterface`
- Maioria dos repositories são mínimos: apenas construtor chamando parent
- Métodos base: `get()`, `getOne()`, `show()`, `store()`, `delete()`, `queryGet()`

Padrão de repository:
```php
class BudgetRepository extends AppRepository implements BudgetRepositoryInterface {
    public function __construct(?Budget $budget = null) {
        parent::__construct($budget ?? new Budget);
    }
}
```

#### Query Builder do Repository Base (`AppRepository`)

Assinatura do método `queryGet()` (usado internamente por `get()`, `getOne()`, `show()`):
```php
queryGet($condition, array $columns = [], array $simpleJoins = [], array $withs = [])
```

- `$condition`: Closure para queries complexas OU array `['field' => 'value']` para where simples
- `$withs`: Eager load de relacionamentos `['relation1', 'relation2.nested']`

Exemplo do `BudgetService.php`:
```php
$this->creditCardInvoiceRepository->get(
    function (Builder $query) use ($budget) {
        $query->where(['year' => $budget->year, 'month' => $budget->month])
              ->whereHas('creditCard', function (Builder $query) use ($budget) {
                  $query->where('user_id', $budget->user_id);
              });
    },
    [], [], [] // columns, joins, withs
);
```

### Registro de Injeção de Dependência

Todas as interfaces são registradas no método boot dos Service Providers:

**Services e Repositories** em `app/Providers/AppServiceProvider.php`:
```php
$this->app->bind(BudgetServiceInterface::class, BudgetService::class);
$this->app->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
```

**Helpers** em `app/Providers/HelperServiceProvider.php`:
```php
$this->app->bind(BudgetCalculateInterface::class, BudgetCalculate::class);
$this->app->bind(BudgetShowDataInterface::class, BudgetShowData::class);
```

**Ao criar novos services/repositories/helpers:**
1. Crie interface no diretório `Interfaces/`
2. Implemente a interface
3. Registre o binding no ServiceProvider apropriado
   - Services/Repositories → `AppServiceProvider`
   - Helpers → `HelperServiceProvider`

## Ambiente de Desenvolvimento Docker

### Comandos Principais (via `start.sh`)

**Nunca use `docker compose` diretamente - sempre use `./start.sh`**

Comandos essenciais:
```bash
./start.sh dev           # Inicia desenvolvimento (nginx, php, mysql)
./start.sh dev-full      # Inclui Vite hot reload (porta 5173)
./start.sh stop          # Para todos os containers
./start.sh logs          # Acompanha os logs
./start.sh status        # Verifica status dos containers
```

### Acesso a Ferramentas via Scripts

Todos os comandos executam DENTRO dos containers via scripts wrapper em `scripts/`:

```bash
./scripts/artisan.sh migrate              # Migrations do Laravel
./scripts/artisan.sh tinker               # Shell interativo
./scripts/composer.sh install             # Dependências Composer
./scripts/npm.sh install                  # Dependências Node
./scripts/npm.sh run dev                  # Build Vite
./scripts/php-cs-fixer.sh fix            # Formatação de código
./scripts/ide-helper.sh                   # Gera IDE helpers
```

**Crítico:** Nunca execute `php`, `composer`, `npm` diretamente no host - as dependências estão nos containers.

### Tasks do VS Code

Use tasks predefinidas (Ctrl+Shift+P → "Run Task"):
- "Laravel: Start Development"
- "Artisan: Migration Run"
- "Vite: Development Build"
- "PHP CS Fixer: Fix All"

Veja `.vscode/tasks.json` ou saída de ajuda do `start.sh` para lista completa.

### Arquitetura dos Containers

Services (veja `docker-compose.yml`):
- **php**: Dockerfile.dev customizado com mapeamento de usuário (PUID/PGID), roda PHP-FPM na porta 9000
- **nginx**: Alpine, faz proxy para php:9000, expõe porta 8080
- **vite**: Baseado em profile (`--profile vite-dev`), polling do Chokidar desabilitado, porta 5173
- **db**: MySQL 8.0, porta 3307, credenciais no `.env` (padrão: admin/root1234)

Volumes:
- `vendor`: Mount delegado para performance
- `node_modules`: Compartilhado entre containers php e vite
- `db_data`: Armazenamento persistente do MySQL

## Stack Frontend (SPA Inertia.js)

### Tech Stack
- **Laravel Vite Plugin**: Bundling de assets (`resources/js/app.js` como entry point)
- **Inertia.js 2.0**: Rotas backend renderizam componentes Vue diretamente (sem camada de API)
- **Vue 3**: Composition API NÃO é usado, Options API em todo o projeto
- **Vuetify 3**: Componentes Material Design com auto-import
- **Vue I18n**: Arquivos de tradução em `resources/js/Locales/`
- **Moment.js**: Manipulação de datas

### Padrões Inertia

**Componentes de Página** (`resources/js/Pages/`):
```vue
<script>
import { Head } from '@inertiajs/vue3'
export default {
    props: ['budgets', 'year'], // Props vindas do controller
    methods: {
        submit() {
            this.$inertia.post('/budget', this.form, {
                onSuccess: () => { /* ... */ }
            })
        }
    }
}
</script>
```

**Navegação:**
- Formulários: `this.$inertia.post()`, `this.$inertia.put()`, `this.$inertia.delete()`
- Links: `<Link href="/budget/show/123">Ver</Link>`
- Redirects backend: `redirect()->back()->with('success', 'key')`

**Mensagens flash** passadas como props, tratadas pelo plugin toast.

### Convenções Vuetify

- Componentes auto-importados (não precisa import manual)
- Wrapper `<v-app>` em `Layouts/Default.vue`
- Material Design Icons: classes `mdi-*`
- Formulários usam `v-form`, `v-text-field`, `v-select`, etc.

### Organização de Arquivos
```
resources/js/
├── Components/      # Componentes Vue reutilizáveis
├── Layouts/         # Layout wrappers (Default.vue)
├── Locales/         # Arquivos de tradução i18n
├── Pages/           # Componentes de página Inertia (Budget/, CreditCard/, etc.)
├── Plugins/         # Plugins Vue (toast, i18n, vuetify)
├── utils/           # Utilitários helper
└── app.js           # Entry point
```

### Componentização

**Importante:** O projeto usa componentização Vue, mas NÃO utiliza gerenciamento de estado centralizado (Vuex/Pinia). A comunicação entre componentes é feita via:
- Props (pai → filho)
- Events (filho → pai)
- Props do Inertia (dados do backend)

## Modelo de Domínio

### Entidades Principais

**Budget** (tabela `budgets`):
- Entidade central: combinação ano/mês por usuário
- Semanas: 4 períodos semanais com datas início/fim (`start_week_1`, `end_week_1`, etc.)
- Relacionamentos: expenses, incomes, provisions, goals, invoices (cartões de crédito), extracts (cartões pré-pagos)

**BudgetExpense** (`budget_expenses`):
- Itens de despesa individuais dentro do orçamento
- Grupos: 'MONTHLY', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4'
- Pode ser compartilhado (`share_value`, `share_user_id`)
- Tags: Many-to-many polimórfico via `taggables`

**CreditCardInvoice** + **CreditCardInvoiceExpense**:
- Faturas vinculadas a orçamentos via `budget_id` (nullable)
- Despesas podem ter parcelamento (`total_portion`, `current_portion`)
- Divisões: Divide despesas entre múltiplas faturas (`CreditCardInvoiceExpenseDivision`)

**PrepaidCard** + **PrepaidCardExtract** + **PrepaidCardExtractExpense**:
- Estrutura similar aos cartões de crédito
- Extratos vinculados a orçamentos

**FixExpense** (despesas fixas):
- Template de despesas incluídas ao criar orçamentos
- `due_date`: Dia do mês (1-31)

**Provision** (provisionamentos):
- Provisionamentos padrão incluídos em novos orçamentos
- Grupos: 'GENERAL', 'INVESTIMENT', 'TRIP', 'OTHERS'

**Financing** + **FinancingInstallment**:
- Financiamentos de longo prazo com parcelas mensais

**Tag** (polimórfico):
- Tags reutilizáveis para despesas, provisionamentos, metas

### Padrão de Usuário Compartilhado

Muitas entidades suportam divisão de custos via `share_value` e `share_user_id` (referencia modelo `People`).

## Fluxo de Desenvolvimento

### Configuração Inicial (Primeira Vez)
```bash
./start.sh setup                    # Configuração única do ambiente
code to-the-future.code-workspace   # Abrir workspace
./scripts/artisan.sh migrate        # Executar migrations
./scripts/composer.sh install       # Instalar dependências PHP
./scripts/npm.sh install && ./scripts/npm.sh run dev  # Build assets
```

### Desenvolvimento Diário
1. `./start.sh dev` (ou `dev-full` para hot reload)
2. Abrir workspace no VS Code
3. Acessar: `http://localhost:8080` (app), `http://localhost:5173` (vite HMR)
4. DB: localhost:3307, user: admin, pass: root1234

### Suporte da IDE

**PHP IntelliSense:**
- Laravel IDE Helper gera `_ide_helper.php`, `_ide_helper_models.php`
- Execute `./start.sh ide-helper` para regenerar após mudanças nos models
- Intelephense configurado nas settings do workspace

**Auto-formatação:**
- PHP CS Fixer: `./scripts/php-cs-fixer.sh fix` (segue estilo Laravel do `pint.json`)
- ESLint + Prettier para JS/Vue: `./scripts/npm.sh run format`

## Convenções de Roteamento

**Rotas web** (`routes/web.php`):
- Resource routes: `Route::resource('/people', PeopleController::class)`
- Grupos de controller: `Route::controller(BudgetController::class)->group(function () { ... })`
- Renders Inertia: Controllers retornam `Inertia::render('Budget/Show', $data)`
- Sem rotas de API - Inertia trata AJAX de forma transparente

**Rotas nomeadas:**
- `route('budget.show', ['id' => 123])` → `/budget/show/123`
- `to_route('budget.show', ['id' => $budget->id])` para redirects

## Padrões e Convenções Comuns

### Chaves de Tradução
- Mensagens de sucesso do controller: `'default.sucess-save'`, `'default.sucess-update'`, `'default.sucess-delete'`
- Mensagens de exceção: `'budget.not-found'`, `'budget.already-exists'`

### Manipulação de Datas
- Carbon para PHP: `Carbon::parse($year . '-' . $month . '-01')`
- Moment.js para frontend (importado globalmente)

### Relacionamentos de Model
- Definir nos models com `hasMany`, `belongsTo`, `morphToMany` (tags)
- Eager load via repository: `$this->budgetRepository->show($id, ['expenses', 'incomes'])`

### Validação de Formulários
- Nível do controller: `$this->validate($request, ['field' => ['required']])`
- Validação de negócio no service: lance exceptions

### Operações de Clone/Cópia
- Clonagem de budget inclui seleção de quais relações copiar
- Veja `BudgetService::clone()` para lógica complexa de clonagem com ajustes de datas Carbon

### Migrations
- Migrations são desenvolvidas à medida que as necessidades surgem
- Não há convenção pré-estabelecida de estrutura ou naming específico
- Use `./scripts/artisan.sh make:migration nome_da_migration` para criar novas

## Debugging

### Logs
```bash
./start.sh logs                      # Todos os containers
docker logs to-the-future-php-1      # Container específico
storage/logs/laravel.log             # Logs da aplicação Laravel
```

### Xdebug
Configurado no `Dockerfile.dev`. Launch config do VS Code nas settings do workspace.

### Acesso ao Banco de Dados
DBeaver/MySQL Workbench: localhost:3307, admin/root1234, database: laravel

## Estilo de Código

**PHP:**
- Estilo Laravel Pint (baseado em PSR-12, veja `pint.json`)
- Type hints obrigatórios: `public function store(Request $request): Model`
- Docblocks para métodos públicos

**Vue:**
- Options API (não Composition API)
- ESLint + Prettier configurados (`eslint.config.cjs`)
- Temporariamente desabilitado no Vite por questões de performance

## Testes

**Status atual:** Testes não foram implementados ainda.
- PHPUnit está configurado (`phpunit.xml`) para quando forem necessários
- Estrutura de testes disponível em `tests/Feature` e `tests/Unit`
- Para executar (quando implementados): `docker compose exec php php artisan test`

## Arquivos Chave de Referência

- **Docker**: `docker-compose.yml`, `Dockerfile.dev`, `start.sh`
- **Configuração Laravel**: `config/app.php`, `config/database.php`
- **Bindings de DI**: `app/Providers/AppServiceProvider.php`, `app/Providers/HelperServiceProvider.php`
- **Rotas**: `routes/web.php`, `routes/auth.php`
- **Entry Point Frontend**: `resources/js/app.js`
- **Configuração Vite**: `vite.config.js`
- **Repository Base**: `app/Repositories/AppRepository.php`
- **Workspace**: `to-the-future.code-workspace`
