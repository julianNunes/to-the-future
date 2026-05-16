<template>
    <Head title="Budget" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('budget.title') }}</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <v-card>
        <v-card-text>
            <v-row dense>
                <v-col cols="12" sm="12" md="2">
                    <v-text-field
                        ref="inputYear"
                        :model-value="yearModel"
                        :label="$t('default.year')"
                        type="number"
                        min="1970"
                        max="2050"
                        step="1"
                        :rules="rules.textFieldRules"
                        required
                        density="comfortable"
                        @change="changeYear"
                    ></v-text-field>
                </v-col>
                <v-col md="10">
                    <v-btn color="primary" class="mt-2" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="budgets"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="budgets.length"
                        :items-per-page="12"
                        :no-data-text="$t('default.no-data-text')"
                        :no-results-text="$t('default.no-data-text')"
                        :footer-props="{
                            'items-per-page-text': $t('default.itens-per-page'),
                            'page-text': $t('default.page-text'),
                        }"
                        :header-props="{
                            sortByText: $t('default.sort-by'),
                        }"
                        fixed-header
                    >
                        <template #[`item.total_expense`]="{ item }">{{ currencyField(item.total_expense) }}</template>
                        <template #[`item.total_income`]="{ item }">{{ currencyField(item.total_income) }}</template>
                        <template #[`item.closed`]="{ item }">{{
                            item.closed ? $t('default.yes') : $t('default.no')
                        }}</template>
                        <template #[`item.start_week_1`]="{ item }">{{
                            item.start_week_1 ? moment(item.start_week_1).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.end_week_1`]="{ item }">{{
                            item.end_week_1 ? moment(item.end_week_1).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.start_week_2`]="{ item }">{{
                            item.start_week_2 ? moment(item.start_week_2).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.end_week_2`]="{ item }">{{
                            item.end_week_2 ? moment(item.end_week_2).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.start_week_3`]="{ item }">{{
                            item.start_week_3 ? moment(item.start_week_3).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.end_week_3`]="{ item }">{{
                            item.end_week_3 ? moment(item.end_week_3).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.start_week_4`]="{ item }">{{
                            item.start_week_4 ? moment(item.start_week_4).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.end_week_4`]="{ item }">{{
                            item.end_week_4 ? moment(item.end_week_4).format('DD/MM/YYYY') : null
                        }}</template>
                        <template #[`item.action`]="{ item }">
                            <div style="width: 90px">
                                <v-tooltip :text="$t('default.show')" location="top">
                                    <template #activator="{ props: tooltipProps }">
                                        <Link :href="hrefBudgetShow(item)" class="v-breadcrumbs-item--link">
                                            <v-icon
                                                v-bind="tooltipProps"
                                                color="light-blue"
                                                icon="mdi-eye"
                                                size="small"
                                            >
                                            </v-icon>
                                        </Link>
                                    </template>
                                </v-tooltip>
                                <v-tooltip :text="$t('default.edit')" location="top">
                                    <template #activator="{ props: tooltipProps }">
                                        <v-icon
                                            v-bind="tooltipProps"
                                            color="warning"
                                            icon="mdi-pencil"
                                            size="small"
                                            class="ml-1"
                                            @click="editItem(item)"
                                        >
                                        </v-icon>
                                    </template>
                                </v-tooltip>
                                <v-tooltip :text="$t('budget.clone')" location="top">
                                    <template #activator="{ props: tooltipProps }">
                                        <v-icon
                                            v-bind="tooltipProps"
                                            color="green"
                                            icon="mdi-content-copy"
                                            size="small"
                                            class="ml-1"
                                            @click="cloneItem(item)"
                                        >
                                        </v-icon>
                                    </template>
                                </v-tooltip>
                                <v-tooltip :text="$t('default.delete')" location="top">
                                    <template #activator="{ props: tooltipProps }">
                                        <v-icon
                                            v-bind="tooltipProps"
                                            class="ml-1"
                                            color="error"
                                            icon="mdi-delete"
                                            size="small"
                                            @click="confirmRemove(item)"
                                        ></v-icon>
                                    </template>
                                </v-tooltip>
                            </div>
                        </template>

                        <template #top>
                            <v-toolbar density="comfortable">
                                <v-row dense>
                                    <v-col cols="12" lg="12" md="12" sm="12">
                                        <v-text-field
                                            v-model="search"
                                            :label="$t('default.search')"
                                            append-icon="mdi-magnify"
                                            single-line
                                            hide-details
                                            clearable
                                            @click:clear="search = null"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-toolbar>
                        </template>
                    </v-data-table>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>

    <!-- Dialog Criacao -->
    <v-dialog v-model="createDialog" persistent width="800">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ titleModal }}</span>
            </v-card-title>
            <v-card-text>
                <v-form ref="formCreate" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" md="4">
                            <v-text-field
                                ref="selectMonthYear"
                                v-model="budget.yearMonth"
                                type="month"
                                :label="$t('default.year-month')"
                                :readonly="budget.id ? true : false"
                                :clearable="budget.id ? false : true"
                                :rules="rules.textFieldRules"
                                :error-messages="budgetYearMonthErrors"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col offset="8" />
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                ref="txtStartWeek1"
                                v-model="budget.start_week_1"
                                :label="$t('budget.start_week_1')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.end_week_1) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.start_week_1"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                                @change="budget.end_week_1 = budget.start_week_1"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                ref="txtEndWeek1"
                                v-model="budget.end_week_1"
                                :label="$t('budget.end_week_1')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.start_week_1) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.end_week_1"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.start_week_2"
                                :label="$t('budget.start_week_2')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.end_week_2) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.start_week_2"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                                @change="budget.end_week_2 = budget.start_week_2"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.end_week_2"
                                :label="$t('budget.end_week_2')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.start_week_2) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.end_week_2"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.start_week_3"
                                :label="$t('budget.start_week_3')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.end_week_3) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.start_week_3"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                                @change="budget.end_week_3 = budget.start_week_3"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.end_week_3"
                                :label="$t('budget.end_week_3')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.start_week_3) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.end_week_3"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.start_week_4"
                                :label="$t('budget.start_week_4')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.end_week_4) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.start_week_4"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                                @change="budget.end_week_4 = budget.start_week_4"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="6" sm="6" md="6">
                            <v-date-input
                                v-model="budget.end_week_4"
                                :label="$t('budget.end_week_4')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                :rules="[
                                    (value) => {
                                        if (budget.start_week_4) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :error-messages="budget.errors.end_week_4"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col v-show="!budget.id" cols="12" md="12">
                            <v-checkbox
                                v-model="budget.automaticGenerateYear"
                                :label="$t('budget.automatic-generate')"
                                density="comfortable"
                            ></v-checkbox>
                        </v-col>
                        <v-col v-show="!budget.id" cols="12" md="12">
                            <v-checkbox
                                v-model="budget.includeFixExpenses"
                                :label="$t('budget.include-fix-expense')"
                                density="comfortable"
                            ></v-checkbox>
                        </v-col>
                        <v-col v-show="!budget.id" cols="12" md="12">
                            <v-checkbox
                                v-model="budget.includeProvisions"
                                :label="$t('budget.include-provision')"
                                density="comfortable"
                            ></v-checkbox>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="budget.processing" @click="createDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="budget.processing" type="submit" @click="save">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Dialog Clone -->
    <v-dialog v-model="cloneDialog" persistent width="800">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ titleModal }}</span>
            </v-card-title>
            <v-card-text>
                <v-form ref="formClone" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" md="4">
                            <v-text-field
                                ref="selectMonthYearClone"
                                v-model="cloneBudget.yearMonth"
                                type="month"
                                :label="$t('default.year-month')"
                                clearable
                                :rules="rules.textFieldRules"
                                :error-messages="cloneYearMonthErrors"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-checkbox
                                v-model="cloneBudget.includeProvisions"
                                :label="$t('budget.include-provision')"
                                density="comfortable"
                            ></v-checkbox>
                            <v-checkbox
                                v-model="cloneBudget.cloneBugdetExpenses"
                                :label="$t('budget.clone-expense')"
                                density="comfortable"
                            ></v-checkbox>
                            <v-checkbox
                                v-model="cloneBudget.cloneBugdetIncomes"
                                :label="$t('budget.clone-income')"
                                density="comfortable"
                            ></v-checkbox>
                            <v-checkbox
                                v-model="cloneBudget.cloneBugdetGoals"
                                :label="$t('budget.clone-goals')"
                                density="comfortable"
                            ></v-checkbox>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="cloneBudget.processing" @click="cloneDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="cloneBudget.processing" type="submit" @click="clone">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { currencyField, formatDate } from '@/utils/utils.js'
    import { Head, Link, router, useForm } from '@inertiajs/vue3'
    import moment from 'moment'
    import { computed, nextTick, ref } from 'vue'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'BudgetIndex', layout: AuthenticatedLayout })

    const props = defineProps({
        budgets: {
            type: Array,
        },
        year: {
            type: String,
        },
    })

    const { t } = useI18n()

    const {
        isLoading,
        editDialog: createDialog,
        titleModal,
        confirmRemove: crudConfirmRemove,
    } = useCrudOperations('/budget')

    const cloneDialog = ref(false)

    const breadcrumbs = computed(() => [
        {
            title: t('menus.dashboard'),
            disabled: false,
            href: '/dashboard',
        },
        {
            title: t('menus.budget'),
            disabled: true,
        },
    ])

    const headers = computed(() => [
        { title: t('default.year-month'), key: 'year_month' },
        { title: t('budget.total-expense'), key: 'total_expense', align: 'end' },
        { title: t('budget.total-income'), key: 'total_income', align: 'end' },
        { title: t('budget.start_week_1'), key: 'start_week_1', align: 'center' },
        { title: t('budget.end_week_1'), key: 'end_week_1', align: 'center' },
        { title: t('budget.start_week_2'), key: 'start_week_2', align: 'center' },
        { title: t('budget.end_week_2'), key: 'end_week_2', align: 'center' },
        { title: t('budget.start_week_3'), key: 'start_week_3', align: 'center' },
        { title: t('budget.end_week_3'), key: 'end_week_3', align: 'center' },
        { title: t('budget.start_week_4'), key: 'start_week_4', align: 'center' },
        { title: t('budget.end_week_4'), key: 'end_week_4', align: 'center' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ])

    const rules = {
        textFieldRules: [(v) => !!v || t('rules.required-text-field')],
    }

    const search = ref(null)

    function createBudgetFormData() {
        return {
            id: null,
            yearMonth: null,
            start_week_1: null,
            end_week_1: null,
            start_week_2: null,
            end_week_2: null,
            start_week_3: null,
            end_week_3: null,
            start_week_4: null,
            end_week_4: null,
            automaticGenerateYear: false,
            includeFixExpenses: false,
            includeProvisions: false,
        }
    }

    function createCloneBudgetFormData() {
        return {
            id: null,
            yearMonth: null,
            includeProvisions: false,
            cloneBugdetExpenses: false,
            cloneBugdetIncomes: false,
            cloneBugdetGoals: false,
        }
    }

    const budget = useForm(createBudgetFormData())
    const cloneBudget = useForm(createCloneBudgetFormData())

    const yearModel = computed(() => props.year)
    const budgetYearMonthErrors = computed(() => [budget.errors.year, budget.errors.month].filter(Boolean))
    const cloneYearMonthErrors = computed(() => [cloneBudget.errors.year, cloneBudget.errors.month].filter(Boolean))

    const formCreate = ref(null)
    const formClone = ref(null)
    const selectMonthYear = ref(null)
    const txtStartWeek1 = ref(null)
    const selectMonthYearClone = ref(null)
    const confirm = ref(null)

    function hrefBudgetShow(item) {
        return '/budget/show/' + item.id
    }

    function changeYear(value) {
        router.get('/budget/' + value.target.value)
    }

    function newItem() {
        titleModal.value = t('budget.new-item')
        createDialog.value = true
        Object.assign(budget, createBudgetFormData())
        budget.clearErrors()
        nextTick(() => {
            selectMonthYear.value?.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('budget.edit-item')
        createDialog.value = true
        Object.assign(budget, {
            id: item.id,
            yearMonth: item.year + '-' + item.month,
            start_week_1: moment(item.start_week_1, 'YYYY-MM-DD').toDate(),
            end_week_1: moment(item.end_week_1, 'YYYY-MM-DD').toDate(),
            start_week_2: moment(item.start_week_2, 'YYYY-MM-DD').toDate(),
            end_week_2: moment(item.end_week_2, 'YYYY-MM-DD').toDate(),
            start_week_3: moment(item.start_week_3, 'YYYY-MM-DD').toDate(),
            end_week_3: moment(item.end_week_3, 'YYYY-MM-DD').toDate(),
            start_week_4: moment(item.start_week_4, 'YYYY-MM-DD').toDate(),
            end_week_4: moment(item.end_week_4, 'YYYY-MM-DD').toDate(),
            automaticGenerateYear: false,
            includeFixExpenses: false,
            includeProvisions: false,
        })
        budget.clearErrors()
        nextTick(() => {
            txtStartWeek1.value?.focus()
        })
    }

    function splitYearMonth(value) {
        if (!value || !value.includes('-')) {
            return { year: null, month: null }
        }

        return {
            year: value.substring(0, 4),
            month: value.substring(5, 7),
        }
    }

    function normalizeBudgetDate(value) {
        if (value === null || value === undefined || value === '') {
            return null
        }

        const normalizedValue = moment(value)

        if (!normalizedValue.isValid()) {
            return null
        }

        return normalizedValue.format('YYYY-MM-DD')
    }

    function buildCreatePayload() {
        const { year, month } = splitYearMonth(budget.yearMonth)

        return {
            year,
            month,
            start_week_1: normalizeBudgetDate(budget.start_week_1),
            end_week_1: normalizeBudgetDate(budget.end_week_1),
            start_week_2: normalizeBudgetDate(budget.start_week_2),
            end_week_2: normalizeBudgetDate(budget.end_week_2),
            start_week_3: normalizeBudgetDate(budget.start_week_3),
            end_week_3: normalizeBudgetDate(budget.end_week_3),
            start_week_4: normalizeBudgetDate(budget.start_week_4),
            end_week_4: normalizeBudgetDate(budget.end_week_4),
            automaticGenerateYear: budget.automaticGenerateYear,
            includeFixExpenses: budget.includeFixExpenses,
            includeProvisions: budget.includeProvisions,
        }
    }

    function buildUpdatePayload() {
        return {
            start_week_1: normalizeBudgetDate(budget.start_week_1),
            end_week_1: normalizeBudgetDate(budget.end_week_1),
            start_week_2: normalizeBudgetDate(budget.start_week_2),
            end_week_2: normalizeBudgetDate(budget.end_week_2),
            start_week_3: normalizeBudgetDate(budget.start_week_3),
            end_week_3: normalizeBudgetDate(budget.end_week_3),
            start_week_4: normalizeBudgetDate(budget.start_week_4),
            end_week_4: normalizeBudgetDate(budget.end_week_4),
        }
    }

    function buildClonePayload() {
        const { year, month } = splitYearMonth(cloneBudget.yearMonth)

        return {
            year,
            month,
            includeProvisions: cloneBudget.includeProvisions,
            cloneBugdetExpenses: cloneBudget.cloneBugdetExpenses,
            cloneBugdetIncomes: cloneBudget.cloneBugdetIncomes,
            cloneBugdetGoals: cloneBudget.cloneBugdetGoals,
        }
    }

    async function save() {
        let validate = await formCreate.value.validate()
        if (validate.valid) {
            if (budget.id) {
                await update()
            } else {
                await _create()
            }
        }
    }

    async function _create() {
        budget
            .transform(() => buildCreatePayload())
            .post('/budget', {
                preserveScroll: true,
                onSuccess: () => {
                    createDialog.value = false
                    Object.assign(budget, createBudgetFormData())
                    budget.clearErrors()
                },
            })
    }

    async function update() {
        budget
            .transform(() => buildUpdatePayload())
            .put('/budget/' + budget.id, {
                preserveScroll: true,
                onSuccess: () => {
                    createDialog.value = false
                },
            })
    }

    function cloneItem(item) {
        titleModal.value = t('budget.clone-item')
        cloneDialog.value = true
        Object.assign(cloneBudget, {
            id: item.id,
            yearMonth: null,
            includeProvisions: false,
            cloneBugdetExpenses: false,
            cloneBugdetIncomes: false,
            cloneBugdetGoals: false,
        })
        cloneBudget.clearErrors()
        nextTick(() => {
            selectMonthYearClone.value?.focus()
        })
    }

    async function clone() {
        let validate = await formClone.value.validate()
        if (validate.valid) {
            cloneBudget
                .transform(() => buildClonePayload())
                .put('/budget/clone/' + cloneBudget.id, {
                    preserveScroll: true,
                    onSuccess: () => {
                        cloneDialog.value = false
                        Object.assign(cloneBudget, createCloneBudgetFormData())
                        cloneBudget.clearErrors()
                    },
                })
        }
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('budget.budget'), t('default.confirm-delete-item'))
    }
</script>
