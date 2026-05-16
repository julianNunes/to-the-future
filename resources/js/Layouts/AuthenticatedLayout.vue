<script setup>
    import NavigationMenu from '@/Components/NavigationMenu.vue'
import { usePage } from '@inertiajs/vue3'
import md5 from 'crypto-js/md5'
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useDisplay } from 'vuetify'

    const drawer = ref(false)
    const rail = ref(false)

    const page = usePage()
    const { t } = useI18n()
    const { mobile } = useDisplay()
    const toast = useToast()

    const avatar = computed(() => {
        return `https://www.gravatar.com/avatar/${md5(page.props.auth.user.email)}?s=200`
    })

    watch(
        () => page.props,
        (newProps) => {
            const flash = newProps.flash || {}
            if (flash.success) {
                toast.success(t(flash.success))
            } else if (flash.error) {
                toast.error(t(flash.error))
            } else if (newProps.errors && newProps.errors.error) {
                toast.error(t(newProps.errors.error))
            }
        },
        { deep: true }
    )

    onMounted(() => {
        drawer.value = !mobile.value
    })
</script>

<template>
    <v-app class="bg-grey-lighten-4">
        <a class="skip-link" href="#main-content">{{ t('default.skip-to-content') }}</a>
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
            <v-app-bar-nav-icon
                v-if="$vuetify.display.mobile"
                :aria-label="t('default.open-navigation')"
                @click.stop="drawer = !drawer"
            />
            <v-app-bar-nav-icon v-else :aria-label="t('default.toggle-navigation')" @click.stop="rail = !rail" />
            <v-toolbar-title text="To the Future" />
        </v-app-bar>
        <v-main id="main-content" tabindex="-1">
            <v-container id="v-container">
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>
