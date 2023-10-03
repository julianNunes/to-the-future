<template>
    <Head title="Credit Card" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('credit-card-invoice.title-index') }}</h5>
        </div>
        <!-- Dados do cartão de credito -->
        <v-card class="mb-4">
            <v-card-title class="bg-primary">
                <span class="text-h6">{{ $t('credit-card-invoice.credit-card-title') }}</span>
            </v-card-title>
            <v-card-text class="pt-4">
                <v-row dense>
                    <v-col cols="12" sm="12" md="4">
                        <v-text-field
                            ref="txtName"
                            v-model="credit_card.name"
                            :label="$t('default.name')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="credit_card.digits"
                            :label="$t('credit-card.4-digits')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="credit_card.due_date"
                            :label="$t('credit-card.due-date')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="credit_card.closing_date"
                            :label="$t('credit-card.closing-date')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="isActive"
                            :label="$t('default.active')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card class="mt-4">
            <v-card-title class="bg-primary">
                <span class="text-h6">{{ $t('credit-card-invoice.title-invoices') }}</span>
            </v-card-title>
            <v-row dense class="pa-4">
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="invoices"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="invoices.length"
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
                        <template #[`item.closing_date`]="{ item }">{{
                            moment(item.closing_date).format('DD/MM/YYYY')
                        }}</template>
                        <template #[`item.due_date`]="{ item }">{{
                            moment(item.due_date).format('DD/MM/YYYY')
                        }}</template>
                        <template #[`item.total`]="{ item }">{{ currencyField(item.total) }}</template>
                        <template #[`item.total_paid`]="{ item }">{{ currencyField(item.total_paid) }}</template>
                        <template #[`item.closed`]="{ item }">{{
                            item.closed ? $t('default.yes') : $t('default.no')
                        }}</template>

                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('default.show')" location="top">
                                <template #activator="{ props }">
                                    <Link :href="hrefInvoiceShow(item)" class="v-breadcrumbs-item--link">
                                        <v-icon v-bind="props" color="warning" icon="mdi-eye" size="small" class="me-2">
                                        </v-icon>
                                    </Link>
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

        <!-- Dialog Criacao/Edicao -->
        <v-dialog v-model="editDialog" persistent width="800">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ titleModal }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" @submit.prevent>
                        <v-row dense>
                            <v-col cols="12" md="4">
                                <v-text-field
                                    ref="selectMonthYear"
                                    v-model="yearMonth"
                                    type="month"
                                    :label="$t('default.month')"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="automaticGenerate"
                                    :label="$t('credit-card-invoice.automatic-generate')"
                                ></v-checkbox>
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
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<script>
import moment from 'moment'
import { currencyField, MONTHS } from '../../utils/utils.js'

export default {
    name: 'ProvisionIndex',
    props: {
        creditCard: {
            type: Object,
        },
        invoices: {
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
            credit_card: this.creditCard,
            invoice: {
                id: null,
                due_date: null,
                closing_date: null,
                year_month: null,
                year: null,
                month: null,
                total_paid: null,
                closed: null,
                remarks: null,
                credit_card_id: null,
            },
            listMonths: MONTHS,
        }
    },

    computed: {
        isActive() {
            return this.credit_card.is_active ? this.$t('default.yes') : this.$t('default.no')
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefInvoiceShow(item) {
            return '/credit-card/' + item.credit_card_id + '/invoice/' + item.id
        },

        newItem() {
            this.titleModal = this.$t('credit-card-invoice.new-item')
            this.editDialog = true
            this.yearMonth = null
            this.automaticGenerate = false
            setTimeout(() => {
                this.$refs.selectMonthYear.focus()
            })
        },

        closeItem() {
            this.editDialog = false
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                this.isLoading = true
                this.$inertia.post(
                    '/credit-card/' + this.credit_card.id + '/invoice',
                    {
                        due_date: this.yearMonth + '-' + this.credit_card.due_date,
                        closing_date: this.yearMonth + '-' + this.credit_card.closing_date,
                        year: this.yearMonth.substring(0, 4),
                        month: this.yearMonth.substring(5, 7),
                        automatic_generate: this.automaticGenerate,
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
            }
        },

        openDelete(item) {
            this.deleteId = item.id
            this.deleteDialog = true
        },

        delete() {
            this.isLoading = true
            this.$inertia.delete(`/credit-card/${this.credit_card.id}/invoice/${this.deleteId}`, {
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
    },
}
</script>
