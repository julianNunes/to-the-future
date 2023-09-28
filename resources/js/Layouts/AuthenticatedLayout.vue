<script setup>
import NavigationMenu from '@/Components/NavigationMenu.vue'
</script>

<template>
    <v-app class="bg-grey-lighten-4">
        <v-navigation-drawer v-model="drawer" :rail="rail" permanent>
            <v-list>
                <v-list-item
                    :prepend-avatar="avatar"
                    :title="$page.props.auth.user.name"
                    :subtitle="$page.props.auth.user.email"
                />
            </v-list>
            <v-divider />
            <NavigationMenu />
        </v-navigation-drawer>
        <v-app-bar color="light-green">
            <v-app-bar-nav-icon v-if="$vuetify.display.mobile" @click.stop="drawer = !drawer" />
            <v-app-bar-nav-icon v-else @click.stop="rail = !rail" />
            <v-toolbar-title text="To the Future" />
        </v-app-bar>
        <v-main>
            <v-container>
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import md5 from 'crypto-js/md5'
import { useToast } from 'vue-toastification'

export default {
    data() {
        return {
            drawer: false,
            rail: false,
        }
    },
    computed: {
        avatar() {
            return `https://www.gravatar.com/avatar/${md5(this.$page.props.auth.user.email)}?s=200`
        },
    },
    watch: {
        $page: {
            handler() {
                const toast = useToast()
                const flash = this.$page.props.flash
                if (flash.success) {
                    toast.success(this.$t(flash.success))
                } else if (flash.error) {
                    toast.error(this.$t(flash.error))
                } else if (this.$page.props.errors) {
                    toast.error(this.$t(this.$page.props.errors.error))
                }
            },
        },
    },
    mounted() {
        this.drawer = !this.$vuetify.display.mobile
    },
}
</script>
