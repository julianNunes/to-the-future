<template>
    <Head title="Prepaid Card Extract" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('prepaid-card-extract.title-show') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>

        <!-- Componente da Fatura -->
        <ExtractExpense :extract="extract" :share-users="shareUsers" />
    </AuthenticatedLayout>
</template>

<script setup>
    import { computed } from 'vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import ExtractExpense from '@/Components/PrepaidCardExtract/ExtractExpense.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { Head } from '@inertiajs/vue3'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'PrepaidCardExtractShow' })

    const props = defineProps({
        extract: { type: Object },
        shareUsers: { type: Array },
    })

    const { t } = useI18n()

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.prepaid-card'), disabled: false, href: '/prepaid-card' },
        {
            title: t('prepaid-card-extract.title-index'),
            disabled: false,
            href: '/prepaid-card/' + props.extract.prepaid_card_id + '/extract',
        },
        { title: t('prepaid-card-extract-expense.title'), disabled: true },
    ])
</script>
