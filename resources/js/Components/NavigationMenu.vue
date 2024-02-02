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
        <Link href="/logout" method="post" as="div">
            <v-list-item prepend-icon="mdi-exit-to-app" title="Log Out" link />
        </Link>
    </v-list>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<script>
import moment from 'moment'

export default {
    data() {
        return {
            items: [
                {
                    title: this.$t('menus.dashboard'),
                    icon: 'mdi-view-dashboard',
                    to: '/dashboard',
                },
                {
                    title: this.$t('menus.tags'),
                    icon: 'mdi-tag',
                    to: '/tag',
                },
                {
                    title: this.$t('menus.provision'),
                    icon: 'mdi-cash-lock',
                    to: '/provision',
                },

                {
                    title: this.$t('menus.fix-expense'),
                    icon: 'mdi-cash-lock',
                    to: '/fix-expense',
                },
                {
                    title: this.$t('menus.financing'),
                    icon: 'mdi-cash-multiple',
                    to: '/financing',
                },
                {
                    title: this.$t('menus.credit-card'),
                    icon: 'mdi-credit-card',
                    to: '/credit-card',
                },
                {
                    title: this.$t('menus.budget'),
                    icon: 'mdi-credit-card',
                    to: '/budget/' + moment().format('YYYY'),
                },
            ],
        }
    },
}
</script>
