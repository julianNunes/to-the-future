import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import BudgetExpense from '../BudgetExpense.vue'
import BudgetIncome from '../BudgetIncome.vue'
import BudgetProvision from '../BudgetProvision.vue'

const inertiaState = vi.hoisted(() => ({
    forms: [],
    router: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}))

function createMockForm(initialValues) {
    return {
        ...initialValues,
        errors: {},
        processing: false,
        submittedData: null,
        lastUrl: null,
        _transformer: null,
        post: vi.fn(function post(url, options = {}) {
            this.lastUrl = url
            this.submittedData = this._transformer ? this._transformer({ ...this }) : { ...this }
            options.onSuccess?.()
            options.onFinish?.()
        }),
        put: vi.fn(function put(url, options = {}) {
            this.lastUrl = url
            this.submittedData = this._transformer ? this._transformer({ ...this }) : { ...this }
            options.onSuccess?.()
            options.onFinish?.()
        }),
        transform: vi.fn(function transform(callback) {
            this._transformer = callback
            return this
        }),
        clearErrors: vi.fn(function clearErrors() {
            this.errors = {}
        }),
    }
}

vi.mock('@inertiajs/vue3', () => ({
    router: inertiaState.router,
    useForm: (initialValues) => {
        const form = createMockForm(initialValues)
        inertiaState.forms.push(form)
        return form
    },
}))

vi.mock('vue-i18n', async () => {
    const actual = await vi.importActual('vue-i18n')

    return {
        ...actual,
        useI18n: () => ({
            t: (key) => key,
        }),
    }
})

vi.mock('@/Components/ConfirmDialog.vue', () => ({
    default: { name: 'ConfirmDialog', template: '<div />' },
}))

vi.mock('@/composables/useCrudOperations.js', () => ({
    useCrudOperations: () => ({
        isLoading: ref(false),
        editDialog: ref(false),
        titleModal: ref(''),
    }),
}))

vi.mock('@/composables/useTagSearch.js', () => ({
    useTagSearch: () => ({
        tags: ref([]),
        isSearching: ref(false),
        searchTags: vi.fn(),
    }),
}))

vi.mock('@/composables/useDescriptionSearch.js', () => ({
    useDescriptionSearch: () => ({
        descriptions: ref([]),
        isSearching: ref(false),
        searchDescriptions: vi.fn(),
    }),
}))

vi.mock('@/composables/useShareCalculation.js', () => ({
    useShareCalculation: () => ({
        calculateShareValue: vi.fn(),
    }),
}))

vi.mock('@/composables/useFormConstants.js', () => ({
    useValidationRules: () => ({
        textFieldRules: [() => true],
        currencyFieldRules: [() => true],
    }),
}))

vi.mock('@/utils/utils.js', () => ({
    currencyField: (value) => String(value ?? ''),
    formatDate: (value) => value,
    reverseFormatNumber: (value) => Number(value || 0),
    sumField: () => 0,
    sumGroup: () => 0,
}))

const globalStubs = {
    'vuetify-money': true,
    'v-expansion-panels': true,
    'v-expansion-panel': true,
    'v-expansion-panel-title': true,
    'v-expansion-panel-text': true,
    'v-toolbar': true,
    'v-row': true,
    'v-col': true,
    'v-btn': true,
    'v-icon': true,
    'v-tooltip': true,
    'v-data-table': true,
    'v-chip': true,
    'v-dialog': true,
    'v-card': true,
    'v-card-title': true,
    'v-card-text': true,
    'v-form': true,
    'v-card-actions': true,
    'v-spacer': true,
    'v-text-field': true,
    'v-date-input': true,
    'v-select': true,
    'v-autocomplete': true,
    'v-list-item': true,
}

function mountBudgetExpense() {
    return shallowMount(BudgetExpense, {
        props: {
            budgetId: 7,
            yearMonth: '2026-05',
            expenses: [],
            shareUsers: [{ share_user_id: 9, share_user_name: 'Partner User' }],
            installments: [],
            viewOnly: false,
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

function mountBudgetIncome() {
    return shallowMount(BudgetIncome, {
        props: {
            budgetId: 7,
            yearMonth: '2026-05',
            incomes: [],
            viewOnly: false,
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

function mountBudgetProvision() {
    return shallowMount(BudgetProvision, {
        props: {
            budgetId: 7,
            yearMonth: '2026-05',
            provisions: [],
            shareUsers: [{ share_user_id: 9, share_user_name: 'Partner User' }],
            budgetWeeks: [],
            viewOnly: false,
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Budget subflow forms', () => {
    beforeEach(() => {
        inertiaState.forms.length = 0
        inertiaState.router.get.mockReset()
        inertiaState.router.post.mockReset()
        inertiaState.router.put.mockReset()
        inertiaState.router.delete.mockReset()
    })

    it('submits BudgetExpense create payload through useForm', async () => {
        const wrapper = mountBudgetExpense()

        wrapper.vm.newItem()
        wrapper.vm.expense.description = 'Groceries'
        wrapper.vm.expense.date = '2026-05-10'
        wrapper.vm.expense.value = 120.5
        wrapper.vm.expense.portion = '2'
        wrapper.vm.expense.portion_total = '3'
        wrapper.vm.expense.group = 'MONTHLY'
        wrapper.vm.expense.paid = 1
        wrapper.vm.expense.remarks = 'Shared'
        wrapper.vm.expense.share_value = 60.25
        wrapper.vm.expense.share_user_id = 9
        wrapper.vm.expense.tags = [{ name: 'FOOD' }]

        await wrapper.vm.createData()

        expect(inertiaState.forms).toHaveLength(1)
        expect(inertiaState.forms[0].post).toHaveBeenCalledOnce()
        expect(inertiaState.forms[0].lastUrl).toBe('/budget-expense')
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            description: 'Groceries',
            date: '2026-05-10',
            value: 120.5,
            portion: 2,
            portion_total: 3,
            paid: true,
            group: 'MONTHLY',
            share_value: 60.25,
            share_user_id: 9,
            budget_id: 7,
        })
    })

    it('submits BudgetIncome update payload through useForm', async () => {
        const wrapper = mountBudgetIncome()

        wrapper.vm.editItem({
            id: 55,
            description: 'Salary',
            value: 2500,
            date: '2026-05-05',
            remarks: null,
            tags: [],
            budget_id: 7,
        })
        wrapper.vm.income.description = 'Updated Salary'
        wrapper.vm.income.date = '2026-05-06'
        wrapper.vm.income.value = 3200.45
        wrapper.vm.income.remarks = 'Main income'
        wrapper.vm.income.tags = [{ name: 'WORK' }]

        await wrapper.vm.updateData()

        expect(inertiaState.forms).toHaveLength(1)
        expect(inertiaState.forms[0].put).toHaveBeenCalledOnce()
        expect(inertiaState.forms[0].lastUrl).toBe('/budget-income/55')
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            description: 'Updated Salary',
            date: '2026-05-06',
            value: 3200.45,
            remarks: 'Main income',
            budget_id: 7,
        })
    })

    it('submits BudgetProvision update payload through useForm', async () => {
        const wrapper = mountBudgetProvision()

        wrapper.vm.editItem({
            id: 89,
            description: 'Emergency',
            value: 250,
            group: 'MONTHLY',
            remarks: null,
            share_value: null,
            share_user_id: null,
            tags: [],
        })
        wrapper.vm.provision.description = { description: 'Updated Emergency' }
        wrapper.vm.provision.value = 410.7
        wrapper.vm.provision.group = 'WEEK_2'
        wrapper.vm.provision.remarks = 'Shared'
        wrapper.vm.provision.share_value = 205.35
        wrapper.vm.provision.share_user_id = 9
        wrapper.vm.provision.tags = [{ name: 'SAFETY' }]

        await wrapper.vm.updateData()

        expect(inertiaState.forms).toHaveLength(1)
        expect(inertiaState.forms[0].put).toHaveBeenCalledOnce()
        expect(inertiaState.forms[0].lastUrl).toBe('/budget-provision/89')
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            description: 'Updated Emergency',
            value: 410.7,
            group: 'WEEK_2',
            remarks: 'Shared',
            share_value: 205.35,
            share_user_id: 9,
            budget_id: 7,
        })
    })
})
