import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import PrepaidCardExtractIndex from '../Index.vue'

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
        put: vi.fn(function put(url, options = {}) {
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
    formatDate: (value) => value,
    reverseFormatNumber: (value) => value,
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
    'v-card-actions': true,
    'v-spacer': true,
    'v-textarea': true,
    'vuetify-money': true,
    'v-date-input': true,
}

function mountComponent() {
    return shallowMount(PrepaidCardExtractIndex, {
        props: {
            prepaidCard: {
                id: 9,
                name: 'Nomad',
                digits: '7788',
                is_active: true,
            },
            extracts: [],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('PrepaidCardExtract/Index.vue', () => {
    beforeEach(() => {
        inertiaState.forms.length = 0
    })

    it('opens a new extract dialog with a reset form', async () => {
        const wrapper = mountComponent()

        inertiaState.forms[0].yearMonth = '2026-08'
        inertiaState.forms[0].credit = 100
        inertiaState.forms[0].errors = { error: 'prepaid-card-extract.already-exists' }

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(inertiaState.forms[0].reset).toHaveBeenCalled()
        expect(inertiaState.forms[0].clearErrors).toHaveBeenCalled()
    })

    it('submits a normalized create payload from yearMonth', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        inertiaState.forms[0].yearMonth = '2026-05'
        inertiaState.forms[0].credit = 1234.56
        inertiaState.forms[0].credit_date = '2026-05-07'
        inertiaState.forms[0].remarks = 'Initial load'

        await wrapper.vm.save()

        expect(inertiaState.forms[0].post).toHaveBeenCalledWith(
            '/prepaid-card/extract',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            year: '2026',
            month: '05',
            credit: 1234.56,
            credit_date: '2026-05-07',
            remarks: 'Initial load',
            prepaid_card_id: 9,
        })
    })

    it('submits an update payload without period fields', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        wrapper.vm.editItem({
            id: 11,
            year: '2026',
            month: '06',
            credit: 500,
            credit_date: '2026-06-09',
            remarks: 'Old note',
            prepaid_card_id: 9,
        })

        inertiaState.forms[0].credit = 2500.4
        inertiaState.forms[0].credit_date = '2026-06-10'
        inertiaState.forms[0].remarks = 'Updated note'

        await wrapper.vm.save()

        expect(inertiaState.forms[0].put).toHaveBeenCalledWith(
            '/prepaid-card/extract/11',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[0].submittedData).toMatchObject({
            credit: 2500.4,
            credit_date: '2026-06-10',
            remarks: 'Updated note',
        })
        expect(inertiaState.forms[0].submittedData.year).toBeUndefined()
        expect(inertiaState.forms[0].submittedData.month).toBeUndefined()
    })

    it('surfaces duplicate extract backend errors on the month field', () => {
        mountComponent()

        inertiaState.forms[0].errors = {
            error: 'prepaid-card-extract.already-exists',
        }

        expect(inertiaState.forms[0].errors.error).toBe('prepaid-card-extract.already-exists')
    })
})
