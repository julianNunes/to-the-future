import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import BudgetShow from '../Show.vue'

const inertiaState = vi.hoisted(() => ({
    router: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div />' },
    router: inertiaState.router,
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

vi.mock('@/Layouts/AuthenticatedLayout.vue', () => ({
    default: { name: 'AuthenticatedLayout', template: '<div><slot /></div>' },
}))

vi.mock('@/Components/Breadcrumbs.vue', () => ({
    default: { name: 'Breadcrumbs', template: '<div />' },
}))

vi.mock('@/Components/ConfirmDialog.vue', () => ({
    default: { name: 'ConfirmDialog', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetExpense.vue', () => ({
    default: { name: 'BudgetExpense', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetExpenseTagOptions.vue', () => ({
    default: { name: 'BudgetExpenseTagOptions', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetExpenseTags.vue', () => ({
    default: { name: 'BudgetExpenseTags', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetGoal.vue', () => ({
    default: { name: 'BudgetGoal', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetIncome.vue', () => ({
    default: { name: 'BudgetIncome', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetProvision.vue', () => ({
    default: { name: 'BudgetProvision', template: '<div />' },
}))

vi.mock('@/Components/Budget/BudgetResume.vue', () => ({
    default: { name: 'BudgetResume', template: '<div />' },
}))

vi.mock('@/Components/CreditCardInvoice/InvoiceExpense.vue', () => ({
    default: { name: 'InvoiceExpense', template: '<div />' },
}))

vi.mock('@/Components/PrepaidCardExtract/ExtractExpense.vue', () => ({
    default: { name: 'ExtractExpense', template: '<div />' },
}))

const globalStubs = {
    'v-card': true,
    'v-row': true,
    'v-col': true,
    'v-text-field': true,
    'v-btn': true,
    'v-tabs': true,
    'v-tab': true,
    'v-window': true,
    'v-window-item': true,
}

function createBudget(id, userId = 1) {
    return {
        id,
        user_id: userId,
        year: '2026',
        month: '05',
        start_week_1: '2026-05-01',
        end_week_1: '2026-05-07',
        start_week_2: null,
        end_week_2: null,
        start_week_3: null,
        end_week_3: null,
        start_week_4: null,
        end_week_4: null,
        expenses: [],
        incomes: [],
        provisions: [],
        invoices: [],
        extracts: [],
        goals: [],
        expenseTagOptions: [],
    }
}

function mountComponent(overrides = {}) {
    return shallowMount(BudgetShow, {
        props: {
            installments: [],
            shareUser: null,
            shareUsers: [],
            owner: {
                budget: createBudget(1),
                resume: {},
                goalsCharts: [],
                expenseToTags: [],
                expenseToTagOptionCharts: [],
            },
            share: {
                budget: null,
                resume: null,
                goalsCharts: null,
                expenseToTags: null,
                expenseToTagOptionCharts: null,
            },
            ...overrides,
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Budget/Show.vue', () => {
    beforeEach(() => {
        inertiaState.router.get.mockReset()
        inertiaState.router.post.mockReset()
    })

    it('starts on the owner tab and hides the share tab without a shared budget', () => {
        const wrapper = mountComponent()

        expect(wrapper.vm.tab).toBe('one')
        expect(wrapper.vm.shareTabVisible).toBe(false)
    })

    it('shows the share tab only when both share user and shared budget exist', () => {
        const wrapper = mountComponent({
            shareUser: {
                share_user: {
                    name: 'Partner User',
                },
            },
            share: {
                budget: createBudget(2, 2),
                resume: {},
                goalsCharts: [],
                expenseToTags: [],
                expenseToTagOptionCharts: [],
            },
        })

        expect(wrapper.vm.shareTabVisible).toBe(true)
        expect(wrapper.vm.shareUserName).toBe('Partner User')
    })

    it('keeps the share tab hidden when there is a share user but no shared budget', () => {
        const wrapper = mountComponent({
            shareUser: {
                share_user: {
                    name: 'Partner User',
                },
            },
        })

        expect(wrapper.vm.shareTabVisible).toBe(false)
    })
})
