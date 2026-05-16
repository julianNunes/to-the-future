import { shallowMount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import BudgetIndex from '../Index.vue'

const inertiaState = vi.hoisted(() => ({
    forms: [],
    router: {
        get: vi.fn(),
    },
}))

function createMockForm(initialValues) {
    return {
        ...initialValues,
        processing: false,
        errors: {},
        submittedData: null,
        _transformer: null,
        post: vi.fn(function post(url, options = {}) {
            this.submittedData = this._transformer ? this._transformer({ ...this }) : { ...this }
            options.onSuccess?.()
        }),
        put: vi.fn(function put(url, options = {}) {
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
    }
}

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div />' },
    Link: { name: 'Link', template: '<a><slot /></a>' },
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
    'v-checkbox': true,
}

function mountComponent() {
    return shallowMount(BudgetIndex, {
        props: {
            budgets: [],
            year: '2026',
        },
        global: {
            mocks: {
                $t: (key) => key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Budget/Index.vue', () => {
    beforeEach(() => {
        inertiaState.forms.length = 0
        inertiaState.router.get.mockReset()
    })

    it('opens a new item dialog with a reset form', async () => {
        const wrapper = mountComponent()

        wrapper.vm.editItem({
            id: 9,
            year: '2026',
            month: '05',
            start_week_1: '2026-05-01',
            end_week_1: '2026-05-07',
            start_week_2: '2026-05-08',
            end_week_2: '2026-05-14',
            start_week_3: '2026-05-15',
            end_week_3: '2026-05-21',
            start_week_4: '2026-05-22',
            end_week_4: '2026-05-28',
        })
        await wrapper.vm.$nextTick()

        wrapper.vm.newItem()
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.createDialog).toBe(true)
        expect(inertiaState.forms[0].id).toBe(null)
        expect(inertiaState.forms[0].yearMonth).toBe(null)
        expect(inertiaState.forms[0].includeFixExpenses).toBe(false)
        expect(inertiaState.forms[0].clearErrors).toHaveBeenCalled()
    })

    it('submits a create request with normalized year-month and week dates', async () => {
        const wrapper = mountComponent()

        wrapper.vm.formCreate = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }

        inertiaState.forms[0].yearMonth = '2026-05'
        inertiaState.forms[0].start_week_1 = new Date('2026-05-01T00:00:00Z')
        inertiaState.forms[0].end_week_1 = new Date('2026-05-07T00:00:00Z')
        inertiaState.forms[0].start_week_2 = ''
        inertiaState.forms[0].end_week_2 = 'Invalid date'
        inertiaState.forms[0].includeFixExpenses = true
        inertiaState.forms[0].includeProvisions = false

        await wrapper.vm.save()

        expect(inertiaState.forms[0].post).toHaveBeenCalledWith(
            '/budget',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[0].submittedData.year).toBe('2026')
        expect(inertiaState.forms[0].submittedData.month).toBe('05')
        expect(inertiaState.forms[0].submittedData.start_week_1).toBe('2026-05-01')
        expect(inertiaState.forms[0].submittedData.end_week_1).toBe('2026-05-07')
        expect(inertiaState.forms[0].submittedData.start_week_2).toBe(null)
        expect(inertiaState.forms[0].submittedData.end_week_2).toBe(null)
    })

    it('submits an update request for an existing budget', async () => {
        const wrapper = mountComponent()

        wrapper.vm.formCreate = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }

        inertiaState.forms[0].id = 14
        inertiaState.forms[0].start_week_1 = new Date('2026-06-01T00:00:00Z')
        inertiaState.forms[0].end_week_1 = new Date('2026-06-07T00:00:00Z')

        await wrapper.vm.save()

        expect(inertiaState.forms[0].put).toHaveBeenCalledWith(
            '/budget/14',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[0].submittedData.start_week_1).toBe('2026-06-01')
        expect(inertiaState.forms[0].submittedData.end_week_1).toBe('2026-06-07')
    })

    it('submits a clone request with normalized year-month', async () => {
        const wrapper = mountComponent()

        wrapper.vm.formClone = {
            validate: vi.fn().mockResolvedValue({ valid: true }),
        }

        inertiaState.forms[1].id = 18
        inertiaState.forms[1].yearMonth = '2026-06'
        inertiaState.forms[1].cloneBugdetExpenses = true
        inertiaState.forms[1].cloneBugdetIncomes = false
        inertiaState.forms[1].cloneBugdetGoals = true

        await wrapper.vm.clone()

        expect(inertiaState.forms[1].put).toHaveBeenCalledWith(
            '/budget/clone/18',
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            })
        )
        expect(inertiaState.forms[1].submittedData.year).toBe('2026')
        expect(inertiaState.forms[1].submittedData.month).toBe('06')
        expect(inertiaState.forms[1].submittedData.cloneBugdetExpenses).toBe(true)
        expect(inertiaState.forms[1].submittedData.cloneBugdetGoals).toBe(true)
    })
})
