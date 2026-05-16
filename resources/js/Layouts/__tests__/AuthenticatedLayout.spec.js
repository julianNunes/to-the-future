import { mount } from '@vue/test-utils'
import { describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import AuthenticatedLayout from '../AuthenticatedLayout.vue'

vi.mock('@/Components/NavigationMenu.vue', () => ({
    default: { name: 'NavigationMenu', template: '<nav />' },
}))

vi.mock('crypto-js/md5', () => ({
    default: () => 'avatar-hash',
}))

vi.mock('vue-toastification', () => ({
    useToast: () => ({ success: vi.fn(), error: vi.fn() }),
}))

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({
        props: {
            auth: {
                user: {
                    name: 'Jane',
                    email: 'jane@example.com',
                },
            },
            flash: {},
            errors: {},
        },
    }),
}))

vi.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key) => key,
    }),
}))

vi.mock('vuetify', () => ({
    useDisplay: () => ({ mobile: ref(false) }),
}))

function mountLayout() {
    return mount(AuthenticatedLayout, {
        global: {
            mocks: {
                $vuetify: { display: { mobile: false } },
                $page: {
                    props: {
                        auth: {
                            user: {
                                name: 'Jane',
                                email: 'jane@example.com',
                            },
                        },
                    },
                },
            },
            stubs: {
                'v-app': { template: '<div><slot /></div>' },
                'v-navigation-drawer': { template: '<aside><slot /></aside>' },
                'v-list': { template: '<div><slot /></div>' },
                'v-list-item': { props: ['title', 'subtitle'], template: '<div>{{ title }} {{ subtitle }}</div>' },
                'v-divider': { template: '<hr />' },
                'v-app-bar': { template: '<header><slot /></header>' },
                'v-app-bar-nav-icon': {
                    emits: ['click'],
                    template: '<button v-bind="$attrs" @click="$emit(\'click\')"></button>',
                },
                'v-toolbar-title': { props: ['text'], template: '<h1>{{ text }}</h1>' },
                'v-main': { template: '<main v-bind="$attrs"><slot /></main>' },
                'v-container': { template: '<div v-bind="$attrs"><slot /></div>' },
            },
        },
        slots: {
            default: '<section>Dashboard</section>',
        },
    })
}

describe('AuthenticatedLayout', () => {
    it('exposes a skip link and labelled navigation toggle', () => {
        const wrapper = mountLayout()

        expect(wrapper.find('.skip-link').attributes('href')).toBe('#main-content')
        expect(wrapper.find('#main-content').attributes('tabindex')).toBe('-1')
        expect(wrapper.find('button').attributes('aria-label')).toBe('default.toggle-navigation')
    })
})
