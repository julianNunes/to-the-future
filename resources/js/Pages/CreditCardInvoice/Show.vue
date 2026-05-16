<template>
    <Head title="Credit Card Invoice" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('credit-card-invoice.title-show') }}</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <!-- Componente da Fatura -->
    <InvoiceExpense :invoice="invoice" :share-users="shareUsers" />
</template>

<script setup>
    import { computed } from 'vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import InvoiceExpense from '@/Components/CreditCardInvoice/InvoiceExpense.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { Head } from '@inertiajs/vue3'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'CreditCardInvoiceShow', layout: AuthenticatedLayout })

    const componentProps = defineProps({
        invoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
    })

    const { t } = useI18n()

    const breadcrumbs = computed(() => [
        {
            title: t('menus.dashboard'),
            disabled: false,
            href: '/dashboard',
        },
        {
            title: t('menus.credit-card'),
            disabled: false,
            href: '/credit-card',
        },
        {
            title: t('credit-card-invoice.title-index'),
            disabled: false,
            href: '/credit-card/' + componentProps.invoice.credit_card_id + '/invoice',
        },
        {
            title: t('credit-card-invoice-expense.title'),
            disabled: true,
        },
    ])
</script>
