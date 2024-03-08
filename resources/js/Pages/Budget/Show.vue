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
                    <v-btn color="primary" @click="confirmIncludeFixExpenses">{{
                        $t('budget-show.include-fix-expense')
                    }}</v-btn>
                    <v-btn class="ml-2" color="primary" @click="confirmIncludeProvisionss">{{
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
                    <BudgetResume :resume="budgetResume" />
                    <BudgetExpenseTags :expense-to-tags="budgetExpanseToTags" />
                    <BudgetGoal :budget-id="budgetId" :goals="budgetGoals" :goals-charts="budgetGoalsCharts" />
                    <BudgetExpense
                        :budget-id="budgetId"
                        :year-month="yearMonthModel"
                        :expenses="budgetExpenses"
                        :share-users="shareUsers"
                        :installments="installments"
                    />
                    <BudgetIncome :budget-id="budgetId" :year-month="yearMonthModel" :incomes="budgetIncomes" />
                    <InvoiceExpense
                        v-for="invoice in budgetInvoices"
                        :key="invoice.id"
                        :invoice="invoice"
                        :share-users="shareUsers"
                        :title-card="true"
                        :year-month="yearMonthModel"
                    />
                    <BudgetProvision
                        :budget-id="budgetId"
                        :year-month="yearMonthModel"
                        :provisions="budgetProvisions"
                        :share-users="shareUsers"
                    />
                </v-window-item>
                <v-window-item v-if="shareUser" value="two">
                    <BudgetResume :resume="budgetShareResume" />
                    <BudgetExpenseTags :expense-to-tags="budgetShareExpanseToTags" />
                    <BudgetGoal
                        :budget-id="budgetId"
                        :goals="budgetShareGoals"
                        :goals-charts="budgetShareGoalsCharts"
                        :view-only="true"
                    />
                    <BudgetExpense :expenses="budgetShareExpenses" :view-only="true" />
                    <BudgetIncome :year-month="yearMonthModel" :incomes="budgetShareIncomes" :view-only="true" />
                    <InvoiceExpense
                        v-for="invoice in budgetShareInvoices"
                        :key="invoice.id"
                        :invoice="invoice"
                        :share-users="shareUsers"
                        :title-card="true"
                        :view-only="true"
                    />
                    <BudgetProvision
                        :year-month="yearMonthModel"
                        :provisions="budgetShareProvisions"
                        :view-only="true"
                    />
                </v-window-item>
            </v-window>
        </div>

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import BudgetGoal from '../../Components/Budget/BudgetGoal.vue'
import BudgetExpenseTags from '../../Components/Budget/BudgetExpenseTags.vue'
import BudgetResume from '../../Components/Budget/BudgetResume.vue'
import BudgetExpense from '../../Components/Budget/BudgetExpense.vue'
import BudgetIncome from '../../Components/Budget/BudgetIncome.vue'
import BudgetProvision from '../../Components/Budget/BudgetProvision.vue'
import InvoiceExpense from '../../Components/CreditCardInvoice/InvoiceExpense.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
</script>

<script>
import moment from 'moment'

export default {
    name: 'BudgetShow',

    components: {
        BudgetExpenseTags,
        BudgetGoal,
        BudgetResume,
        BudgetExpense,
        BudgetIncome,
        BudgetProvision,
        InvoiceExpense,
    },

    props: {
        installments: {
            type: Array,
        },
        shareUser: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
        owner: {
            type: Object,
        },
        share: {
            type: Object,
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
            return this.owner.budget.year + '-' + this.owner.budget.month
        },
        shareUserName() {
            return this.shareUser.share_user.name
        },
        budgetId() {
            return this.owner.budget.id
        },
        budgetResume() {
            return this.owner.resume
        },
        budgetExpanseToTags() {
            return this.owner.expenseToTags
        },
        budgetExpenses() {
            return this.owner.budget.expenses
        },
        budgetIncomes() {
            return this.owner.budget.incomes
        },
        budgetProvisions() {
            return this.owner.budget.provisions
        },
        budgetInvoices() {
            return this.owner.budget.invoices
        },
        budgetGoals() {
            return this.owner.budget.goals
        },
        budgetGoalsCharts() {
            return this.owner.goalsCharts
        },
        budgetShareResume() {
            return this.share.resume
        },
        budgetShareExpanseToTags() {
            return this.share.expenseToTags
        },
        budgetShareExpenses() {
            return this.share.budget.expenses
        },
        budgetShareIncomes() {
            return this.share.budget.incomes
        },
        budgetShareProvisions() {
            return this.share.budget.provisions
        },
        budgetShareInvoices() {
            return this.share.budget.invoices
        },
        budgetShareGoals() {
            return this.share.budget.goals
        },
        budgetShareGoalsCharts() {
            return this.share.goalsCharts
        },
    },

    methods: {
        changeYearMonth(event) {
            let year = event.target.value.substring(0, 4)
            let month = event.target.value.substring(5, 7)

            this.$inertia.get(`/budget/find/${year}/${month}`)
        },

        async confirmIncludeFixExpenses() {
            if (
                await this.$refs.confirm.open(
                    this.$t('budget-show.include-fix-expense'),
                    this.$t('budget-show.confirm-include-fix-expense')
                )
            ) {
                this.includeFixExpenses()
            }
        },

        includeFixExpenses() {
            this.isLoading = true
            this.$inertia.post(`/budget/${this.owner.budget.id}/include-fix-expenses`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {},
                onError: () => {
                    this.isLoading = false
                },
                onFinish: () => {
                    this.isLoading = false
                },
            })
        },

        async confirmIncludeProvisionss() {
            if (
                await this.$refs.confirm.open(
                    this.$t('budget-show.include-provision'),
                    this.$t('budget-show.confirm-include-provision')
                )
            ) {
                this.includeProvisions()
            }
        },

        includeProvisions() {
            this.isLoading = true
            this.$inertia.post(`/budget/${this.owner.budget.id}/include-provisions`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {},
                onError: () => {
                    this.isLoading = false
                },
                onFinish: () => {
                    this.isLoading = false
                },
            })
        },
    },
}
</script>
