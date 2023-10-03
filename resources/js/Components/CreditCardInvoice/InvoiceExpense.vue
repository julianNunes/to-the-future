<template>
    <v-card class="">
        <v-card-title class="bg-primary">
            <span class="text-h6">{{ $t('credit-card-invoice.data-invoice') }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-row dense>
                <v-col cols="12" sm="12" md="4">
                    <v-text-field
                        ref="txtName"
                        v-model="invoice.credit_card.name"
                        :label="$t('default.credit-card')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoice.due_date"
                        :label="$t('credit-card.due-date')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoice.closing_date"
                        :label="$t('credit-card.closing-date')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        :text="currencyField(invoice.total)"
                        :label="$t('default.total')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        :text="currencyField(invoice.total_paid)"
                        :label="$t('default.total-paid')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="isClosed"
                        :label="$t('default.closed')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
            </v-row>
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    <v-btn color="info" class="ml-2" @click="downloadTemplate">{{
                        $t('credit-card-invoice.download-template')
                    }}</v-btn>
                    <v-btn color="info" class="ml-2" @click="importExcel">{{
                        $t('credit-card-invoice.import-excel')
                    }}</v-btn>
                </v-col>
            </v-row>
            <v-row dense>
                <v-col md="12">
                    <v-data-table
                        :group-by="[{ key: 'week', order: 'asc' }]"
                        :headers="headers"
                        :items="invoice.expenses"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="invoice.expenses"
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
                        <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                        <template #[`item.share_value`]="{ item }">{{ currencyField(item.share_value) }}</template>
                        <template #[`item.date`]="{ item }">{{ moment(item.date).format('DD/MM/YYYY') }}</template>
                        <template #[`item.week`]="{ item }">{{ convertWeek(item.week) }}</template>
                        <template #[`item.potion`]="{ item }">{{
                            item.portion ? item.portion + '/' + item.portion_total : ''
                        }}</template>
                        <template #[`item.share_user_id`]="{ item }">{{
                            item.share_user ? item.share_user.name : ''
                        }}</template>
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('default.edit')" location="top">
                                <template #activator="{ props }">
                                    <v-icon
                                        v-bind="props"
                                        color="warning"
                                        icon="mdi-pencil"
                                        size="small"
                                        class="me-2"
                                        @click="editItem(item)"
                                    >
                                    </v-icon>
                                </template>
                            </v-tooltip>
                            <v-tooltip :text="$t('default.delete')" location="top">
                                <template #activator="{ props }">
                                    <v-icon
                                        v-bind="props"
                                        class="ml-2"
                                        color="error"
                                        icon="mdi-delete"
                                        size="small"
                                        @click="openDelete(item)"
                                    >
                                    </v-icon>
                                </template>
                            </v-tooltip>
                        </template>

                        <template #group-header="{ item, toggleGroup, isGroupOpen }">
                            <tr>
                                <th class="title" width="150px">
                                    <VBtn
                                        size="small"
                                        variant="text"
                                        :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                        @click="toggleGroup(item)"
                                    ></VBtn>
                                    {{ convertWeek(item.value) }}
                                </th>
                                <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">
                                    {{ sumGroup(invoice.expenses, item.key, item.value, 'value') }}
                                </th>
                                <th class="title text-right">
                                    {{ sumGroup(invoice.expenses, item.key, item.value, 'share_value') }}
                                </th>
                                <th :colspan="6"></th>
                            </tr>
                        </template>

                        <template v-if="invoice.expenses.length" #tfoot>
                            <tr class="green--text">
                                <th class="title"></th>
                                <th colspan="2" class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumField(invoice.expenses, 'value') }}</th>
                                <th class="title text-right">{{ sumField(invoice.expenses, 'share_value') }}</th>
                            </tr>
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

    <!-- Dialog Criacao/Edicao -->
    <v-dialog v-model="editDialog" persistent width="800">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ titleModal }}</span>
            </v-card-title>
            <v-card-text>
                <v-form ref="form" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescription"
                                v-model="expense.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-text-field
                                v-model="expense.date"
                                type="date"
                                :label="$t('default.date')"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-text-field
                                v-model="expense.portion"
                                type="number"
                                :label="$t('default.portion')"
                                min="0"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-text-field
                                v-model="expense.portion_total"
                                type="number"
                                :label="$t('default.portion-total')"
                                min="0"
                                required
                                :rules="[
                                    (value) => {
                                        if (expense.portion) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-select
                                v-model="expense.week"
                                :label="$t('default.week')"
                                :items="weekList"
                                item-title="name"
                                item-value="value"
                                clearable
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-text-field
                                v-model="expense.share_value"
                                :label="$t('default.share-value')"
                                type="number"
                                min="0"
                                :rules="[
                                    (value) => {
                                        if (expense.share_user_id) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="8">
                            <v-select
                                v-model="expense.share_user_id"
                                :label="$t('default.share-user')"
                                :items="shareUsers"
                                item-title="share_user_name"
                                item-value="share_user_id"
                                clearable
                                :rules="[
                                    (value) => {
                                        if (expense.share_value && parseFloat(expense.share_value) > 0) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-text-field
                                v-model="expense.remarks"
                                :label="$t('default.remarks')"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="isLoading" @click="editDialog = false">
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
        <v-dialog v-model="deleteDialog" persistent width="auto">
            <v-card>
                <v-card-text>{{ $t('default.confirm-delete-item') }}</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn color="error" elevated :loading="isLoading" @click="deleteDialog = false">
                        {{ $t('default.cancel') }}</v-btn
                    >
                    <v-btn color="primary" elevated :loading="isLoading" text @click="this.delete()">
                        {{ $t('default.delete') }}</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script setup>
// import { Link } from '@inertiajs/vue3'
</script>

<script>
import moment from 'moment'
import { sumField, sumGroup, currencyField } from '../../utils/utils.js'

export default {
    name: 'InvoiceExpenses',
    props: {
        creditCardInvoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
    },

    setup(props) {
        return props
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.date'), key: 'date' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.portion'), key: 'portion' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.action'), key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                booleanFieldRules: [(v) => v !== null || this.$t('rules.required-text-field')],
                digitsFieldRules: [
                    (value) => {
                        if (!value) return this.$t('rules.required-text-field')
                        if (!/^\d+$/.test(value)) return this.$t('rules.only-numbers')

                        return true
                    },
                ],
            },
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            invoice: this.creditCardInvoice,
            expense: {
                description: null,
                date: null,
                value: null,
                portion: null,
                portion_total: null,
                remarks: null,
                share_value: null,
                share_user_id: null,
                invoice_id: null,
            },
            weekList: [
                {
                    name: this.$t('default.week-1'),
                    value: 'WEEK_1',
                },
                {
                    name: this.$t('default.week-2'),
                    value: 'WEEK_2',
                },
                {
                    name: this.$t('default.week-3'),
                    value: 'WEEK_3',
                },
                {
                    name: this.$t('default.week-4'),
                    value: 'WEEK_4',
                },
            ],
        }
    },

    computed: {
        isClosed() {
            return this.invoice.closed ? this.$t('default.yes') : this.$t('default.no')
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        convertWeek(group) {
            return this.weekList.find((x) => x.value === group).name
        },
        newItem() {
            this.titleModal = this.$t('credit-card-invoice-expense.new-item')
            this.editDialog = true
            this.expense = {
                description: null,
                date: null,
                value: null,
                portion: null,
                portion_total: null,
                remarks: null,
                share_value: null,
                invoice_id: null,
                share_user_id: null,
            }
            setTimeout(() => {
                this.$refs.txtName.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('credit-card-invoice-expense.edit-item')
            this.editDialog = true
            this.expense = {
                description: item.description,
                date: item.date,
                value: item.value,
                portion: item.portion,
                portion_total: item.portion_total,
                remarks: item.remarks,
                share_value: item.share_value,
                invoice_id: item.invoice_id,
                share_user_id: item.share_user_id,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        closeItem() {
            this.editDialog = false
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.creditCard.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/credit-card/' + this.invoice.credit_card.id + '/invoice/' + this.invoice.id + '/expense',
                {
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    invoice_id: this.invoice.id,
                },
                {
                    onSuccess: () => {
                        this.editDialog = false
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
                '/credit-card/' +
                    this.invoice.credit_card.id +
                    '/invoice/' +
                    this.invoice.id +
                    '/expense/' +
                    this.expense.id,
                {
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    invoice_id: this.invoice.id,
                },
                {
                    onSuccess: () => {
                        this.editDialog = false
                    },
                    onFinish: () => {
                        this.isLoading = false
                    },
                }
            )
        },

        openDelete(item) {
            this.deleteId = item.id
            this.deleteDialog = true
        },

        delete() {
            this.isLoading = true
            this.$inertia.delete(`/credit-card/${this.deleteId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.deleteDialog = false
                    this.editDialog = false
                },
                onError: () => {
                    this.isLoading = false
                },
                onFinish: () => {
                    this.isLoading = false
                },
            })
        },

        downloadTemplate() {},

        importExcel() {},
    },
}
</script>
