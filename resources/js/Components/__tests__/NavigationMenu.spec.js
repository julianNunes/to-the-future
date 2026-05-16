import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import NavigationMenu from '../NavigationMenu.vue'

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        inheritAttrs: false,
        props: ['as'],
        template: '<component :is="as || \'a\'" v-bind="$attrs"><slot /></component>',
    },
}))

vi.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key) => key,
    }),
}))

describe('NavigationMenu', () => {
    it('renders logout as a labelled keyboard target', () => {
        const wrapper = mount(NavigationMenu, {
            global: {
                mocks: {
                    $page: { url: '/dashboard' },
                    $t: (key) => key,
                },
                stubs: {
                    'v-list': { template: '<nav><slot /></nav>' },
                    'v-list-item': { props: ['title'], template: '<span>{{ title }}</span>' },
                },
            },
        })

        const logout = wrapper.find('[aria-label="default.logout"]')

        expect(logout.exists()).toBe(true)
        expect(logout.attributes('role')).toBe('button')
        expect(logout.attributes('tabindex')).toBe('0')
        expect(logout.text()).toContain('default.logout')
    })
})
