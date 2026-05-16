import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import BudgetExpenseTable from '../BudgetExpenseTable.vue'

vi.mock('@/utils/utils.js', () => ({
    currencyField: (value) => String(value ?? ''),
    sumField: () => 0,
}))

const layoutStubs = {
    'v-row': { template: '<div><slot /></div>' },
    'v-col': { template: '<div><slot /></div>' },
    'v-toolbar': { template: '<div><slot /></div>' },
}

function mountTable(props = {}) {
    return mount(BudgetExpenseTable, {
        props: {
            headers: [{ title: 'Description', key: 'description' }],
            expenses: [
                {
                    id: 12,
                    description: 'Rent',
                    date: '2026-05-10',
                    value: 900,
                    share_value: 450,
                    group: 'MONTHLY',
                    paid: false,
                    remarks: null,
                    tags: [],
                    share_user: null,
                },
            ],
            search: '',
            rowProps: () => ({ class: '' }),
            groupLabel: (group) => `Group ${group}`,
            installmentLabel: () => 'Installment details',
            ...props,
        },
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-btn': {
                    emits: ['click'],
                    template: '<button @click="$emit(\'click\')"><slot /></button>',
                },
                'v-data-table': {
                    props: ['items'],
                    template: '<div><slot name="top" /><slot name="item.action" :item="items[0]" /></div>',
                },
                'v-text-field': {
                    props: ['modelValue'],
                    emits: ['update:modelValue', 'click:clear'],
                    template: `
                        <div>
                            <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />
                            <button type="button" data-test="clear" @click="$emit('click:clear')">clear</button>
                        </div>
                    `,
                },
                'v-tooltip': {
                    template: '<div><slot name="activator" :props="{}" /></div>',
                },
                'v-icon': {
                    emits: ['click'],
                    template: '<button data-test="icon" @click="$emit(\'click\')"></button>',
                },
            },
        },
    })
}

describe('BudgetExpenseTable', () => {
    it('emits list actions back to BudgetExpense', async () => {
        const wrapper = mountTable()

        await wrapper.find('button').trigger('click')
        await wrapper.find('input').setValue('Rent')
        await wrapper.find('[data-test="clear"]').trigger('click')
        await wrapper.findAll('[data-test="icon"]')[0].trigger('click')
        await wrapper.findAll('[data-test="icon"]')[1].trigger('click')

        expect(wrapper.emitted('new')).toHaveLength(1)
        expect(wrapper.emitted('update:search')).toEqual([['Rent'], [null]])
        expect(wrapper.emitted('edit')?.[0][0]).toMatchObject({ id: 12, description: 'Rent' })
        expect(wrapper.emitted('remove')?.[0][0]).toMatchObject({ id: 12, description: 'Rent' })
    })

    it('hides the new action in view-only mode', () => {
        const wrapper = mountTable({ viewOnly: true })

        expect(wrapper.text()).not.toContain('default.new')
    })
})
