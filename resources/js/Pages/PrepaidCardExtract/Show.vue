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
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import ExtractExpense from '@/Components/PrepaidCardExtract/ExtractExpense.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { Head } from '@inertiajs/vue3'
</script>

<script>
    export default {
        name: 'PrepaidCardExtractShow',
        props: {
            extract: {
                type: Object,
            },
            shareUsers: {
                type: Array,
            },
        },

        data() {
            return {
                breadcrumbs: [
                    {
                        title: this.$t('menus.dashboard'),
                        disabled: false,
                        href: '/dashboard',
                    },
                    {
                        title: this.$t('menus.prepaid-card'),
                        disabled: false,
                        href: '/prepaid-card',
                    },
                    {
                        title: this.$t('prepaid-card-extract.title-index'),
                        disabled: false,
                        href: '/prepaid-card/' + this.extract.prepaid_card_id + '/extract',
                    },
                    {
                        title: this.$t('prepaid-card-extract-expense.title'),
                        disabled: true,
                    },
                ],
                isLoading: false,
            }
        },
    }
</script>
