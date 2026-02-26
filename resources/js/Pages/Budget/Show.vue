<template>
    <Head title="Budget Show" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">
                {{ $t('budget-show.title') }}
            </h5>
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
                    />
                </v-col>
                <v-col md="6" class="mt-2">
                    <v-btn color="primary" @click="confirmIncludeFixExpenses">
                        {{ $t('budget-show.include-fix-expense') }}
                    </v-btn>
                    <v-btn class="ml-2" color="primary" @click="confirmIncludeProvisionss">
                        {{ $t('budget-show.include-provision') }}
                    </v-btn>
                </v-col>
                <v-col md="3" class="mt-2" />
                <v-col cols="3" sm="3" md="3">
                    <v-text-field
                        v-model="budgetWeek1"
                        :label="$t('default.week-1')"
                        density="comfortable"
                        :readonly="true"
                    />
                </v-col>
                <v-col cols="3" sm="3" md="3">
                    <v-text-field
                        v-model="budgetWeek2"
                        :label="$t('default.week-2')"
                        density="comfortable"
                        :readonly="true"
                    />
                </v-col>
                <v-col cols="3" sm="3" md="3">
                    <v-text-field
                        v-model="budgetWeek3"
                        :label="$t('default.week-3')"
                        density="comfortable"
                        :readonly="true"
                    />
                </v-col>
                <v-col cols="3" sm="3" md="3">
                    <v-text-field
                        v-model="budgetWeek4"
                        :label="$t('default.week-4')"
                        density="comfortable"
                        :readonly="true"
                    />
                </v-col>
            </v-row>
        </v-card>

        <!-- Tabs -->
        <v-card class="mt-2">
            <v-tabs v-model="tab" bg-color="light-green" density="comfortable">
                <v-tab value="one">
                    {{ $t('budget-show.my-budget') }}
                </v-tab>
                <v-tab v-if="shareUser" value="two">
                    {{ shareUserName }}
                </v-tab>
            </v-tabs>
        </v-card>

        <!-- Conteudo Principal -->
        <div>
            <v-window v-model="tab">
                <v-window-item value="one">
                    <BudgetResume :resume="budgetResume" />
                    <BudgetExpenseTags :expense-to-tags="budgetExpanseToTags" />
                    <BudgetExpenseTagOptions
                        :budget-id="budgetId"
                        :tags-options="budgetExpenseToTagOptions"
                        :tags-options-chats="budgetExpenseToTagOptionCharts"
                    />
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
                        :budget-weeks="budgetWeeks"
                    />
                    <ExtractExpense
                        v-for="extract in budgetExtracts"
                        :key="extract.id"
                        :extract="extract"
                        :share-users="shareUsers"
                        :title-card="true"
                        :year-month="yearMonthModel"
                        :budget-weeks="budgetWeeks"
                    />
                    <BudgetProvision
                        :budget-id="budgetId"
                        :year-month="yearMonthModel"
                        :provisions="budgetProvisions"
                        :share-users="shareUsers"
                        :budget-weeks="budgetWeeks"
                    />
                </v-window-item>
                <v-window-item v-if="shareUser && budgetShareId" value="two">
                    <BudgetResume :resume="budgetShareResume" />
                    <BudgetExpenseTags :expense-to-tags="budgetShareExpanseToTags" />
                    <BudgetExpenseTagOptions
                        :tags-options="budgetShareExpenseToTagOptions"
                        :tags-options-chats="budgetShareExpenseToTagOptionCharts"
                        :view-only="true"
                    />
                    <BudgetGoal :goals="budgetShareGoals" :goals-charts="budgetShareGoalsCharts" :view-only="true" />
                    <BudgetExpense :expenses="budgetShareExpenses" :view-only="true" />
                    <BudgetIncome :year-month="yearMonthModel" :incomes="budgetShareIncomes" :view-only="true" />
                    <InvoiceExpense
                        v-for="invoice in budgetShareInvoices"
                        :key="invoice.id"
                        :invoice="invoice"
                        :share-users="shareUsers"
                        :budget-weeks="budgetShareWeeks"
                        :title-card="true"
                        :view-only="true"
                    />
                    <ExtractExpense
                        v-for="extract in budgetShareExtracts"
                        :key="extract.id"
                        :extract="extract"
                        :share-users="shareUsers"
                        :budget-weeks="budgetShareWeeks"
                        :title-card="true"
                        :view-only="true"
                    />
                    <BudgetProvision
                        :year-month="yearMonthModel"
                        :provisions="budgetShareProvisions"
                        :budget-weeks="budgetShareWeeks"
                        :view-only="true"
                    />
                </v-window-item>
            </v-window>
        </div>

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
    import { ref, computed } from 'vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import BudgetExpense from '@/Components/Budget/BudgetExpense.vue'
    import BudgetExpenseTagOptions from '@/Components/Budget/BudgetExpenseTagOptions.vue'
    import BudgetExpenseTags from '@/Components/Budget/BudgetExpenseTags.vue'
    import BudgetGoal from '@/Components/Budget/BudgetGoal.vue'
    import BudgetIncome from '@/Components/Budget/BudgetIncome.vue'
    import BudgetProvision from '@/Components/Budget/BudgetProvision.vue'
    import BudgetResume from '@/Components/Budget/BudgetResume.vue'
    import InvoiceExpense from '@/Components/CreditCardInvoice/InvoiceExpense.vue'
    import ExtractExpense from '@/Components/PrepaidCardExtract/ExtractExpense.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
    import { logger } from '@/utils/logger.js'
    import { Head, router } from '@inertiajs/vue3'
    import { useI18n } from 'vue-i18n'
    import moment from 'moment'

    defineOptions({ name: 'BudgetShow' })

    const props = defineProps({
        installments: { type: Array },
        shareUser: { type: Object },
        shareUsers: { type: Array },
        owner: { type: Object },
        share: { type: Object },
    })

    const { t } = useI18n()
    const confirm = ref(null)
    const tab = ref(null)
    const isLoading = ref(false)

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.budget'), disabled: false, href: '/budget/' + moment().format('YYYY') },
        { title: t('budget-show.title'), disabled: true },
    ])

    const yearMonthModel = computed(() => props.owner.budget.year + '-' + props.owner.budget.month)
    const shareUserName = computed(() => props.shareUser?.share_user?.name)
    const budgetId = computed(() => props.owner.budget.id)

    function buildWeeks(budget) {
        const weeks = []
        for (let i = 1; i <= 4; i++) {
            const start = budget[`start_week_${i}`]
            const end = budget[`end_week_${i}`]
            if (start && end) {
                weeks.push({
                    text: t('budget.range-week', {
                        start: moment(start).format('DD/MM/YYYY'),
                        end: moment(end).format('DD/MM/YYYY'),
                    }),
                    value: `WEEK_${i}`,
                })
            }
        }
        return weeks
    }

    function weekLabel(budget, week) {
        const start = budget[`start_week_${week}`]
        const end = budget[`end_week_${week}`]
        if (start && end) {
            return t('budget.range-week', {
                start: moment(start).format('DD/MM/YYYY'),
                end: moment(end).format('DD/MM/YYYY'),
            })
        }
        return null
    }

    const budgetWeeks = computed(() => buildWeeks(props.owner.budget))
    const budgetWeek1 = computed(() => weekLabel(props.owner.budget, 1))
    const budgetWeek2 = computed(() => weekLabel(props.owner.budget, 2))
    const budgetWeek3 = computed(() => weekLabel(props.owner.budget, 3))
    const budgetWeek4 = computed(() => weekLabel(props.owner.budget, 4))

    const budgetResume = computed(() => props.owner.resume)
    const budgetExpanseToTags = computed(() => props.owner.expenseToTags)
    const budgetExpenseToTagOptions = computed(() => {
        logger.log('budgetExpenseToTagOptions ', props.owner.budget.expenseTagOptions)
        return props.owner.budget.expenseTagOptions
    })
    const budgetExpenseToTagOptionCharts = computed(() => {
        logger.log('budgetExpenseToTagOptionCharts ', props.owner.budget.expenseToTagOptionCharts)
        return props.owner.expenseToTagOptionCharts
    })
    const budgetExpenses = computed(() => props.owner.budget.expenses)
    const budgetIncomes = computed(() => props.owner.budget.incomes)
    const budgetProvisions = computed(() => props.owner.budget.provisions)
    const budgetInvoices = computed(() => props.owner.budget.invoices)
    const budgetExtracts = computed(() => props.owner.budget.extracts)
    const budgetGoals = computed(() => props.owner.budget.goals)
    const budgetGoalsCharts = computed(() => props.owner.goalsCharts)

    const budgetShareId = computed(() => props.share?.budget?.id)
    const budgetShareWeeks = computed(() => (props.share?.budget ? buildWeeks(props.share.budget) : []))
    const budgetShareResume = computed(() => props.share?.resume)
    const budgetShareExpanseToTags = computed(() => props.share?.expenseToTags)
    const budgetShareExpenseToTagOptions = computed(() => {
        logger.log('budgetShareExpenseToTagOptions ', props.share?.budget?.expenseTagOptions)
        return props.share?.budget?.expenseTagOptions
    })
    const budgetShareExpenseToTagOptionCharts = computed(() => {
        logger.log('budgetShareExpenseToTagOptionCharts ', props.share?.budget?.expenseToTagOptionCharts)
        return props.share?.expenseToTagOptionCharts
    })
    const budgetShareExpenses = computed(() => props.share?.budget?.expenses)
    const budgetShareIncomes = computed(() => props.share?.budget?.incomes)
    const budgetShareProvisions = computed(() => props.share?.budget?.provisions)
    const budgetShareInvoices = computed(() => props.share?.budget?.invoices)
    const budgetShareExtracts = computed(() => props.share?.budget?.extracts)
    const budgetShareGoals = computed(() => props.share?.budget?.goals)
    const budgetShareGoalsCharts = computed(() => props.share?.goalsCharts)

    function changeYearMonth(event) {
        const year = event.target.value.substring(0, 4)
        const month = event.target.value.substring(5, 7)
        router.get(`/budget/find/${year}/${month}`)
    }

    async function confirmIncludeFixExpenses() {
        if (
            await confirm.value.open(t('budget-show.include-fix-expense'), t('budget-show.confirm-include-fix-expense'))
        ) {
            includeFixExpenses()
        }
    }

    function includeFixExpenses() {
        isLoading.value = true
        router.post(
            `/budget/${props.owner.budget.id}/include-fix-expenses`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
            }
        )
    }

    async function confirmIncludeProvisionss() {
        if (await confirm.value.open(t('budget-show.include-provision'), t('budget-show.confirm-include-provision'))) {
            includeProvisions()
        }
    }

    function includeProvisions() {
        isLoading.value = true
        router.post(
            `/budget/${props.owner.budget.id}/include-provisions`,
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
            }
        )
    }
</script>
