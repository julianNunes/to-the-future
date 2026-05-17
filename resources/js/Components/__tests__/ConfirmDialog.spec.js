import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import { nextTick } from 'vue'
import ConfirmDialog from '../ConfirmDialog.vue'

const globalStubs = {
    'v-dialog': {
        props: ['modelValue', 'maxWidth'],
        template: '<div class="dialog-root" :data-open="String(modelValue)" v-bind="$attrs"><slot /></div>',
    },
    'v-card': { template: '<section><slot /></section>' },
    'v-toolbar': { template: '<header><slot /></header>' },
    'v-toolbar-title': { template: '<h2 v-bind="$attrs"><slot /></h2>' },
    'v-card-text': { template: '<p v-bind="$attrs"><slot /></p>' },
    'v-card-actions': { template: '<footer><slot /></footer>' },
    'v-spacer': { template: '<span />' },
    'v-btn': {
        emits: ['click'],
        template: '<button type="button" v-bind="$attrs" @click="$emit(\'click\')"><slot /></button>',
    },
}

function mountComponent() {
    return mount(ConfirmDialog, {
        global: {
            mocks: {
                $t: (key) =>
                    ({
                        'default.cancel': 'Cancel',
                    })[key] ?? key,
            },
            stubs: globalStubs,
        },
    })
}

describe('Components/ConfirmDialog.vue', () => {
    it('renders accessible dialog metadata when opened with a message', async () => {
        const wrapper = mountComponent()
        const resolution = wrapper.vm.open('Delete item', 'This action cannot be undone.')

        await nextTick()

        const dialog = wrapper.find('.dialog-root')
        const titleId = dialog.attributes('aria-labelledby')
        const messageId = dialog.attributes('aria-describedby')

        expect(dialog.attributes('role')).toBe('alertdialog')
        expect(dialog.attributes('aria-modal')).toBe('true')
        expect(wrapper.find(`#${titleId}`).text()).toBe('Delete item')
        expect(wrapper.find(`#${messageId}`).text()).toBe('This action cannot be undone.')

        await wrapper.find('button[aria-label="Cancel"]').trigger('click')
        await expect(resolution).resolves.toBe(false)
    })

    it('omits description binding when no message is provided and resolves confirmation', async () => {
        const wrapper = mountComponent()
        const resolution = wrapper.vm.open('Close modal', null, { noconfirm: true })

        await nextTick()

        const dialog = wrapper.find('.dialog-root')
        const buttons = wrapper.findAll('button')

        expect(dialog.attributes('aria-describedby')).toBeUndefined()
        expect(buttons).toHaveLength(1)

        await buttons[0].trigger('click')
        await expect(resolution).resolves.toBe(true)
    })
})
