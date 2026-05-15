import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import TagIndex from '../Index.vue'

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
        reset: vi.fn(function reset() {
            this.id = initialValues.id
            this.name = initialValues.name
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

vi.mock('write-excel-file', () => ({
    default: vi.fn(),
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
}

function mountComponent() {
    return shallowMount(TagIndex, {
        props: {
            tags: [],
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Tag/Index.vue', () => {
    beforeEach(() => {
        currentForm = null
    })

    it('opens a new item dialog with a reset form', () => {
        const wrapper = mountComponent()

        wrapper.vm.editItem({ id: 5, name: 'OLD' })
        wrapper.vm.newItem()

        expect(wrapper.vm.editDialog).toBe(true)
        expect(currentForm.reset).toHaveBeenCalled()
        expect(currentForm.id).toBe(null)
        expect(currentForm.name).toBe(null)
    })

    it('normalizes the tag name to uppercase', () => {
        const wrapper = mountComponent()

        wrapper.vm.tagName = 'security'

        expect(currentForm.name).toBe('SECURITY')
    })

    it('submits a create request for a new tag', async () => {
        const wrapper = mountComponent()

        wrapper.vm.formRef = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }
        currentForm.name = 'SECURITY'

        await wrapper.vm.save()

        expect(currentForm.post).toHaveBeenCalledWith(
            '/tag',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.put).not.toHaveBeenCalled()
    })

    it('submits an update request for an existing tag', async () => {
        const wrapper = mountComponent()

        wrapper.vm.formRef = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }
        currentForm.id = 9
        currentForm.name = 'SAVINGS'

        await wrapper.vm.save()

        expect(currentForm.put).toHaveBeenCalledWith(
            '/tag/9',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(currentForm.post).not.toHaveBeenCalled()
    })
})
