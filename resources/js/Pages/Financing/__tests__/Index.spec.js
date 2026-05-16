import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import FinancingIndex from '../Index.vue'

let currentForm

function createMockForm(initialValues) {
    return {
        ...initialValues,
        processing: false,
        errors: {},
        transform: vi.fn(function transform(callback) {
            this._transform = callback
            return this
        }),
        post: vi.fn((url, options = {}) => {
            options.onSuccess?.()
        }),
        put: vi.fn((url, options = {}) => {
            options.onSuccess?.()
        }),
        clearErrors: vi.fn(function clearErrors() {
            this.errors = {}
        }),
    }
}

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div />' },
    Link: { template: '<a><slot /></a>' },
    useForm: (initialValues) => {
        currentForm = createMockForm(initialValues)
        return currentForm
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

vi.mock('@/Layouts/AuthenticatedLayout.vue', () => ({
    default: { name: 'AuthenticatedLayout', template: '<div><slot /></div>' },
}))

vi.mock('@/Components/ConfirmDialog.vue', () => ({
    default: { name: 'ConfirmDialog', template: '<div />' },
}))

vi.mock('@/Components/Breadcrumbs.vue', () => ({
    default: { name: 'Breadcrumbs', template: '<div />' },
}))

vi.mock('@/composables/useCrudOperations.js', () => ({
    useCrudOperations: () => ({
        isLoading: ref(false),
        editDialog: ref(false),
        titleModal: ref(''),
        confirmRemove: vi.fn(),
    }),
}))

vi.mock('@/composables/useFormConstants.js', () => ({
    useValidationRules: () => ({
        textFieldRules: [() => true],
        numberFieldRules: [() => true],
        currencyFieldRules: [() => true],
    }),
    useCurrencyConfig: () => ({
        locale: 'pt-BR',
        prefix: 'R$',
        suffix: '',
        length: 11,
        precision: 2,
    }),
}))

const globalStubs = {
    'v-card': true,
    'v-card-text': true,
    'v-row': true,
    'v-col': true,
    'v-btn': true,
    'v-data-table': true,
    'v-tooltip': true,
    'v-icon': true,
    'v-toolbar': true,
    'v-text-field': true,
    'v-dialog': true,
    'v-card-title': true,
    'v-form': true,
    'v-card-actions': true,
    'v-spacer': true,
    'v-date-input': true,
    'vuetify-money': true,
}

function mountComponent() {
    return shallowMount(FinancingIndex, {
        props: {
            financings: [],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Financing/Index.vue', () => {
    beforeEach(() => {
        currentForm = null
    })

    it('opens a new item dialog with a reset form', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 9,
            description: 'Old loan',
            start_date: '2026-01-10',
            total: 1200,
            fees_monthly: 1.5,
            portion_total: 12,
            remarks: 'legacy',
        })
        await wrapper.vm.$nextTick()

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(currentForm.id).toBe(null)
        expect(currentForm.description).toBe(null)
        expect(currentForm.total).toBe(0)
        expect(currentForm.portion_total).toBe(0)
        expect(currentForm.clearErrors).toHaveBeenCalled()
    })

    it('populates the form when editing an item', () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 11,
            description: 'House loan',
            start_date: '2026-05-15',
            total: 9000,
            fees_monthly: 1.9,
            portion_total: 18,
            remarks: 'Updated',
        })

        expect(currentForm.id).toBe(11)
        expect(currentForm.description).toBe('House loan')
        expect(currentForm.total).toBe(9000)
        expect(currentForm.fees_monthly).toBe(1.9)
        expect(currentForm.portion_total).toBe(18)
        expect(currentForm.remarks).toBe('Updated')
    })

    it('submits a create request for a new financing', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.description = 'House loan'
        currentForm.start_date = new Date('2026-05-15T00:00:00')
        currentForm.total = 1200
        currentForm.fees_monthly = 1.75
        currentForm.portion_total = 3
        currentForm.start_date_installment = new Date('2026-06-01T00:00:00')
        currentForm.value_installment = 400

        await wrapper.vm.save()

        expect(currentForm.transform).toHaveBeenCalled()
        expect(currentForm.post).toHaveBeenCalledWith(
            '/financing',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.put).not.toHaveBeenCalled()
    })

    it('submits an update request for an existing financing', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.id = 14
        currentForm.description = 'Updated loan'
        currentForm.start_date = new Date('2026-01-15T00:00:00')
        currentForm.total = 4500
        currentForm.fees_monthly = 3.5

        await wrapper.vm.save()

        expect(currentForm.transform).toHaveBeenCalled()
        expect(currentForm.put).toHaveBeenCalledWith(
            '/financing/14',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.post).not.toHaveBeenCalled()
    })
})
