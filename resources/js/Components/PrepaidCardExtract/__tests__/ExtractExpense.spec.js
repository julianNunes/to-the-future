import { shallowMount } from '@vue/test-utils'
import moment from 'moment'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import ExtractExpense from '../ExtractExpense.vue'

const inertiaState = vi.hoisted(() => ({
    router: {
        get: vi.fn(),
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

function createExtract() {
    return {
        id: 11,
        year: '2026',
        month: '05',
        year_month: '05/2026',
        credit_date: '2026-05-05',
        credit: 800,
        prepaid_card: {
            id: 7,
            name: 'Nomad',
        },
        expenses: [],
    }
}

function mountComponent() {
    return shallowMount(ExtractExpense, {
        props: {
            extract: createExtract(),
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

describe('PrepaidCardExtract/ExtractExpense.vue', () => {
    beforeEach(() => {
        inertiaState.router.get.mockReset()
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
            remarks: 'Old note',
            share_value: 40,
            share_user_id: 9,
            extract_id: 11,
            tags: [{ name: 'OLD' }],
        }

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(wrapper.vm.expense).toMatchObject({
            id: null,
            description: null,
            value: 0,
            group: null,
            remarks: null,
            share_value: null,
            share_user_id: null,
            extract_id: null,
            tags: [],
        })
        expect(wrapper.vm.expense.date.format('YYYY-MM-DD')).toBe('2026-05-01')
    })

    it('populates expense state when editing an item', async () => {
        const wrapper = mountComponent()
        const item = {
            id: 21,
            description: 'Fuel',
            date: '2026-05-10',
            value: 140,
            group: 'WEEK_2',
            remarks: 'Trip',
            share_value: 70,
            extract_id: 11,
            share_user_id: 9,
            tags: [{ name: 'CAR' }],
        }

        wrapper.vm.editItem(item)
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(wrapper.vm.expense.id).toBe(21)
        expect(wrapper.vm.expense.description).toBe('Fuel')
        expect(wrapper.vm.expense.date.format('YYYY-MM-DD')).toBe('2026-05-10')
        expect(wrapper.vm.expense.tags).toEqual(item.tags)
    })

    it('submits create payload through router.post', async () => {
        const wrapper = mountComponent()

        wrapper.vm.expense.description = { description: 'Gas station' }
        wrapper.vm.expense.date = moment('2026-05-10')
        wrapper.vm.expense.value = 500
        wrapper.vm.expense.group = 'WEEK_1'
        wrapper.vm.expense.remarks = 'Work'
        wrapper.vm.expense.share_value = 250
        wrapper.vm.expense.share_user_id = 9
        wrapper.vm.expense.tags = [{ name: 'FUEL' }]

        await wrapper.vm.createData()

        expect(inertiaState.router.post).toHaveBeenCalledWith(
            '/prepaid-card/extract/expense',
            expect.objectContaining({
                prepaid_card_id: 7,
                extract_id: 11,
                description: 'Gas station',
                date: '2026-05-10',
                value: 500,
                group: 'WEEK_1',
                remarks: 'Work',
                share_value: 250,
                share_user_id: 9,
                tags: [{ name: 'FUEL' }],
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
        wrapper.vm.expense.description = 'Updated fuel'
        wrapper.vm.expense.date = '2026-05-12'
        wrapper.vm.expense.value = 333.7
        wrapper.vm.expense.group = 'WEEK_3'
        wrapper.vm.expense.remarks = 'Updated'
        wrapper.vm.expense.share_value = 111.2
        wrapper.vm.expense.share_user_id = 9
        wrapper.vm.expense.tags = [{ name: 'UPDATED' }]

        await wrapper.vm.updateData()

        expect(inertiaState.router.put).toHaveBeenCalledWith(
            '/prepaid-card/extract/expense/33',
            expect.objectContaining({
                prepaid_card_id: 7,
                extract_id: 11,
                description: 'Updated fuel',
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

    it('removes an expense through router.delete', () => {
        const wrapper = mountComponent()

        wrapper.vm.deleteId = 44
        wrapper.vm.remove()

        expect(inertiaState.router.delete).toHaveBeenCalledWith(
            '/prepaid-card/extract/expense/44',
            expect.objectContaining({
                onFinish: expect.any(Function),
                preserveScroll: true,
            })
        )
    })

    it('rejects import rows with incomplete share data', () => {
        const wrapper = mountComponent()

        const isValid = wrapper.vm.validateImportExcel([
            {
                description: 'Fuel',
                date: '2026-05-10',
                value: 50,
                group: 'WEEK_1',
                share_value: 20,
                share_user_id: null,
            },
        ])

        expect(isValid).toBe(false)
        expect(toastState.error).toHaveBeenCalled()
    })

    it('submits import payload through router.post', async () => {
        const wrapper = mountComponent()

        const rows = [
            {
                description: 'Imported fuel',
                date: '2026-05-09',
                value: 80.5,
                group: 'WEEK_3',
                remarks: 'Imported',
                share_value: 20.1,
                share_user_id: 9,
                tags: [{ name: 'IMPORT' }],
            },
        ]

        await wrapper.vm.importExcel(rows)

        expect(inertiaState.router.post).toHaveBeenCalledWith(
            '/prepaid-card/extract/expense/import-excel',
            {
                data: rows,
                extract_id: 11,
            },
            expect.objectContaining({
                onFinish: expect.any(Function),
            })
        )
    })

    it('opens the extract show page in view-only mode', () => {
        const wrapper = mountComponent()

        wrapper.vm.openExtract()

        expect(inertiaState.router.get).toHaveBeenCalledWith('/prepaid-card/extract/11')
    })
})
