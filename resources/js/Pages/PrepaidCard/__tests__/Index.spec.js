import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import PrepaidCardIndex from '../Index.vue'

let currentForm

function createMockForm(initialValues) {
    return {
        ...initialValues,
        processing: false,
        errors: {},
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
        booleanFieldRules: [() => true],
        digitsFieldRules: [() => true],
    }),
    useIsActiveOptions: () => [
        { name: 'default.yes', value: 1 },
        { name: 'default.no', value: 0 },
    ],
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
    'v-select': true,
}

function mountComponent() {
    return shallowMount(PrepaidCardIndex, {
        props: {
            prepaidCards: [],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('PrepaidCard/Index.vue', () => {
    beforeEach(() => {
        currentForm = null
    })

    it('opens a new item dialog with a reset form', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 5,
            name: 'Old',
            digits: '1111',
            is_active: 1,
        })
        await wrapper.vm.$nextTick()

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(currentForm.id).toBe(null)
        expect(currentForm.name).toBe(null)
        expect(currentForm.digits).toBe(null)
        expect(currentForm.is_active).toBe(null)
        expect(currentForm.clearErrors).toHaveBeenCalled()
    })

    it('populates the form when editing an item', () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 11,
            name: 'Wallet',
            digits: '4321',
            is_active: 0,
        })

        expect(currentForm.id).toBe(11)
        expect(currentForm.name).toBe('Wallet')
        expect(currentForm.digits).toBe('4321')
        expect(currentForm.is_active).toBe(0)
    })

    it('submits a create request for a new prepaid card', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.name = 'Wallet'
        currentForm.digits = '1234'
        currentForm.is_active = 1

        await wrapper.vm.save()

        expect(currentForm.post).toHaveBeenCalledWith(
            '/prepaid-card',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.put).not.toHaveBeenCalled()
    })

    it('submits an update request for an existing prepaid card', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.id = 14
        currentForm.name = 'Updated Wallet'
        currentForm.digits = '9876'
        currentForm.is_active = 0

        await wrapper.vm.save()

        expect(currentForm.put).toHaveBeenCalledWith(
            '/prepaid-card/14',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.post).not.toHaveBeenCalled()
    })
})
