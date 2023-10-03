<template>
    <Head title="Credit Card Invoice" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('credit-card-invoice.title-show') }}</h5>
        </div>

        <!-- Componente da Fatura -->
        <InvoiceExpense :credit-card-invoice="creditCardInvoice" :share-users="shareUsers" />
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
        creditCardInvoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [
                { title: this.$t('credit-card-invoice.closing-date'), key: 'closing_date' },
                { title: this.$t('credit-card-invoice.due-date'), key: 'due_date' },
                { title: 'Total', key: 'total' },
                { title: this.$t('default.total-paid'), key: 'total_paid' },
                { title: this.$t('credit-card-invoice.closed'), key: 'closed' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.action'), key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
            },
            modalYear: false,
            automaticGenerate: false,
            yearMonth: null,
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            invoice: this.creditCardInvoice,
        }
    },

    async created() {},

    async mounted() {},

    methods: {},
}
</script>
