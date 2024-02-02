<template>
    <Head title="Budget" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('budget.title') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>

        <v-card class="pa-4">
            <v-row dense>
                <v-col cols="12" sm="12" md="2">
                    <v-text-field
                        ref="inputYear"
                        v-model="year"
                        :label="$t('default.year')"
                        type="number"
                        min="1970"
                        max="1970"
                        step="1"
                        :rules="rules.textFieldRules"
                        required
                        density="comfortable"
                        @change="changeYear"
                    ></v-text-field>
                </v-col>
                <v-col md="10">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
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
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('default.show')" location="top">
                                <template #activator="{ props }">
                                    <Link :href="hrefBudgetShow(item)" class="v-breadcrumbs-item--link">
                                        <v-icon v-bind="props" color="warning" icon="mdi-eye" size="small" class="me-2">
                                        </v-icon>
                                    </Link>
                                </template>
                            </v-tooltip>
                            <v-tooltip :text="$t('budget.clone')" location="top">
                                <template #activator="{ props }">
                                    <v-icon
                                        v-bind="props"
                                        color="warning"
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
                                        @click="openDelete(item)"
                                    ></v-icon>
                                </template>
                            </v-tooltip>
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
                                    v-model="createBudget.yearMonth"
                                    type="month"
                                    :label="$t('budget.year-month')"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="createBudget.automaticGenerate"
                                    :label="$t('budget.automatic-generate')"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="createBudget.includeFixExpense"
                                    :label="$t('budget.include-fix-expense')"
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="createBudget.includeProvision"
                                    :label="$t('budget.include-provision')"
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
                    <v-btn color="primary" flat :loading="isLoading" type="submit" @click="create">
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
                                    v-model="createBudget.yearMonth"
                                    type="month"
                                    :label="$t('budget.year-month')"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="createBudget.automaticGenerate"
                                    :label="$t('budget.automatic-generate')"
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

        <!-- Dialog delete -->
        <v-row justify="center">
            <v-dialog v-model="removeDialog" persistent width="auto">
                <v-card>
                    <v-card-text>{{ $t('default.confirm-delete-item') }}</v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn color="error" elevated :loading="isLoading" @click="removeDialog = false">
                            {{ $t('default.cancel') }}</v-btn
                        >
                        <v-btn color="primary" elevated :loading="isLoading" text @click="remove()">
                            {{ $t('default.delete') }}</v-btn
                        >
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
</script>

<script>
export default {
    name: 'BudgetIndex',
    props: {
        budgets: {
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
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.name'), key: 'name', groupable: false },
                { title: this.$t('budget.digits'), key: 'digits' },
                { title: this.$t('budget.due-date'), key: 'due_date' },
                { title: this.$t('budget.closing-date'), key: 'closing_date' },
                { title: this.$t('default.active'), key: 'is_active' },
                { title: this.$t('default.action'), align: 'end', key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
            },
            search: null,
            createDialog: false,
            cloneDialog: false,
            removeDialog: false,
            isLoading: false,
            deleteId: null,
            createBudget: {
                yearMonth: null,
                automaticGenerateYear: false,
                includeFixExpense: false,
                includeProvision: false,
            },
            cloneBudget: {
                id: null,
                yearMonth: null,
                includeProvision: false,
                cloneBugdetExpense: false,
                cloneBugdetIncome: false,
                cloneBugdetGoals: false,
            },
        }
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefBudgetShow(item) {
            return '/budget/' + item.id
        },

        newItem() {
            this.titleModal = this.$t('budget.new-item')
            this.editDialog = true
            this.createBudget = {
                yearMonth: null,
                automaticGenerateYear: false,
                includeFixExpense: false,
                includeProvision: false,
            }
            setTimeout(() => {
                this.$refs.selectMonthYear.focus()
            })
        },

        async create() {
            let validate = await this.$refs.formCreate.validate()
            if (validate.valid) {
                this.isLoading = true
                this.$inertia.post(
                    '/budget',
                    {
                        year: this.createBudget.yearMonth.substring(0, 4),
                        month: this.createBudget.yearMonth.substring(5, 7),
                        automaticGenerateYear: this.createBudget.automaticGenerateYear,
                        includeFixExpense: this.createBudget.includeFixExpense,
                        includeProvision: this.createBudget.includeProvision,
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
            }
        },

        cloneItem(item) {
            this.titleModal = this.$t('budget.clone-item')
            this.cloneDialog = true
            this.cloneBudget = {
                id: item.id,
                yearMonth: null,
                includeProvision: false,
                cloneBugdetExpense: false,
                cloneBugdetIncome: false,
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
                        year: this.createBudget.yearMonth.substring(0, 4),
                        month: this.createBudget.yearMonth.substring(5, 7),
                        includeProvision: this.createBudget.includeProvision,
                        cloneBugdetExpense: this.createBudget.cloneBugdetExpense,
                        cloneBugdetIncome: this.createBudget.cloneBugdetIncome,
                        cloneBugdetGoals: this.createBudget.cloneBugdetGoals,
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

        openDelete(item) {
            this.deleteId = item.id
            this.removeDialog = true
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/budget/${this.deleteId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.removeDialog = false
                    this.removeDialog = false
                },
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
