<template>
    <v-list nav>
        <!-- List Menu -->
        <Link v-for="(item, key) in items" :key="key" :href="item.to" as="div">
            <v-list-item
                :prepend-icon="item.icon"
                :title="item.title"
                :exact="item.exact"
                link
                :class="{ 'v-list-item--active': $page.url.startsWith(item.to) }"
            />
        </Link>

        <!-- Log Out -->
        <Link
            href="/logout"
            method="post"
            as="div"
            role="button"
            tabindex="0"
            :aria-label="$t('default.logout')"
            @keydown.enter.prevent="$event.currentTarget.click()"
            @keydown.space.prevent="$event.currentTarget.click()"
        >
            <v-list-item prepend-icon="mdi-exit-to-app" :title="$t('default.logout')" link />
        </Link>
    </v-list>
</template>

<script setup>
    import { Link } from '@inertiajs/vue3'
import moment from 'moment'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'NavigationMenu' })

    const { t } = useI18n()

    const items = computed(() => [
        {
            title: t('menus.dashboard'),
            icon: 'mdi-view-dashboard',
            to: '/dashboard',
        },
        {
            title: t('menus.tags'),
            icon: 'mdi-tag',
            to: '/tag',
        },
        {
            title: t('menus.provision'),
            icon: 'mdi-cash-lock',
            to: '/provision',
        },

        {
            title: t('menus.fix-expense'),
            icon: 'mdi-cash-lock',
            to: '/fix-expense',
        },
        {
            title: t('menus.people'),
            icon: 'mdi-account-group',
            to: '/people',
        },
        {
            title: t('menus.financing'),
            icon: 'mdi-cash-multiple',
            to: '/financing',
        },
        {
            title: t('menus.credit-card'),
            icon: 'mdi-credit-card',
            to: '/credit-card',
        },
        {
            title: t('menus.prepaid-card'),
            icon: 'mdi-credit-card',
            to: '/prepaid-card',
        },
        {
            title: t('menus.budget'),
            icon: 'mdi-credit-card',
            to: '/budget/' + moment().format('YYYY'),
        },
    ])
</script>
