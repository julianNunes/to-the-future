<template>
    <Head title="Credit Card Invoice" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('credit-card-invoice.title-show') }}</h5>
        </div>

        <!-- Componente da Fatura -->
        <InvoiceExpense :invoice="invoice" :share-users="shareUsers" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import InvoiceExpense from '../../Components/CreditCardInvoice/InvoiceExpense.vue'
</script>

<script>
export default {
    name: 'CreditCardInvoiceShow',
    props: {
        invoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
    },

    data() {
        return {
            isLoading: false,
        }
    },
}
</script>
