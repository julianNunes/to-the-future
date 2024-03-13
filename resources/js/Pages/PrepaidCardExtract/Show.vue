<template>
    <Head title="Credit Card Invoice" />
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import ExtractExpense from '../../Components/PrepaidCardExtract/ExtractExpense.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
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
                    title: this.$t('menus.credit-card'),
                    disabled: false,
                    href: '/credit-card',
                },
                {
                    title: this.$t('prepaid-card-extract.title-index'),
                    disabled: false,
                    href: '/credit-card/' + this.invoice.credit_card_id + '/invoice',
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
