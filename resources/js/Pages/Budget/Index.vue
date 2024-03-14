<template>
    <Head title="Budget" />
    <AuthenticatedLayout>
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
                            <template #[`item.total_expense`]="{ item }">{{
                                currencyField(item.total_expense)
                            }}</template>
                            <template #[`item.total_income`]="{ item }">{{
                                currencyField(item.total_income)
                            }}</template>
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
                                        <template #activator="{ props }">
                                            <Link :href="hrefBudgetShow(item)" class="v-breadcrumbs-item--link">
                                                <v-icon v-bind="props" color="light-blue" icon="mdi-eye" size="small">
                                                </v-icon>
                                            </Link>
                                        </template>
                                    </v-tooltip>
                                    <v-tooltip :text="$t('default.edit')" location="top">
                                        <template #activator="{ props }">
                                            <v-icon
                                                v-bind="props"
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
                                        <template #activator="{ props }">
                                            <v-icon
                                                v-bind="props"
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
                                        <template #activator="{ props }">
                                            <v-icon
                                                v-bind="props"
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
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col offset="8" />
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    ref="txtStartWeek1"
                                    v-model="budget.start_week_1"
                                    type="date"
                                    :label="$t('budget.start_week_1')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.end_week_1) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                    @change="budget.end_week_1 = budget.start_week_1"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.end_week_1"
                                    type="date"
                                    :label="$t('budget.end_week_1')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.start_week_1) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.start_week_2"
                                    type="date"
                                    :label="$t('budget.start_week_2')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.end_week_2) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                    @change="budget.end_week_2 = budget.start_week_2"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.end_week_2"
                                    type="date"
                                    :label="$t('budget.end_week_2')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.start_week_2) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.start_week_3"
                                    type="date"
                                    :label="$t('budget.start_week_3')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.end_week_3) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                    @change="budget.end_week_3 = budget.start_week_3"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.end_week_3"
                                    type="date"
                                    :label="$t('budget.end_week_3')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.start_week_3) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.start_week_4"
                                    type="date"
                                    :label="$t('budget.start_week_4')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.end_week_4) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                    @change="budget.end_week_4 = budget.start_week_4"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="budget.end_week_4"
                                    type="date"
                                    :label="$t('budget.end_week_4')"
                                    required
                                    :rules="[
                                        (value) => {
                                            if (budget.start_week_4) {
                                                if (!value) return $t('rules.required-text-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                ></v-text-field>
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
                    <v-btn color="error" flat :loading="isLoading" @click="createDialog = false">
                        {{ $t('default.cancel') }}
                    </v-btn>
                    <v-btn color="primary" flat :loading="isLoading" type="submit" @click="save">
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
                                    :label="$t('budget.year-month')"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="cloneBudget.includeProvisions"
                                    :label="$t('budget.include-provision')"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="cloneBudget.cloneBugdetExpenses"
                                    :label="$t('budget.clone-expense')"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="cloneBudget.cloneBugdetIncomes"
                                    :label="$t('budget.clone-income')"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="cloneBudget.cloneBugdetGoals"
                                    :label="$t('budget.clone-goals')"
                                ></v-checkbox>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" flat :loading="isLoading" @click="cloneDialog = false">
                        {{ $t('default.cancel') }}
                    </v-btn>
                    <v-btn color="primary" flat :loading="isLoading" type="submit" @click="save">
                        {{ $t('default.save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { currencyField } from '../../utils/utils.js'
import moment from 'moment'
</script>

<script>
export default {
    name: 'BudgetIndex',
    props: {
        budgets: {
            type: Array,
        },
        year: {
            type: String,
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
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.year-month'), key: 'year_month' },
                { title: this.$t('budget.total-expense'), key: 'total_expense', align: 'end' },
                { title: this.$t('budget.total-income'), key: 'total_income', align: 'end' },
                // { title: this.$t('default.closed'), key: 'closed', align: 'center' },
                { title: this.$t('budget.start_week_1'), key: 'start_week_1', align: 'center' },
                { title: this.$t('budget.end_week_1'), key: 'end_week_1', align: 'center' },
                { title: this.$t('budget.start_week_2'), key: 'start_week_2', align: 'center' },
                { title: this.$t('budget.end_week_2'), key: 'end_week_2', align: 'center' },
                { title: this.$t('budget.start_week_3'), key: 'start_week_3', align: 'center' },
                { title: this.$t('budget.end_week_3'), key: 'end_week_3', align: 'center' },
                { title: this.$t('budget.start_week_4'), key: 'start_week_4', align: 'center' },
                { title: this.$t('budget.end_week_4'), key: 'end_week_4', align: 'center' },
                { title: this.$t('default.action'), align: 'center', key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
            },
            search: null,
            createDialog: false,
            cloneDialog: false,
            isLoading: false,
            deleteId: null,
            budget: {
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
            },
            cloneBudget: {
                id: null,
                yearMonth: null,
                includeProvisions: false,
                cloneBugdetExpenses: false,
                cloneBugdetIncomes: false,
                cloneBugdetGoals: false,
            },
        }
    },

    computed: {
        yearModel() {
            return this.year
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefBudgetShow(item) {
            return '/budget/show/' + item.id
        },

        changeYear(value) {
            this.$inertia.get('/budget/' + value.target.value)
        },

        newItem() {
            this.titleModal = this.$t('budget.new-item')
            this.createDialog = true
            this.budget = {
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
            setTimeout(() => {
                this.$refs.selectMonthYear.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('budget.edit-item')
            this.createDialog = true
            this.budget = {
                id: item.id,
                yearMonth: item.year + '-' + item.month,
                start_week_1: item.start_week_1,
                end_week_1: item.end_week_1,
                start_week_2: item.start_week_2,
                end_week_2: item.end_week_2,
                start_week_3: item.start_week_3,
                end_week_3: item.end_week_3,
                start_week_4: item.start_week_4,
                end_week_4: item.end_week_4,
            }
            setTimeout(() => {
                this.$refs.txtStartWeek1.focus()
            })
        },

        async save() {
            let validate = await this.$refs.formCreate.validate()
            if (validate.valid) {
                if (this.budget.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/budget',
                {
                    year: this.budget.yearMonth.substring(0, 4),
                    month: this.budget.yearMonth.substring(5, 7),
                    start_week_1: this.budget.start_week_1,
                    end_week_1: this.budget.end_week_1,
                    start_week_2: this.budget.start_week_2,
                    end_week_2: this.budget.end_week_2,
                    start_week_3: this.budget.start_week_3,
                    end_week_3: this.budget.end_week_3,
                    start_week_4: this.budget.start_week_4,
                    end_week_4: this.budget.end_week_4,
                    automaticGenerateYear: this.budget.automaticGenerateYear,
                    includeFixExpenses: this.budget.includeFixExpenses,
                    includeProvisions: this.budget.includeProvisions,
                },
                {
                    onSuccess: () => {
                        this.createDialog = false
                    },
                    onFinish: () => {
                        this.isLoading = false
                    },
                }
            )
        },

        async update() {
            this.isLoading = true
            this.$inertia.put(
                '/budget/' + this.budget.id,
                {
                    start_week_1: this.budget.start_week_1,
                    end_week_1: this.budget.end_week_1,
                    start_week_2: this.budget.start_week_2,
                    end_week_2: this.budget.end_week_2,
                    start_week_3: this.budget.start_week_3,
                    end_week_3: this.budget.end_week_3,
                    start_week_4: this.budget.start_week_4,
                    end_week_4: this.budget.end_week_4,
                },
                {
                    onSuccess: () => {
                        this.createDialog = false
                    },
                    onFinish: () => {
                        this.isLoading = false
                    },
                }
            )
        },

        cloneItem(item) {
            this.titleModal = this.$t('budget.clone-item')
            this.cloneDialog = true
            this.cloneBudget = {
                id: item.id,
                yearMonth: null,
                includeProvisions: false,
                cloneBugdetExpenses: false,
                cloneBugdetIncomes: false,
                cloneBugdetGoals: false,
            }
            setTimeout(() => {
                this.$refs.selectMonthYearClone.focus()
            })
        },

        async clone() {
            let validate = await this.$refs.formClone.validate()
            if (validate.valid) {
                this.isLoading = true
                this.$inertia.put(
                    '/budget/' + this.budget.id + '/clone',
                    {
                        year: this.cloneBudget.yearMonth.substring(0, 4),
                        month: this.cloneBudget.yearMonth.substring(5, 7),
                        includeProvisions: this.cloneBudget.includeProvisions,
                        cloneBugdetExpenses: this.cloneBudget.cloneBugdetExpenses,
                        cloneBugdetIncomes: this.cloneBudget.cloneBugdetIncomes,
                        cloneBugdetGoals: this.cloneBudget.cloneBugdetGoals,
                    },
                    {
                        onSuccess: () => {
                            this.cloneDialog = false
                        },
                        onFinish: () => {
                            this.isLoading = false
                        },
                    }
                )
            }
        },

        async confirmRemove(item) {
            this.deleteId = item.id
            if (await this.$refs.confirm.open(this.$t('budget.budget'), this.$t('default.confirm-delete-item'))) {
                this.remove()
            }
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/budget/${this.deleteId}`, {
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
