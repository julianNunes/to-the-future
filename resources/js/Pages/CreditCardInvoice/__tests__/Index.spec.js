import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import CreditCardInvoiceIndex from '../Index.vue'

const inertiaState = vi.hoisted(() => ({
    forms: [],
}))

function createMockForm(initialValues) {
    return {
        ...initialValues,
        processing: false,
        errors: {},
        submittedData: null,
        _transformer: null,
        post: vi.fn(function post(url, options = {}) {
            this.lastUrl = url
            this.submittedData = this._transformer ? this._transformer({ ...this }) : { ...this }
            options.onSuccess?.()
        }),
        transform: vi.fn(function transform(callback) {
            this._transformer = callback
            return this
        }),
        clearErrors: vi.fn(function clearErrors() {
            this.errors = {}
        }),
        reset: vi.fn(function reset() {
            Object.assign(this, initialValues)
        }),
    }
}

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div />' },
    Link: { name: 'Link', template: '<a><slot /></a>' },
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

vi.mock('@/utils/utils.js', () => ({
    currencyField: (value) => value,
}))

const globalStubs = {
    'v-card': true,
    'v-card-title': true,
    'v-card-text': true,
    'v-row': true,
    'v-col': true,
    'v-text-field': true,
    'v-btn': true,
    'v-data-table': true,
    'v-tooltip': true,
    'v-icon': true,
    'v-toolbar': true,
    'v-dialog': true,
    'v-form': true,
    'v-checkbox': true,
    'v-card-actions': true,
    'v-spacer': true,
}

function mountComponent() {
    return shallowMount(CreditCardInvoiceIndex, {
        props: {
            creditCard: {
                id: 7,
                name: 'Visa',
                digits: '1234',
                due_date: '5',
                closing_date: '25',
                is_active: true,
            },
            invoices: [],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('CreditCardInvoice/Index.vue', () => {
    beforeEach(() => {
        inertiaState.forms.length = 0
    })

    it('opens a new invoice dialog with a reset form', async () => {
        const wrapper = mountComponent()

        inertiaState.forms[0].yearMonth = '2026-08'
        inertiaState.forms[0].automatic_generate = true
        inertiaState.forms[0].errors = { error: 'credit-card-invoice.already-exists' }

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(inertiaState.forms[0].reset).toHaveBeenCalled()
        expect(inertiaState.forms[0].clearErrors).toHaveBeenCalled()
    })

    it('submits a normalized invoice payload from yearMonth', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }

        inertiaState.forms[0].yearMonth = '2026-05'
        inertiaState.forms[0].automatic_generate = true

        await wrapper.vm.save()

        expect(inertiaState.forms[0].post).toHaveBeenCalledWith(
            '/credit-card/invoice',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            due_date: '2026-05-05',
            closing_date: '2026-05-25',
            year: '2026',
            month: '05',
            credit_card_id: 7,
            automatic_generate: true,
        })
    })

    it('surfaces duplicate invoice backend errors on the month field', () => {
        const wrapper = mountComponent()

        inertiaState.forms[0].errors = {
            error: 'credit-card-invoice.already-exists',
        }

        expect(wrapper.vm.invoiceYearMonthErrors).toEqual(['credit-card-invoice.already-exists'])
    })
})
