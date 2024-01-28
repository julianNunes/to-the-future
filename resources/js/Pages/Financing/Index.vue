<template>
    <Head title="Financing" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('financing.title') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-card class="pa-4">
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="financings"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="financings.length"
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
                        <template #[`item.start_date`]="{ item }">{{
                            moment(item.start_date).format('DD/MM/YYYY')
                        }}</template>
                        <template #[`item.total`]="{ item }">{{ currencyField(item.total) }}</template>
                        <template #[`item.fees_monthly`]="{ item }">{{ percentField(item.fees_monthly) }}</template>
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('financing.installments-show')" location="top">
                                <template #activator="{ props }">
                                    <Link :href="hrefInstalmment(item)" class="v-breadcrumbs-item--link">
                                        <v-icon v-bind="props" color="warning" icon="mdi-checkbook" size="small">
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
                                        @click="editItem(item)"
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
                            <v-col cols="12" sm="12" md="12">
                                <v-text-field
                                    ref="txtDescription"
                                    v-model="financing.description"
                                    :label="$t('default.description')"
                                    :rules="rules.textFieldRules"
                                    required
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.start_date"
                                    :label="$t('financing.start-date')"
                                    type="date"
                                    required
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.total"
                                    type="number"
                                    :label="$t('default.total')"
                                    min="0"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.fees_monthly"
                                    type="number"
                                    :label="$t('financing.fees-monthly')"
                                    min="0"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="!financing.id" cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.portion_total"
                                    type="number"
                                    :label="$t('financing.portion-total')"
                                    min="2"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="!financing.id" cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.start_date_installment"
                                    :label="$t('financing.installment-first-date')"
                                    type="date"
                                    required
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="!financing.id" cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="financing.value_installment"
                                    type="number"
                                    :label="$t('financing.installment-value')"
                                    min="0"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-textarea
                                    v-model="financing.remarks"
                                    :label="$t('default.remarks')"
                                    density="comfortable"
                                ></v-textarea>
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
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import moment from 'moment'
</script>

<script>
import { currencyField, percentField } from '../../utils/utils.js'

export default {
    name: 'FinancingIndex',
    props: {
        financings: {
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
                    title: this.$t('menus.financing'),
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description' },
                { title: this.$t('financing.start-date'), align: 'center', key: 'start_date' },
                { title: this.$t('financing.fees-monthly'), align: 'end', key: 'fees_monthly' },
                { title: this.$t('financing.portion-total'), align: 'end', key: 'portion_total' },
                { title: this.$t('default.total'), align: 'end', key: 'total' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.action'), align: 'end', key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                currencyFieldRules: [
                    (value) => {
                        if (!value) return this.$t('rules.required-text-field')
                        if (Number(value) <= 0) return this.$t('rules.required-currency-field')
                        return true
                    },
                ],
            },
            search: null,
            editDialog: false,
            removeDialog: false,
            isLoading: false,
            deleteId: null,
            financing: {
                id: null,
                description: null,
                start_date: null,
                total: 0,
                fees_monthly: 0,
                portion_total: 0,
                remarks: null,
                start_date_installment: null,
                value_installment: 0,
            },
        }
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefInstalmment(item) {
            return '/financing/' + item.id + '/installment'
        },

        newItem() {
            this.titleModal = this.$t('financing.new-item')
            this.editDialog = true
            this.financing = {
                id: null,
                description: null,
                start_date: null,
                total: 0,
                fees_monthly: 0,
                portion_total: 0,
                remarks: null,
                start_date_installment: null,
                value_installment: 0,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('financing.edit-item')
            this.editDialog = true
            this.financing = {
                id: item.id,
                description: item.description,
                start_date: item.start_date,
                total: Number(item.total),
                fees_monthly: item.fees_monthly ? Number(item.fees_monthly) : 0,
                portion_total: Number(item.portion_total),
                remarks: item.remarks,
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
                if (this.financing.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/financing',
                {
                    description: this.financing.description,
                    start_date: this.financing.start_date,
                    total: this.financing.total,
                    fees_monthly: this.financing.fees_monthly,
                    portion_total: this.financing.portion_total,
                    remarks: this.financing.remarks,
                    start_date_installment: this.financing.start_date_installment,
                    value_installment: this.financing.value_installment,
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
                '/financing/' + this.financing.id,
                {
                    description: this.financing.description,
                    start_date: this.financing.start_date,
                    total: this.financing.total,
                    fees_monthly: this.financing.fees_monthly,
                    remarks: this.financing.remarks,
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
            this.removeDialog = true
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/financing/${this.deleteId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.removeDialog = false
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
