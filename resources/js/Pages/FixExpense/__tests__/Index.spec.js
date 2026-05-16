import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import FixExpenseIndex from '../Index.vue'

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
        currencyFieldRules: [() => true],
        selectFieldRules: [() => true],
    }),
    useCurrencyConfig: () => ({
        locale: 'pt-BR',
        prefix: 'R$',
        suffix: '',
        length: 11,
        precision: 2,
    }),
    useDaysList: () => ['01', '02', '03'],
}))

vi.mock('@/utils/logger.js', () => ({
    logger: {
        error: vi.fn(),
    },
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
    'v-autocomplete': true,
    'vuetify-money': true,
}

function mountComponent() {
    return shallowMount(FixExpenseIndex, {
        props: {
            expenses: [],
            shareUsers: [
                {
                    share_user_id: 7,
                    share_user_name: 'Ana',
                },
            ],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('FixExpense/Index.vue', () => {
    beforeEach(() => {
        currentForm = null
    })

    it('opens a new item dialog with a reset form', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 9,
            description: 'Old',
            value: 120,
            due_date: '10',
            remarks: 'legacy',
            share_value: 30,
            share_user_id: 7,
            tags: [{ name: 'HOME' }],
        })
        await wrapper.vm.$nextTick()

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(currentForm.id).toBe(null)
        expect(currentForm.description).toBe(null)
        expect(currentForm.value).toBe(0)
        expect(currentForm.tags).toEqual([])
        expect(currentForm.clearErrors).toHaveBeenCalled()
    })

    it('populates the form when editing an item', () => {
        const wrapper = mountComponent()

        wrapper.vm.form = { resetValidation: vi.fn() }
        wrapper.vm.editItem({
            id: 11,
            description: 'Water',
            value: 98.5,
            due_date: '12',
            remarks: 'Shared',
            share_value: 49.25,
            share_user_id: 7,
            tags: [{ name: 'HOUSE' }],
        })

        expect(currentForm.id).toBe(11)
        expect(currentForm.description).toBe('Water')
        expect(currentForm.value).toBe(98.5)
        expect(currentForm.share_value).toBe(49.25)
        expect(currentForm.share_user_id).toBe(7)
        expect(currentForm.tags).toEqual([{ name: 'HOUSE' }])
    })

    it('submits a create request for a new fix expense', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.description = 'Rent'
        currentForm.value = 150
        currentForm.due_date = '05'

        await wrapper.vm.save()

        expect(currentForm.post).toHaveBeenCalledWith(
            '/fix-expense',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.put).not.toHaveBeenCalled()
    })

    it('submits an update request for an existing fix expense', async () => {
        const wrapper = mountComponent()

        wrapper.vm.form = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
            resetValidation: vi.fn(),
        }

        currentForm.id = 14
        currentForm.description = 'Updated rent'
        currentForm.value = 220
        currentForm.due_date = '08'

        await wrapper.vm.save()

        expect(currentForm.put).toHaveBeenCalledWith(
            '/fix-expense/14',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.post).not.toHaveBeenCalled()
    })
})
