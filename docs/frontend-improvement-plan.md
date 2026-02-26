# Plano de Melhorias Frontend — Vue.js 3 & Inertia.js

> Documento gerado em: 20/02/2026  
> Escopo: Análise completa do frontend (Vue 3 + Inertia.js v2 + Vuetify 3)  
> Total de arquivos analisados: ~45 componentes Vue, plugins, utils, configs e layouts

---

## Índice

1. [Resumo Executivo](#1-resumo-executivo)
2. [Bugs Críticos (Correção Imediata)](#2-bugs-críticos-correção-imediata)
3. [Fase 1 — Correções Rápidas e Quick Wins](#3-fase-1--correções-rápidas-e-quick-wins)
4. [Fase 2 — Eliminação de Duplicação (Composables)](#4-fase-2--eliminação-de-duplicação-composables)
5. [Fase 3 — Modernização do Inertia.js v2](#5-fase-3--modernização-do-inertiaj-v2)
6. [Fase 4 — Padronização de API Style (Vue 3)](#6-fase-4--padronização-de-api-style-vue-3)
7. [Fase 5 — Componentização e Separação de Responsabilidades](#7-fase-5--componentização-e-separação-de-responsabilidades)
8. [Fase 6 — Performance e Bundle Size](#8-fase-6--performance-e-bundle-size)
9. [Fase 7 — Qualidade de Código e DX](#9-fase-7--qualidade-de-código-e-dx)
10. [Fase 8 — Acessibilidade](#10-fase-8--acessibilidade)
11. [Resumo de Impacto por Fase](#11-resumo-de-impacto-por-fase)
12. [Apêndice A — Inventário de Componentes](#12-apêndice-a--inventário-de-componentes)
13. [Apêndice B — Mapa de Duplicação de Código](#13-apêndice-b--mapa-de-duplicação-de-código)

---

## 1. Resumo Executivo

A análise identificou **62 pontos de melhoria** em 8 categorias. Os problemas mais impactantes:

| Categoria | Severidade | Qtd |
|-----------|:----------:|:---:|
| Bugs em URLs e dados | 🔴 Crítica | 6 |
| Duplicação massiva de código | 🔴 Alta | 9 |
| Padrões Inertia.js desatualizados | 🟠 Alta | 4 |
| Inconsistência de API Style (Vue 3) | 🟠 Média | 3 |
| Componentes monolíticos (900-1500 linhas) | 🟠 Média | 3 |
| Performance e bundle size | 🟡 Média | 6 |
| Qualidade de código | 🟡 Baixa | 12 |
| Acessibilidade | 🟡 Baixa | 7 |

**Estimativa total: 8 fases incrementais, aplicáveis independentemente.**

---

## 2. Bugs Críticos (Correção Imediata)

Estes devem ser corrigidos antes de qualquer refatoração:

### 2.1 URL com Dupla Barra — `BudgetExpenseTagOptions.vue`

**Arquivo:** `resources/js/Components/Budget/BudgetExpenseTagOptions.vue`  
**Problema:** O método `create()` envia POST para `'//budget-expense-tag-option'` (dupla barra).  
**Correção:** Remover a barra extra → `'/budget-expense-tag-option'`

### 2.2 URL Sem Barra — `BudgetExpense.vue`

**Arquivo:** `resources/js/Components/Budget/BudgetExpense.vue`  
**Problema:** URL da deleção de parcelas concatena sem separador:
```js
`/budget-expense/${this.deleteId}delete-all-portions`
```
**Correção:** Adicionar barra → `/budget-expense/${this.deleteId}/delete-all-portions`

### 2.3 `titleModal` Não Declarado em `data()` — 10+ arquivos

**Arquivos afetados:** Todos os Index.vue de CRUD + BudgetExpense, BudgetGoal, BudgetIncome, BudgetProvision, InvoiceExpense, ExtractExpense  
**Problema:** `this.titleModal` é atribuído em `newItem()` / `editItem()` mas nunca declarado em `data()`. No Vue 3, propriedades não declaradas em `data()` não são reativas.  
**Correção:** Adicionar `titleModal: ''` em todos os `data()`.

### 2.4 `<Head title>` Incorretos — 6 arquivos

| Arquivo | Valor Atual | Valor Correto |
|---------|-------------|---------------|
| `Pages/FixExpense/Index.vue` | `"Provision"` | `"Fix Expense"` |
| `Pages/Financing/Show.vue` | `"Provision"` | `"Financing"` |
| `Pages/PrepaidCard/Index.vue` | `"Credit Card"` | `"Prepaid Card"` |
| `Pages/PrepaidCardExtract/Index.vue` | `"Credit Card"` | `"Prepaid Card Extract"` |
| `Pages/PrepaidCardExtract/Show.vue` | `"Credit Card Invoice"` | `"Prepaid Card Extract"` |
| `Pages/Auth/Register.vue` | `"Log in"` | `"Register"` |

### 2.5 Ref Duplicada — `Budget/Index.vue`

**Problema:** Dois `v-date-input` diferentes com `ref="txtStartWeek1"`. Apenas o segundo será acessível via `this.$refs`.  
**Correção:** Corrigir os nomes dos refs para serem únicos.

### 2.6 Bug de Dado Duplicado — `FixExpense/Index.vue`

**Problema:** O array `dueDateList` tem o valor `'06'` duplicado.  
**Correção:** Remover a entrada duplicada.

### 2.7 `BudgetExpenseTags` Renderiza Duas Vezes — `Budget/Show.vue`

**Problema:** O componente `BudgetExpenseTags` aparece duplicado no template (linhas ~90 e ~97) com os mesmos props — provável copy-paste acidental.  
**Correção:** Remover a instância duplicada.

### 2.8 Name Collision — InvoiceExpense vs ExtractExpense

**Problema:** Ambos `InvoiceExpense.vue` e `ExtractExpense.vue` declaram `name: 'InvoiceExpenses'`.  
**Correção:** `ExtractExpense.vue` deve declarar `name: 'ExtractExpenses'`.

---

## 3. Fase 1 — Correções Rápidas e Quick Wins

**Esforço:** ~2-4 horas | **Impacto:** Alto | **Risco:** Baixo

### 3.1 Remover `console.log` de Produção

**Arquivos afetados:**
- `Components/Budget/BudgetExpense.vue` — 1 ocorrência
- `Pages/Budget/Show.vue` — 4 ocorrências
- `Components/PrepaidCardExtract/ExtractExpense.vue` — 2 ocorrências
- Todos os blocos `catch` de `searchTags()` / `searchDescriptions()` — ~9 ocorrências

**Ação:** Remover todos ou substituir por logger condicional:
```js
// utils/logger.js
export const logger = {
    error: (...args) => import.meta.env.DEV && console.error(...args),
    warn: (...args) => import.meta.env.DEV && console.warn(...args),
}
```

### 3.2 Remover Lifecycle Hooks Vazios

**Arquivos afetados:** 10+ páginas e componentes com:
```js
async created() {},
async mounted() {},
```
**Ação:** Remover completamente.

### 3.3 Corrigir Default de Props Mutáveis

**Arquivos afetados:** BudgetExpense, BudgetIncome, BudgetProvision  
**Problema:**
```js
expenses: { type: Array, default: new Array() }  // ❌ Referência compartilhada
```
**Correção:**
```js
expenses: { type: Array, default: () => [] }  // ✅ Factory function
```

### 3.4 Adicionar `required` e `default` em Props

**Todos os componentes autenticados** devem ter props completas:
```js
// ❌ Atual
props: {
    budgets: Array,
    year: String,
}

// ✅ Corrigido
props: {
    budgets: { type: Array, required: true, default: () => [] },
    year: { type: String, required: true },
}
```

### 3.5 Padronizar Import Paths com Alias `@/`

**Problema:** Mistura de `@/` e `../../`:
```js
import BudgetGoal from '../../Components/Budget/BudgetGoal.vue'  // ❌
import Breadcrumbs from '@/Components/Breadcrumbs.vue'            // ✅
```
**Ação:** Converter todos para `@/`.

### 3.6 Remover Registro Redundante de Componentes

**Arquivo:** `Pages/Budget/Show.vue`  
**Problema:** Componentes importados no `<script setup>` são auto-registrados; o bloco `components: {}` no Options API é redundante.  
**Ação:** Remover o bloco `components` ou mover os imports para o `<script>` Options API.

### 3.7 Remover Código Morto

- `Configs/navigation.js` — exporta `listMenu` que nunca é importado. `NavigationMenu.vue` hardcoda seu próprio menu.
  - **Ação:** Fazer `NavigationMenu.vue` importar de `navigation.js` **OU** deletar `navigation.js`.
- `Components/Dashboard/GroupResumes.vue` — componente stub incompleto.
  - **Ação:** Completar ou remover.
- `CreditCardInvoice/Index.vue` — importa `MONTHS` para `listMonths` mas nunca usa.

### 3.8 Corrigir Classes Vuetify 2 Depreciadas

**Problema:** Classes de texto no padrão Vuetify 2:
```html
class="green--text"   <!-- ❌ Vuetify 2 -->
class="text-green"    <!-- ✅ Vuetify 3 -->
```
**Arquivos:** Buscar globalmente por padrão `--text` em templates e substituir.

---

## 4. Fase 2 — Eliminação de Duplicação (Composables)

**Esforço:** ~8-12 horas | **Impacto:** Muito Alto | **Risco:** Médio

A duplicação de código é o problema mais grave do frontend. As mesmas funções estão copiadas em **7 a 12 arquivos**.

### 4.1 Criar Composable `useTagSearch()`

**Código duplicado em:** BudgetExpense, BudgetExpenseTagOptions, BudgetGoal, BudgetIncome, BudgetProvision, InvoiceExpense, ExtractExpense, FixExpense/Index, Provision/Index (9 arquivos)

```js
// resources/js/composables/useTagSearch.js
import { ref } from 'vue'

export function useTagSearch() {
    const tags = ref([])
    const tagSearch = ref(null)
    let timeout = null

    async function searchTags(val, existingTags = []) {
        if (!val || val.length < 2) return

        clearTimeout(timeout)
        timeout = setTimeout(async () => {
            try {
                const { data } = await window.axios.get(`/tag/search/${encodeURIComponent(val)}`)
                tags.value = data.filter(
                    tag => !existingTags.some(existing => existing.id === tag.id)
                )
            } catch (error) {
                if (import.meta.env.DEV) console.error('Tag search error:', error)
            }
        }, 300)
    }

    function clearTags() {
        tags.value = []
        tagSearch.value = null
    }

    return { tags, tagSearch, searchTags, clearTags }
}
```

### 4.2 Criar Composable `useDescriptionSearch()`

**Código duplicado em:** InvoiceExpense, ExtractExpense, BudgetProvision (3 arquivos)

```js
// resources/js/composables/useDescriptionSearch.js
import { ref } from 'vue'

export function useDescriptionSearch(entityType) {
    const descriptions = ref([])
    let timeout = null

    async function searchDescriptions(val) {
        if (!val || val.length < 2) return

        clearTimeout(timeout)
        timeout = setTimeout(async () => {
            try {
                const { data } = await window.axios.get(
                    `/${entityType}/search/${encodeURIComponent(val)}`
                )
                descriptions.value = data
            } catch (error) {
                if (import.meta.env.DEV) console.error('Description search error:', error)
            }
        }, 300)
    }

    return { descriptions, searchDescriptions }
}
```

### 4.3 Criar Composable `useShareCalculation()`

**Código duplicado em:** BudgetExpense, BudgetProvision, InvoiceExpense, ExtractExpense (4 arquivos)

```js
// resources/js/composables/useShareCalculation.js
export function useShareCalculation() {
    function calculateShareValue(value, shareValue) {
        if (!value || !shareValue) return 0
        return (parseFloat(value) * parseFloat(shareValue) / 100).toFixed(2)
    }

    return { calculateShareValue }
}
```

### 4.4 Criar Constantes Compartilhadas `useFormConstants()`

**Código duplicado em:** Todos os CRUD (10+ arquivos)

```js
// resources/js/composables/useFormConstants.js
import i18n from '@/Locales/i18n'

const { t } = i18n.global

export function useValidationRules() {
    return {
        textFieldRules: [(v) => !!v || t('default.required-field')],
        currencyFieldRules: [(v) => !!v || t('default.required-field')],
        booleanFieldRules: [(v) => v !== null || t('default.required-field')],
        selectFieldRules: [(v) => !!v || t('default.required-field')],
    }
}

export function useGroupList() {
    return [
        { name: t('default.monthly'), value: 'MONTHLY' },
        { name: t('default.week-1'), value: 'WEEK_1' },
        { name: t('default.week-2'), value: 'WEEK_2' },
        { name: t('default.week-3'), value: 'WEEK_3' },
        { name: t('default.week-4'), value: 'WEEK_4' },
    ]
}

export function useCurrencyConfig() {
    return {
        locale: 'pt-BR',
        prefix: 'R$',
        suffix: '',
        length: 11,
        precision: 2,
    }
}

export function useDaysList() {
    return Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0'))
}

export function useIsActiveOptions() {
    return [
        { name: t('default.yes'), value: 1 },
        { name: t('default.no'), value: 0 },
    ]
}
```

### 4.5 Criar Composable `useCrudOperations()`

**Código duplicado em:** Todos os Index.vue (9 arquivos) + todos os componentes CRUD

```js
// resources/js/composables/useCrudOperations.js
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCrudOperations(baseUrl) {
    const isLoading = ref(false)
    const editDialog = ref(false)
    const deleteId = ref(null)
    const titleModal = ref('')

    function create(data, options = {}) {
        isLoading.value = true
        router.post(baseUrl, data, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                editDialog.value = false
                options.onSuccess?.()
            },
            onError: () => { isLoading.value = false },
            onFinish: () => { isLoading.value = false },
        })
    }

    function update(id, data, options = {}) {
        isLoading.value = true
        router.put(`${baseUrl}/${id}`, data, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                editDialog.value = false
                options.onSuccess?.()
            },
            onError: () => { isLoading.value = false },
            onFinish: () => { isLoading.value = false },
        })
    }

    function remove(confirmRef) {
        isLoading.value = true
        router.delete(`${baseUrl}/${deleteId.value}`, {
            preserveState: true,
            preserveScroll: true,
            onError: () => { isLoading.value = false },
            onFinish: () => { isLoading.value = false },
        })
    }

    async function confirmRemove(item, confirmRef) {
        deleteId.value = item.id
        if (await confirmRef.open('', '', { color: 'red' })) {
            remove()
        }
    }

    async function save(formRef, entity, options = {}) {
        const validate = await formRef.validate()
        if (validate.valid) {
            if (entity.id) {
                update(entity.id, entity, options)
            } else {
                create(entity, options)
            }
        }
    }

    return {
        isLoading,
        editDialog,
        deleteId,
        titleModal,
        create,
        update,
        remove,
        confirmRemove,
        save,
    }
}
```

### 4.6 Criar Composable `useExcelImport()`

**Código duplicado em:** InvoiceExpense, ExtractExpense (2 arquivos, ~50 linhas cada)

### 4.7 Estrutura de Diretório Proposta

```
resources/js/
├── composables/              ← NOVO
│   ├── useCrudOperations.js
│   ├── useTagSearch.js
│   ├── useDescriptionSearch.js
│   ├── useShareCalculation.js
│   ├── useFormConstants.js
│   ├── useExcelImport.js
│   └── index.js              ← Re-exporta todos
```

---

## 5. Fase 3 — Modernização do Inertia.js v2

**Esforço:** ~6-8 horas | **Impacto:** Alto | **Risco:** Médio

### 5.1 Migrar `this.$inertia.*` para `router.*`

**Arquivos afetados:** Todas as 15 páginas autenticadas + 7 componentes CRUD

**Antes:**
```js
this.$inertia.post('/budget', this.budget, {
    preserveState: true,
    onSuccess: () => {},
})
```

**Depois:**
```js
import { router } from '@inertiajs/vue3'

router.post('/budget', this.budget, {
    preserveState: true,
    onSuccess: () => {},
})
```

> **Nota:** Se a migração para Composition API (Fase 4) for feita em paralelo, usar `router` diretamente dentro dos composables.

### 5.2 Implementar `useForm()` para Formulários CRUD

**Todos os formulários** atualmente usam `data()` manual com `isLoading` manual. O `useForm()` do Inertia v2 fornece:
- `form.processing` (substitui `isLoading`)
- `form.errors` (erros de validação automáticos)
- `form.isDirty` (controle de mudanças)
- `form.reset()` (reset para estado inicial)
- `form.clearErrors()`

**Antes:**
```js
data() {
    return {
        budget: { year: '', month: '' },
        isLoading: false,
    }
},
methods: {
    create() {
        this.isLoading = true
        this.$inertia.post('/budget', this.budget, {
            onFinish: () => { this.isLoading = false }
        })
    }
}
```

**Depois (Composition API):**
```js
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    year: '',
    month: '',
})

function create() {
    form.post('/budget', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}
```

**Depois (Options API — se preferir manter):**
```js
import { useForm } from '@inertiajs/vue3'

export default {
    setup() {
        const form = useForm({ year: '', month: '' })
        return { form }
    },
    methods: {
        create() {
            this.form.post('/budget', {
                preserveScroll: true,
                onSuccess: () => this.form.reset(),
            })
        }
    }
}
```

### 5.3 Implementar Persistent Layout

**Problema:** Cada página monta/desmonta o `AuthenticatedLayout` em cada navegação, causando re-renderização desnecessária do menu, navbar e sidebar.

**Antes:**
```vue
<template>
    <AuthenticatedLayout>
        <!-- conteúdo da página -->
    </AuthenticatedLayout>
</template>
```

**Depois:**
```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
    <!-- conteúdo da página diretamente, sem wrapper -->
</template>
```

**Arquivos afetados:** Todas as 15 páginas autenticadas.

**Benefícios:**
- Layout mantém estado entre navegações
- Não re-monta sidebar/navbar
- Scroll do layout é preservado
- Melhor performance percebida

### 5.4 Considerar Rotas Nomeadas com Ziggy

**Problema:** Todas as URLs são strings hardcoded:
```js
this.$inertia.get('/budget/' + value.target.value)
href: '/dashboard'
this.$inertia.post('/credit-card', ...)
```

**Solução:** Instalar o pacote Ziggy para usar rotas nomeadas do Laravel no frontend:
```bash
composer require tightenco/ziggy
npm install ziggy-js
```

**Depois:**
```js
import { route } from 'ziggy-js'

router.get(route('budget.show', { id: value }))
```

**Benefícios:**
- URLs sempre sincronizadas com `routes/web.php`
- Refatoração de rotas não quebra frontend
- Autocomplete de nomes de rotas

> **Nota:** Esta é uma melhoria opcional de longo prazo. As URLs hardcoded funcionam, mas são frágeis.

---

## 6. Fase 4 — Padronização de API Style (Vue 3)

**Esforço:** ~12-16 horas | **Impacto:** Médio | **Risco:** Médio-Alto

### 6.1 Situação Atual: Padrão Híbrido

Quase todos os componentes usam **dois blocos `<script>`**:
```vue
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
</script>

<script>
export default {
    components: { /* redundante! */ },
    props: { ... },
    data() { ... },
    methods: { ... },
}
</script>
```

Os imports no `<script setup>` auto-registram componentes, tornando o bloco `components: {}` redundante.

### 6.2 Opção A — Padronizar como Options API Puro

Mover todos os imports para dentro do Options API `<script>`:

```vue
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

export default {
    components: { AuthenticatedLayout, Head },
    props: { ... },
    // ...
}
</script>
```

**Prós:** Consistência sem reescrita de lógica  
**Contras:** Perde auto-import; não aproveita Vue 3 features

### 6.3 Opção B — Migrar para Composition API com `<script setup>` (Recomendado)

```vue
<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useTagSearch } from '@/composables/useTagSearch'
import { useValidationRules } from '@/composables/useFormConstants'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    budgets: { type: Array, required: true, default: () => [] },
    year: { type: String, required: true },
})

const { tags, searchTags } = useTagSearch()
const { textFieldRules } = useValidationRules()

const form = useForm({ year: '', month: '' })
const editDialog = ref(false)

function save() {
    form.post('/budget', { preserveScroll: true })
}
</script>
```

**Prós:**
- Vue 3 idiomático, melhor tree-shaking
- Composables nativos sem adaptação
- Melhor inferência de tipos para futuro TypeScript
- Bundle menor (runtime Composition API é mais leve)

**Contras:**
- Reescrita significativa de cada componente
- Curva de aprendizado para quem só conhece Options API

### 6.4 Estratégia de Migração Recomendada

**Migrar incrementalmente, um módulo por vez:**

1. Começar por componentes menores (Breadcrumbs, BarChart, etc.)
2. Depois componentes Budget (Budget/Show.vue + filhos)
3. Depois páginas Index (CRUD simples)
4. Por último os componentes grandes (InvoiceExpense, BudgetExpense)

**Regra:** Nunca deixar o mesmo módulo com metade Options API e metade Composition API.

---

## 7. Fase 5 — Componentização e Separação de Responsabilidades

**Esforço:** ~10-14 horas | **Impacto:** Alto | **Risco:** Médio

### 7.1 Dividir `InvoiceExpense.vue` (~1500 linhas)

**Componente mais crítico.** Proposta de divisão:

```
Components/CreditCardInvoice/
├── InvoiceExpense.vue              ← Orchestrator (~200 linhas)
├── InvoiceExpenseTable.vue         ← Data table + search bar
├── InvoiceExpenseFormDialog.vue    ← Create/edit dialog
├── InvoiceExpenseDivision.vue      ← Sub-dialog de divisões
├── InvoiceExpenseImport.vue        ← Import Excel logic + dialog
└── InvoiceExpenseDeleteDialog.vue  ← Confirmação de exclusão
```

### 7.2 Dividir `BudgetExpense.vue` (~920 linhas)

```
Components/Budget/
├── BudgetExpense.vue               ← Orchestrator
├── BudgetExpenseTable.vue          ← Data table com groups
├── BudgetExpenseFormDialog.vue     ← Create/edit dialog
└── BudgetExpenseDeleteDialog.vue   ← Delete + delete-all-portions
```

### 7.3 Dividir `ExtractExpense.vue` (~920 linhas)

Mesma estrutura do InvoiceExpense (são quase idênticos).

### 7.4 Extrair Componente `DataTableToolbar`

**Duplicado em 10+ arquivos:**
```vue
<!-- components/DataTableToolbar.vue -->
<template>
    <v-toolbar flat color="white">
        <v-toolbar-title class="text-subtitle-2 text-grey">
            {{ title }}
        </v-toolbar-title>
        <v-spacer />
        <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            :label="$t('default.search')"
            single-line
            hide-details
            density="compact"
        />
        <v-divider class="mx-4" inset vertical />
        <slot name="actions" />
    </v-toolbar>
</template>
```

### 7.5 Extrair Componente `FormDialog`

**Padrão duplicado em todos os CRUD:**
```vue
<!-- components/FormDialog.vue -->
<template>
    <v-dialog v-model="modelValue" persistent :max-width="maxWidth">
        <v-card>
            <v-card-title>{{ title }}</v-card-title>
            <v-card-text>
                <v-form ref="form">
                    <slot />
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn @click="$emit('cancel')">{{ $t('default.cancel') }}</v-btn>
                <v-btn color="primary" :loading="loading" @click="$emit('save')">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
```

### 7.6 Deduplificar Budget/Show.vue Computeds

**Problema:** `budgetWeeks` e `budgetShareWeeks` são ~40 linhas idênticas cada, repetidas com `this.owner` vs `this.share`. O mesmo para `budgetWeek1/2/3/4` vs `budgetShareWeek1/2/3/4`.

**Solução:** Função parametrizada:
```js
function buildWeeks(data) {
    return {
        weekly: computed(() => filterByGroup(data, ['WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4'])),
        week1: computed(() => filterByGroup(data, ['WEEK_1'])),
        week2: computed(() => filterByGroup(data, ['WEEK_2'])),
        week3: computed(() => filterByGroup(data, ['WEEK_3'])),
        week4: computed(() => filterByGroup(data, ['WEEK_4'])),
    }
}

const ownerWeeks = buildWeeks(owner)
const shareWeeks = buildWeeks(share)
```

### 7.7 Extrair Componente `WeekDateInputs` para Budget/Index.vue

**Problema:** O template de `Budget/Index.vue` repete 8 blocos de `v-date-input` (4 semanas × start/end).  
**Solução:** Extrair para componente com loop:
```vue
<WeekDateInputs v-model="budget" :weeks="4" />
```

---

## 8. Fase 6 — Performance e Bundle Size

**Esforço:** ~4-6 horas | **Impacto:** Médio | **Risco:** Baixo

### 8.1 Vuetify Tree-Shaking

**Problema atual em `vuetify.js`:**
```js
import * as components from 'vuetify/components'       // ❌ Importa TUDO
import * as labsComponents from 'vuetify/labs/components' // ❌ Importa TUDO
```

**Solução:** O `vite-plugin-vuetify` com `autoImport: true` já está configurado no `vite.config.js`. Basta remover os imports manuais:

```js
// vuetify.js — CORRIGIDO
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import MomentAdapter from '@date-io/moment'

const vuetify = createVuetify({
    // Sem components/directives — auto-importados pelo plugin Vite
    icons: { defaultSet: 'mdi', aliases, sets: { mdi } },
    theme: { defaultTheme: 'light' },
    date: { adapter: MomentAdapter, locale: { pt: 'pt' } },
})

export default vuetify
```

**Impacto estimado:** Redução de 200-400KB no bundle.

### 8.2 Substituir Moment.js por date-fns ou Day.js

**Problema:** `moment.js` tem ~330KB e está em modo de manutenção.

**Opções:**
| Biblioteca | Tamanho | Tree-Shakeable | API Familiar |
|-----------|---------|:-:|:-:|
| moment.js (atual) | ~330KB | ❌ | - |
| day.js | ~7KB | ✅ | ✅ (mesma API) |
| date-fns | ~13KB (usado) | ✅ | ❌ |

**Recomendação:** `Day.js` por ter API quase idêntica ao moment.js, minimizando mudanças.

> **Nota:** Isso requer também trocar o `@date-io/moment` do Vuetify por `@date-io/dayjs`.

### 8.3 Lazy Loading de Componentes Pesados

```js
// Carregar ApexCharts apenas quando necessário
const BarChart = defineAsyncComponent(() => import('@/Components/BarChart.vue'))
```

### 8.4 Componente Global `ConfirmDialog`

**Situação:** Registrado globalmente em `app.js` E instanciado manualmente em cada página.  
**Melhoria:** Mover para o `AuthenticatedLayout.vue` e acessar via `provide/inject`:

```vue
<!-- AuthenticatedLayout.vue -->
<template>
    <ConfirmDialog ref="confirmDialog" />
    <slot />
</template>

<script setup>
import { provide, ref } from 'vue'
const confirmDialog = ref()
provide('confirm', confirmDialog)
</script>
```

```vue
<!-- Qualquer página -->
<script setup>
const confirm = inject('confirm')

async function handleDelete(item) {
    if (await confirm.value.open('', '', { color: 'red' })) {
        router.delete(`/entity/${item.id}`)
    }
}
</script>
```

### 8.5 Build Target

**Atual:** `target: 'es2015'` (ES6)  
**Sugestão:** Atualizar para `'es2020'` ou `'esnext'` para gerar código mais moderno e menor, se não houver necessidade de suportar navegadores antigos.

### 8.6 Habilitar Source Maps em Dev

**Atual:** `sourcemap: false` (mesmo em dev via build config)  
**Sugestão:** Habilitar em development para debugging:
```js
build: {
    sourcemap: !isProduction,
}
```

---

## 9. Fase 7 — Qualidade de Código e DX

**Esforço:** ~4-6 horas | **Impacto:** Médio | **Risco:** Baixo

### 9.1 Reabilitar ESLint no Vite

**Status:** Comentado em `vite.config.js` ("temporariamente desabilitado")  
**Ação:** Mover linting para script separado ou CI/CD em vez do plugin Vite:

```json
// package.json
"scripts": {
    "lint": "eslint 'resources/js/**/*.{js,vue}' --fix",
    "lint:check": "eslint 'resources/js/**/*.{js,vue}'"
}
```

### 9.2 Configurar Husky + lint-staged

Prevenir código sem lint de ser commitado:
```bash
npm install -D husky lint-staged
```

### 9.3 Debounce Utility Proper

**Problema:** `setTimeout` manual em `searchTags()` com variável `this.timeOut` não declarada.  
**Solução:** Usar utilitário de debounce:

```js
// utils/debounce.js
export function debounce(fn, delay = 300) {
    let timer
    return (...args) => {
        clearTimeout(timer)
        timer = setTimeout(() => fn(...args), delay)
    }
}
```

### 9.4 Codificar URLs de Busca

**Problema:**
```js
window.axios.get('/tag/search/' + val)  // ❌ val pode ter caracteres especiais
```
**Correção:**
```js
window.axios.get(`/tag/search/${encodeURIComponent(val)}`)  // ✅
```

### 9.5 Melhorar Tratamento de Flash Messages

**Problema em `AuthenticatedLayout.vue`:**
- Instancia `useToast()` dentro do watcher (deve ser no setup)
- Não faz null-check em `this.$page.props.errors`

**Correção:**
```js
setup() {
    const toast = useToast()
    return { toast }
},
watch: {
    '$page.props.flash'(flash) {
        if (flash?.success) this.toast.success(this.$t(flash.success))
        if (flash?.error) this.toast.error(this.$t(flash.error))
    },
    '$page.props.errors'(errors) {
        if (errors?.error) this.toast.error(this.$t(errors.error))
    },
}
```

### 9.6 Variáveis de Timeout como Propriedades Reativas

**Problema:** `this.timeOut` usado em `searchTags()` nunca é declarado em `data()`.  
**Ação:** Se mantiver Options API, declarar `timeOut: null` em `data()` para cada componente que usa debounce. Se usar composables (Fase 2), isso é resolvido automaticamente.

---

## 10. Fase 8 — Acessibilidade

**Esforço:** ~6-8 horas | **Impacto:** Baixo-Médio | **Risco:** Baixo

### 10.1 ARIA Labels em Botões de Ação

```vue
<!-- ❌ Atual -->
<v-icon @click="editItem(item)">mdi-pencil</v-icon>

<!-- ✅ Acessível -->
<v-btn icon variant="text" :aria-label="$t('default.edit')" @click="editItem(item)">
    <v-icon>mdi-pencil</v-icon>
</v-btn>
```

### 10.2 `aria-modal` em Diálogos

O Vuetify 3 `v-dialog` já possui `role="dialog"` e `aria-modal`, mas o `ConfirmDialog` pode se beneficiar de:
- `aria-labelledby` apontando para o título
- Focus trap automático (Vuetify já faz, verificar se está ativo)

### 10.3 Skip-to-Content Link

Adicionar no `AuthenticatedLayout.vue`:
```html
<a href="#main-content" class="skip-link">Pular para conteúdo</a>
```

### 10.4 Semântica de Footer em Tabelas

```vue
<!-- ❌ Atual: <th> em <tfoot> -->
<tfoot><tr><th>Total</th><th>{{ total }}</th></tr></tfoot>

<!-- ✅ Correto -->
<tfoot><tr><td>Total</td><td>{{ total }}</td></tr></tfoot>
```

### 10.5 Botão de Logout vs Link

**Problema:** `NavigationMenu.vue` usa `<Link>` para logout (que é uma ação POST).  
**Melhoria:** Usar `<form>` com `<button>` ou `router.post()`.

---

## 11. Resumo de Impacto por Fase

| Fase | Descrição | Esforço | Impacto | Risco | Dependências |
|:----:|-----------|:-------:|:-------:|:-----:|:------------:|
| 🔴 | Bugs Críticos | 1-2h | Crítico | Baixo | Nenhuma |
| 1 | Quick Wins | 2-4h | Alto | Baixo | Nenhuma |
| 2 | Composables (DRY) | 8-12h | Muito Alto | Médio | Nenhuma |
| 3 | Inertia.js v2 Modernização | 6-8h | Alto | Médio | Fase 2 (opcional) |
| 4 | API Style Padronização | 12-16h | Médio | Médio-Alto | Fases 2 e 3 |
| 5 | Componentização | 10-14h | Alto | Médio | Fase 2 (recomendado) |
| 6 | Performance/Bundle | 4-6h | Médio | Baixo | Nenhuma |
| 7 | Qualidade/DX | 4-6h | Médio | Baixo | Nenhuma |
| 8 | Acessibilidade | 6-8h | Baixo-Médio | Baixo | Nenhuma |

**Ordem recomendada:** Bugs → Fase 1 → Fase 6 → Fase 2 → Fase 3 → Fase 5 → Fase 7 → Fase 4 → Fase 8

**Tempo total estimado:** ~55-78 horas de desenvolvimento

---

## 12. Apêndice A — Inventário de Componentes

### Páginas (Pages/)

| Componente | Linhas | API Style | `useForm` | `router` | Persistent Layout |
|-----------|:------:|:---------:|:---------:|:--------:|:-----------------:|
| Dashboard.vue | ~25 | Hybrid | ❌ | ❌ | ❌ |
| Welcome.vue | ~15 | Hybrid | ❌ | ❌ | ❌ |
| Budget/Index.vue | ~743 | Hybrid | ❌ | ❌ | ❌ |
| Budget/Show.vue | ~517 | Hybrid | ❌ | ❌ | ❌ |
| CreditCard/Index.vue | ~460 | Hybrid | ❌ | ❌ | ❌ |
| CreditCardInvoice/Index.vue | ~440 | Hybrid | ❌ | ❌ | ❌ |
| CreditCardInvoice/Show.vue | ~200 | Hybrid | ❌ | ❌ | ❌ |
| Financing/Index.vue | ~480 | Hybrid | ❌ | ❌ | ❌ |
| Financing/Show.vue | ~475 | Hybrid | ❌ | ❌ | ❌ |
| FixExpense/Index.vue | ~545 | Hybrid | ❌ | ❌ | ❌ |
| PrepaidCard/Index.vue | ~430 | Hybrid | ❌ | ❌ | ❌ |
| PrepaidCardExtract/Index.vue | ~480 | Hybrid | ❌ | ❌ | ❌ |
| PrepaidCardExtract/Show.vue | ~400 | Hybrid | ❌ | ❌ | ❌ |
| Provision/Index.vue | ~564 | Hybrid | ❌ | ❌ | ❌ |
| Tag/Index.vue | ~305 | Hybrid | ❌ | ❌ | ❌ |

### Componentes (Components/)

| Componente | Linhas | API Style | Observação |
|-----------|:------:|:---------:|------------|
| ApplicationLogo.vue | ~10 | Template-only | OK |
| VuetifyLogo.vue | ~10 | Template-only | OK |
| BarChart.vue | ~27 | Options API | Sem lazy load |
| Breadcrumbs.vue | ~27 | Hybrid | OK |
| ConfirmDialog.vue | ~66 | Options API | OK |
| NavigationMenu.vue | ~68 | Hybrid | Ignora navigation.js |
| BudgetResume.vue | ~105 | Hybrid | OK |
| BudgetExpenseTags.vue | ~155 | Hybrid | Empty methods |
| BudgetIncome.vue | ~345 | Hybrid | Mutable default |
| BudgetExpenseTagOptions.vue | ~507 | Hybrid | Bug URL dupla barra |
| BudgetGoal.vue | ~554 | Hybrid | Duplicação searchTags |
| BudgetProvision.vue | ~702 | Hybrid | Mutable default, duplicação |
| BudgetExpense.vue | ~920 | Hybrid | Bug URL, mutable default |
| ExtractExpense.vue | ~921 | Hybrid | Name collision |
| InvoiceExpense.vue | ~1499 | Hybrid | Maior componente, deve dividir |
| GroupResumes.vue | ~38 | Hybrid | Stub incompleto |

---

## 13. Apêndice B — Mapa de Duplicação de Código

```
┌─────────────────────────────┬───────────────────────────────────────────────────────────┐
│ Código Duplicado             │ Arquivos Afetados                                         │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ searchTags()                │ BudgetExpense, BudgetExpenseTagOptions, BudgetGoal,       │
│                             │ BudgetIncome, BudgetProvision, InvoiceExpense,            │
│                             │ ExtractExpense, FixExpense/Index, Provision/Index (9)      │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ searchDescriptions()        │ InvoiceExpense, ExtractExpense, BudgetProvision (3)        │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ calculeShareValue()         │ BudgetExpense, BudgetProvision, InvoiceExpense,            │
│                             │ ExtractExpense (4)                                         │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ convertGroup()              │ BudgetExpense, BudgetGoal, BudgetIncome,                   │
│                             │ BudgetProvision, InvoiceExpense, ExtractExpense (6)         │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ Validation rules            │ Todos os CRUD (10+)                                        │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ vuetify-money config        │ Todos os forms com currency (10+)                          │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ groupList data              │ BudgetExpense, BudgetGoal, BudgetIncome,                   │
│                             │ BudgetProvision, InvoiceExpense, ExtractExpense (6)         │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ remove() / confirmRemove()  │ Todos os Index.vue (9) + componentes CRUD                  │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ save() form validation      │ Todos os CRUD (12+)                                        │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ DataTable toolbar template  │ Todos os Index.vue (10+)                                   │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ Dialog structure template   │ Todos os CRUD forms (12+)                                  │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ Breadcrumbs data            │ Todas as páginas autenticadas (15)                          │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ days/dueDateList array      │ CreditCard/Index, FixExpense/Index (2)                      │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ isActiveOptions             │ CreditCard/Index, PrepaidCard/Index (2)                     │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ budgetWeeks/budgetShareWeeks│ Budget/Show.vue internamente (4 computed × 2 = 8 blocos)    │
├─────────────────────────────┼───────────────────────────────────────────────────────────┤
│ Excel import logic          │ InvoiceExpense, ExtractExpense (2)                          │
└─────────────────────────────┴───────────────────────────────────────────────────────────┘
```

---

*Este documento serve como guia de referência para melhorias incrementais. Cada fase pode ser executada independentemente, embora a ordem recomendada maximize os benefícios.*
