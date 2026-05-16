import { shallowMount } from '@vue/test-utils'
import moment from 'moment'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import InvoiceExpense from '../InvoiceExpense.vue'

const inertiaState = vi.hoisted(() => ({
    router: {
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}))

const toastState = vi.hoisted(() => ({
    warning: vi.fn(),
    error: vi.fn(),
}))

vi.mock('@inertiajs/vue3', () => ({
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

vi.mock('vue-toastification', () => ({
    useToast: () => toastState,
}))

vi.mock('read-excel-file', () => ({
    default: vi.fn(),
}))

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
        selectFieldRules: [() => true],
    }),
}))

vi.mock('@/utils/utils.js', () => ({
    currencyField: (value) => String(value ?? ''),
    formatDate: (value) => value,
    reverseFormatNumber: (value) => Number(value || 0),
    sumField: (items, key) => (items || []).reduce((total, item) => total + Number.parseFloat(item?.[key] || 0), 0),
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
    'v-checkbox': true,
    'v-chip': true,
    'v-divider': true,
}

function createInvoice() {
    return {
        id: 11,
        due_date: '2026-05-05',
        closing_date: '2026-04-25',
        total: 500,
        total_paid: 0,
        closed: false,
        credit_card: {
            id: 7,
            name: 'Visa',
        },
        expenses: [],
    }
}

function mountComponent() {
    return shallowMount(InvoiceExpense, {
        props: {
            invoice: createInvoice(),
            shareUsers: [{ share_user_id: 9, share_user_name: 'Partner User' }],
            budgetWeeks: [],
            titleCard: true,
            yearMonth: '2026-05',
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

describe('CreditCardInvoice/InvoiceExpense.vue', () => {
    beforeEach(() => {
        inertiaState.router.post.mockReset()
        inertiaState.router.put.mockReset()
        inertiaState.router.delete.mockReset()
        inertiaState.router.post.mockImplementation((url, data, options = {}) => {
            options.onSuccess?.()
            options.onFinish?.()
        })
        inertiaState.router.put.mockImplementation((url, data, options = {}) => {
            options.onSuccess?.()
            options.onFinish?.()
        })
        inertiaState.router.delete.mockImplementation((url, options = {}) => {
            options.onSuccess?.()
            options.onFinish?.()
        })
        toastState.warning.mockReset()
        toastState.error.mockReset()
    })

    it('resets expense state when opening a new item dialog', async () => {
        const wrapper = mountComponent()

        wrapper.vm.expense = {
            id: 99,
            description: 'Old',
            date: '2026-05-10',
            value: 90,
            group: 'WEEK_1',
            portion: 1,
            portion_total: 2,
            remarks: 'Old note',
            share_value: 40,
            share_user_id: 9,
            invoice_id: 11,
            tags: [{ name: 'OLD' }],
            divisions: [{ description: 'Part' }],
        }

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(wrapper.vm.expense).toMatchObject({
            id: null,
            description: null,
            date: null,
            value: 0,
            group: null,
            portion: null,
            portion_total: null,
            remarks: null,
            share_value: null,
            share_user_id: null,
            invoice_id: null,
            tags: [],
            divisions: [],
        })
        expect(wrapper.vm.hasDivisions).toBe(false)
    })

    it('populates expense state and divisions when editing an item', async () => {
        const wrapper = mountComponent()
        const item = {
            id: 21,
            description: 'Dinner',
            date: '2026-05-10',
            value: 140,
            group: 'WEEK_2',
            portion: 1,
            portion_total: 2,
            remarks: 'Team',
            share_value: 70,
            invoice_id: 11,
            share_user_id: 9,
            tags: [{ name: 'FOOD' }],
            divisions: [{ description: 'Main', value: 140, tags: [{ name: 'SPLIT' }] }],
        }

        wrapper.vm.editItem(item)
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(wrapper.vm.expense.id).toBe(21)
        expect(wrapper.vm.expense.description).toBe('Dinner')
        expect(wrapper.vm.expense.date.format('YYYY-MM-DD')).toBe('2026-05-10')
        expect(wrapper.vm.expense.divisions).toEqual(item.divisions)
        expect(wrapper.vm.hasDivisions).toBe(true)
    })

    it('submits create payload through router.post', async () => {
        const wrapper = mountComponent()

        wrapper.vm.expense.description = { description: 'Laptop' }
        wrapper.vm.expense.date = moment('2026-05-10')
        wrapper.vm.expense.value = 500
        wrapper.vm.expense.group = 'PORTION'
        wrapper.vm.expense.portion = 1
        wrapper.vm.expense.portion_total = 3
        wrapper.vm.expense.remarks = 'Work'
        wrapper.vm.expense.share_value = 250
        wrapper.vm.expense.share_user_id = 9
        wrapper.vm.expense.tags = [{ name: 'TECH' }]
        wrapper.vm.expense.divisions = [{ description: 'Notebook', value: 500, tags: [{ name: 'HARDWARE' }] }]

        await wrapper.vm.createData()

        expect(inertiaState.router.post).toHaveBeenCalledWith(
            '/credit-card/invoice/expense',
            expect.objectContaining({
                credit_card_id: 7,
                invoice_id: 11,
                description: 'Laptop',
                date: '2026-05-10',
                value: 500,
                group: 'PORTION',
                portion: 1,
                portion_total: 3,
                remarks: 'Work',
                share_value: 250,
                share_user_id: 9,
                tags: [{ name: 'TECH' }],
                divisions: [{ description: 'Notebook', value: 500, tags: [{ name: 'HARDWARE' }] }],
            }),
            expect.objectContaining({
                onSuccess: expect.any(Function),
                onFinish: expect.any(Function),
            })
        )
        expect(wrapper.vm.editDialog).toBe(false)
    })

    it('submits update payload through router.put', async () => {
        const wrapper = mountComponent()

        wrapper.vm.expense.id = 33
        wrapper.vm.expense.description = 'Updated dinner'
        wrapper.vm.expense.date = '2026-05-12'
        wrapper.vm.expense.value = 333.7
        wrapper.vm.expense.group = 'WEEK_3'
        wrapper.vm.expense.portion = null
        wrapper.vm.expense.portion_total = null
        wrapper.vm.expense.remarks = 'Updated'
        wrapper.vm.expense.share_value = 111.2
        wrapper.vm.expense.share_user_id = 9
        wrapper.vm.expense.tags = [{ name: 'UPDATED' }]
        wrapper.vm.expense.divisions = []

        await wrapper.vm.updateData()

        expect(inertiaState.router.put).toHaveBeenCalledWith(
            '/credit-card/invoice/expense/33',
            expect.objectContaining({
                credit_card_id: 7,
                invoice_id: 11,
                description: 'Updated dinner',
                date: '2026-05-12',
                value: 333.7,
                group: 'WEEK_3',
                share_value: 111.2,
                share_user_id: 9,
            }),
            expect.objectContaining({
                onSuccess: expect.any(Function),
                onFinish: expect.any(Function),
            })
        )
        expect(wrapper.vm.editDialog).toBe(false)
    })

    it('updates the invoice closed flag through router.put', async () => {
        const wrapper = mountComponent()

        await wrapper.vm.updateInvoice(true)

        expect(inertiaState.router.put).toHaveBeenCalledWith(
            '/credit-card/invoice/11',
            { closed: true },
            expect.objectContaining({
                onFinish: expect.any(Function),
            })
        )
    })

    it('blocks save when division totals do not match the expense total', () => {
        const wrapper = mountComponent()

        wrapper.vm.expense.value = 200
        wrapper.vm.expense.divisions = [
            { value: 120, share_value: 0 },
            { value: 50, share_value: 0 },
        ]

        const isValid = wrapper.vm.validateDivisions()

        expect(isValid).toBe(false)
        expect(toastState.warning).toHaveBeenCalledWith('credit-card-invoice-expense.error-total-division')
    })

    it('submits parsed import payload through router.post', async () => {
        const wrapper = mountComponent()
        const rows = [
            {
                date: '2026-05-15',
                description: 'Imported row',
                value: 80.5,
                share_value: 20.1,
                remarks: 'Imported',
                group: 'WEEK_4',
                portion: null,
                portion_total: null,
                share_user_id: 9,
                tags: [{ name: 'IMPORT' }],
            },
        ]

        await wrapper.vm.importExcel(rows)

        expect(inertiaState.router.post).toHaveBeenCalledWith(
            '/credit-card/invoice/expense/import-excel',
            {
                data: rows,
                invoice_id: 11,
            },
            expect.objectContaining({
                onFinish: expect.any(Function),
            })
        )
    })
})
