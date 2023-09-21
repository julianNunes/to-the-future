<template>
    <v-list nav>
        <!-- List Menu -->
        <Link v-for="(item, key) in navigation.items" :key="key" :href="item.to" as="div">
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
import navigation from '@/Configs/navigation'
</script>

<script>
export default {
    async created() {
        for (let item of navigation.items) {
            item.title = this.$t(item.title)
        }
    },
}
</script>
