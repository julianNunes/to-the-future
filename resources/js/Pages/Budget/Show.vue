<template>
    <Head title="Budget Show" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('budget-show.title') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>

        <!-- Cabeçalho com botões base -->
        <v-card class="pa-4">
            <v-row dense>
                <v-col cols="12" md="3">
                    <v-text-field
                        ref="selectMonthYear"
                        :model-value="yearMonthModel"
                        type="month"
                        :label="$t('default.year-month')"
                        clearable
                        density="comfortable"
                        @change="changeYearMonth"
                    ></v-text-field>
                </v-col>
                <v-col md="6" class="mt-2">
                    <v-btn color="primary" @click="addFixExpense">{{ $t('budget-show.include-fix-expense') }}</v-btn>
                    <v-btn class="ml-2" color="primary" @click="addProvision">{{
                        $t('budget-show.include-provision')
                    }}</v-btn>
                </v-col>
                <v-col md="3" class="mt-2"> </v-col>
            </v-row>
        </v-card>

        <!-- Tabs -->
        <v-card class="mt-2">
            <v-tabs v-model="tab" bg-color="light-green" density="comfortable">
                <v-tab value="one">{{ $t('budget-show.my-budget') }}</v-tab>
                <v-tab v-if="shareUser" value="two">{{ shareUserName }}</v-tab>
            </v-tabs>
        </v-card>

        <!-- Conteudo Principal -->
        <div>
            <v-window v-model="tab">
                <v-window-item value="one">
                    <BudgetExpense
                        :budget-id="budgetId"
                        :year-month="yearMonthModel"
                        :expenses="budgetExpenses"
                        :share-users="shareUsers"
                        :installments="installments"
                    />
                    <BudgetIncome :budget-id="budgetId" :year-month="yearMonthModel" :incomes="budgetIncomes" />
                    <InvoiceExpense
                        v-for="invoice in creditCardInvoices"
                        :key="invoice.id"
                        :invoice="invoice"
                        :share-users="shareUsers"
                        :title-card="true"
                    />
                </v-window-item>
                <v-window-item v-if="shareUser" value="two">
                    <BudgetExpense :expenses="budgetShareExpenses" :view-only="true" />
                    <BudgetIncome
                        :budget-id="budgetId"
                        :year-month="yearMonthModel"
                        :incomes="budgetShareIncomes"
                        :view-only="true"
                    />
                    <InvoiceExpense
                        v-for="invoice in creditCardInvoicesShare"
                        :key="invoice.id"
                        :invoice="invoice"
                        :share-users="shareUsers"
                        :title-card="true"
                        :view-only="true"
                    />
                </v-window-item>
            </v-window>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import BudgetExpense from '../../Components/Budget/BudgetExpense.vue'
import BudgetIncome from '../../Components/Budget/BudgetIncome.vue'
import InvoiceExpense from '../../Components/CreditCardInvoice/InvoiceExpense.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
</script>

<script>
import moment from 'moment'

export default {
    name: 'BudgetShow',

    components: {
        BudgetExpense,
        BudgetIncome,
    },

    props: {
        budget: {
            type: Object,
        },
        creditCardInvoices: {
            type: Array,
        },
        budgetShare: {
            type: Object,
        },
        creditCardInvoicesShare: {
            type: Array,
        },
        shareUser: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
        installments: {
            type: Array,
        },
        goalsCharts: {
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
                    title: this.$t('menus.budget'),
                    disabled: false,
                    href: '/budget/' + moment().format('YYYY'),
                },
                {
                    title: this.$t('budget-show.title'),
                    disabled: true,
                },
            ],
            tab: null,
            isLoading: false,
            yearMonth: null,
        }
    },

    computed: {
        yearMonthModel() {
            return this.budget.year + '-' + this.budget.month
        },
        budgetId() {
            return this.budget.id
        },
        budgetExpenses() {
            return this.budget.expenses
        },
        budgetIncomes() {
            return this.budget.incomes
        },
        budgetShareExpenses() {
            return this.budgetShare.expenses
        },
        budgetShareIncomes() {
            return this.budgetShare.incomes
        },
        shareUserName() {
            return this.shareUser.name
        },
    },

    methods: {
        changeYearMonth(event) {
            console.log('value', event.target.value)
            let year = event.target.value.substring(0, 4)
            let month = event.target.value.substring(5, 7)

            this.$inertia.get(`/budget/find/${year}/${month}`)
        },

        addFixExpense() {
            return false
        },

        addProvision() {
            return false
        },
    },
}
</script>
