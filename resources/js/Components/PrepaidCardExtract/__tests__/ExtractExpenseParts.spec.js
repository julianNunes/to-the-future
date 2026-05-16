import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import ExtractExpenseActions from '../ExtractExpenseActions.vue'
import ExtractExpenseSummary from '../ExtractExpenseSummary.vue'
import ExtractExpenseTable from '../ExtractExpenseTable.vue'

const layoutStubs = {
    'v-row': { template: '<div><slot /></div>' },
    'v-col': { template: '<div><slot /></div>' },
    'v-toolbar': { template: '<div><slot /></div>' },
}

const buttonStub = {
    props: ['disabled'],
    emits: ['click'],
    template: '<button :disabled="disabled" @click="$emit(\'click\')"><slot /></button>',
}

const textFieldStub = {
    props: ['label', 'modelValue'],
    emits: ['update:modelValue', 'click:clear'],
    template: `
        <label>
            {{ label }}
            <input
                :aria-label="label"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button type="button" data-test="clear" @click="$emit('click:clear')">clear</button>
        </label>
    `,
}

const tooltipStub = {
    template: '<div><slot name="activator" :props="{}" /></div>',
}

const iconStub = {
    props: ['disabled'],
    emits: ['click'],
    template: '<button :disabled="disabled" @click="$emit(\'click\')"></button>',
}

function mountSummary() {
    return mount(ExtractExpenseSummary, {
        props: {
            prepaidCardName: 'Nomad',
            yearMonth: '05/2026',
            creditDate: '05/05/2026',
            credit: 'R$ 800,00',
        },
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-text-field': textFieldStub,
            },
        },
    })
}

function mountActions(props = {}) {
    return mount(ExtractExpenseActions, {
        props: { viewOnly: false, ...props },
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-btn': buttonStub,
            },
        },
    })
}

function mountTable() {
    return mount(ExtractExpenseTable, {
        props: {
            headers: [{ title: 'Description', key: 'description' }],
            expenses: [
                {
                    id: 10,
                    description: 'Fuel',
                    date: '2026-05-10',
                    value: 120.4,
                    share_value: 60.2,
                    group: 'WEEK_1',
                    tags: [],
                    share_user: null,
                },
            ],
            search: '',
            groupLabel: (group) => `Group ${group}`,
        },
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-data-table': {
                    props: ['items'],
                    template: '<div><slot name="top" /><slot name="item.action" :item="items[0]" /></div>',
                },
                'v-text-field': textFieldStub,
                'v-tooltip': tooltipStub,
                'v-icon': iconStub,
                'v-btn': buttonStub,
            },
        },
    })
}

describe('PrepaidCardExtract extracted expense parts', () => {
    it('renders extract summary values as readonly fields', () => {
        const wrapper = mountSummary()

        const values = wrapper.findAll('input').map((input) => input.element.value)

        expect(values).toEqual(['Nomad', '05/2026', '05/05/2026', 'R$ 800,00'])
    })

    it('emits action events and exposes import file click', async () => {
        const wrapper = mountActions()
        const input = wrapper.find('input[type="file"]').element
        const inputClick = vi.spyOn(input, 'click').mockImplementation(() => {})

        await wrapper.findAll('button')[0].trigger('click')
        wrapper.vm.clickImportFile()
        await wrapper.find('input[type="file"]').trigger('change')

        const viewOnlyWrapper = mountActions({ viewOnly: true })
        await viewOnlyWrapper.findAll('button')[3].trigger('click')

        expect(wrapper.emitted('new')).toHaveLength(1)
        expect(viewOnlyWrapper.emitted('open')).toHaveLength(1)
        expect(wrapper.emitted('select-file')).toHaveLength(1)
        expect(inputClick).toHaveBeenCalled()
    })

    it('emits table search and row action events', async () => {
        const wrapper = mountTable()
        const input = wrapper.find('input')
        const actionButtons = wrapper.findAll('button').filter((button) => !button.attributes('data-test'))

        await input.setValue('Fuel')
        await wrapper.find('[data-test="clear"]').trigger('click')
        await actionButtons[0].trigger('click')
        await actionButtons[1].trigger('click')

        expect(wrapper.emitted('update:search')).toEqual([['Fuel'], [null]])
        expect(wrapper.emitted('edit')?.[0][0]).toMatchObject({ id: 10, description: 'Fuel' })
        expect(wrapper.emitted('remove')?.[0][0]).toMatchObject({ id: 10, description: 'Fuel' })
    })
})
