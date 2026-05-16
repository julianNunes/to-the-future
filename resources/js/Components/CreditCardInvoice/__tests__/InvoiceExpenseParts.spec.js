import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import InvoiceExpenseActions from '../InvoiceExpenseActions.vue'
import InvoiceExpenseDivisionTable from '../InvoiceExpenseDivisionTable.vue'
import InvoiceExpenseSummary from '../InvoiceExpenseSummary.vue'
import InvoiceExpenseTable from '../InvoiceExpenseTable.vue'

vi.mock('@/utils/utils.js', () => ({
    currencyField: (value) => String(value ?? ''),
    sumField: () => 0,
    sumGroup: () => 0,
}))

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
    template: '<button data-test="icon" :disabled="disabled" @click="$emit(\'click\')"></button>',
}

const dataTableStub = {
    props: ['items'],
    template: '<div><slot name="top" /><slot name="item.action" :item="items[0]" /></div>',
}

function mountSummary() {
    return mount(InvoiceExpenseSummary, {
        props: {
            creditCardName: 'Visa',
            dueDate: '05/05/2026',
            closingDate: '25/04/2026',
            total: 'R$ 500,00',
            totalPaid: 'R$ 100,00',
            closedName: 'default.no',
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
    return mount(InvoiceExpenseActions, {
        props: { viewOnly: false, isClosed: false, ...props },
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-btn': buttonStub,
            },
        },
    })
}

function tableProps(itemsKey = 'expenses') {
    return {
        headers: [{ title: 'Description', key: 'description' }],
        [itemsKey]: [
            {
                id: 22,
                description: 'Dinner',
                date: '2026-05-10',
                value: 120,
                share_value: 60,
                group: 'WEEK_1',
                portion: 1,
                portion_total: 2,
                tags: [],
                share_user: null,
            },
        ],
        search: '',
        groupLabel: (group) => `Group ${group}`,
    }
}

function mountTable(component, props) {
    return mount(component, {
        props,
        global: {
            mocks: { $t: (key) => key },
            stubs: {
                ...layoutStubs,
                'v-btn': buttonStub,
                'v-data-table': dataTableStub,
                'v-text-field': textFieldStub,
                'v-tooltip': tooltipStub,
                'v-icon': iconStub,
            },
        },
    })
}

describe('CreditCardInvoice extracted invoice expense parts', () => {
    it('renders invoice summary values as readonly fields', () => {
        const wrapper = mountSummary()

        const values = wrapper.findAll('input').map((input) => input.element.value)

        expect(values).toEqual(['Visa', '05/05/2026', '25/04/2026', 'R$ 500,00', 'R$ 100,00', 'default.no'])
    })

    it('emits invoice action events and exposes import file click', async () => {
        const wrapper = mountActions()
        const input = wrapper.find('input[type="file"]').element
        const inputClick = vi.spyOn(input, 'click').mockImplementation(() => {})

        await wrapper.findAll('button')[0].trigger('click')
        await wrapper.findAll('button')[3].trigger('click')
        wrapper.vm.clickImportFile()
        await wrapper.find('input[type="file"]').trigger('change')

        const closedWrapper = mountActions({ isClosed: true })
        await closedWrapper.findAll('button')[3].trigger('click')

        expect(wrapper.emitted('new')).toHaveLength(1)
        expect(wrapper.emitted('update-invoice')).toEqual([[true]])
        expect(closedWrapper.emitted('update-invoice')).toEqual([[false]])
        expect(wrapper.emitted('select-file')).toHaveLength(1)
        expect(inputClick).toHaveBeenCalled()
    })

    it('emits main table search and row actions', async () => {
        const wrapper = mountTable(InvoiceExpenseTable, tableProps('expenses'))

        await wrapper.find('input').setValue('Dinner')
        await wrapper.find('[data-test="clear"]').trigger('click')
        await wrapper.findAll('[data-test="icon"]')[0].trigger('click')
        await wrapper.findAll('[data-test="icon"]')[1].trigger('click')

        expect(wrapper.emitted('update:search')).toEqual([['Dinner'], [null]])
        expect(wrapper.emitted('edit')?.[0][0]).toMatchObject({ id: 22, description: 'Dinner' })
        expect(wrapper.emitted('remove')?.[0][0]).toMatchObject({ id: 22, description: 'Dinner' })
    })

    it('emits division table search and row actions', async () => {
        const wrapper = mountTable(InvoiceExpenseDivisionTable, tableProps('divisions'))

        await wrapper.find('input').setValue('Dinner')
        await wrapper.find('[data-test="clear"]').trigger('click')
        await wrapper.findAll('[data-test="icon"]')[0].trigger('click')
        await wrapper.findAll('[data-test="icon"]')[1].trigger('click')

        expect(wrapper.emitted('update:search')).toEqual([['Dinner'], [null]])
        expect(wrapper.emitted('edit')?.[0][0]).toMatchObject({ id: 22, description: 'Dinner' })
        expect(wrapper.emitted('remove')?.[0][0]).toMatchObject({ id: 22, description: 'Dinner' })
    })
})
