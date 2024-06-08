<template>
    <Head title="Credit Card" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('prepaid-card-extract.title-index') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>

        <!-- Dados do cartão de credito -->
        <v-card>
            <v-card-title class="bg-primary">
                <span class="text-h6">{{ $t('prepaid-card-extract.prepaid-card-title') }}</span>
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row dense>
                    <v-col cols="12" sm="12" md="4">
                        <v-text-field
                            ref="txtName"
                            v-model="prepaid_card.name"
                            :label="$t('default.name')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="prepaid_card.digits"
                            :label="$t('prepaid-card.4-digits')"
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
                <span class="text-h6">{{ $t('prepaid-card-extract.title-extracts') }}</span>
            </v-card-title>
            <v-card-text class="pa-4">
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    </v-col>
                    <v-col md="12">
                        <v-data-table
                            :headers="headers"
                            :items="extracts"
                            :sort-by="[{ key: 'due_date', order: 'asc' }]"
                            :search="search"
                            :loading="isLoading"
                            :loading-text="$t('default.loading-text-table')"
                            class="elevation-3"
                            density="compact"
                            :total-items="extracts.length"
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
                            <template #[`item.credit_date`]="{ item }">{{
                                moment(item.credit_date).format('DD/MM/YYYY')
                            }}</template>
                            <template #[`item.credit`]="{ item }">{{ currencyField(item.credit) }}</template>
                            <template #[`item.action`]="{ item }">
                                <v-tooltip :text="$t('default.show')" location="top">
                                    <template #activator="{ props }">
                                        <Link :href="hrefExtractShow(item)" class="v-breadcrumbs-item--link">
                                            <v-icon v-bind="props" color="warning" icon="mdi-eye" size="small">
                                            </v-icon>
                                        </Link>
                                    </template>
                                </v-tooltip>
                                <v-tooltip :text="$t('default.edit')" location="top">
                                    <template #activator="{ props }">
                                        <v-icon
                                            v-bind="props"
                                            class="ml-1"
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
                                            @click="confirmRemove(item)"
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
                            <v-col cols="12" md="4">
                                <v-text-field
                                    ref="selectMonthYear"
                                    v-model="extract.year_month"
                                    type="month"
                                    :label="$t('prepaid-card-extract.year-month')"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                    :disabled="extract.id ? true : false"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-text-field
                                    v-model="extract.credit"
                                    type="number"
                                    :label="$t('prepaid-card-extract.credit')"
                                    min="0"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-date-input
                                    v-model="extract.credit_date"
                                    :label="$t('financing.start-date')"
                                    prepend-icon=""
                                    prepend-inner-icon="$calendar"
                                    required
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                    :show-adjacent-months="true"
                                    :show-week="true"
                                ></v-date-input>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-textarea
                                    v-model="extract.remarks"
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

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import moment from 'moment'
import { currencyField } from '../../utils/utils.js'
</script>

<script>
export default {
    name: 'PrepaidCardExtractIndex',
    props: {
        prepaidCard: {
            type: Object,
        },
        extracts: {
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
                    title: this.$t('menus.prepaid-card'),
                    disabled: false,
                    href: '/prepaid-card',
                },
                {
                    title: this.$t('prepaid-card-extract.title-index'),
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.year-month'), key: 'year_month' },
                { title: this.$t('prepaid-card-extract.credit-date'), key: 'credit_date' },
                { title: this.$t('prepaid-card-extract.credit'), key: 'credit' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.action'), align: 'center', key: 'action', sortable: false },
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
            modalYear: false,
            automaticGenerate: false,
            year_month: null,
            search: null,
            editDialog: false,
            isLoading: false,
            deleteId: null,
            prepaid_card: this.prepaidCard,
            extract: {
                id: null,
                year_month: null,
                credit: null,
                credit_date: null,
                remarks: null,
                prepaid_card_id: null,
            },
        }
    },

    computed: {
        isActive() {
            return this.prepaid_card.is_active ? this.$t('default.yes') : this.$t('default.no')
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefExtractShow(item) {
            return '/prepaid-card/extract/' + item.id
        },

        newItem() {
            this.titleModal = this.$t('prepaid-card-extract.new-item')
            this.editDialog = true
            this.extract = {
                id: null,
                year_month: null,
                credit: null,
                credit_date: null,
                remarks: null,
                prepaid_card_id: this.prepaidCard.id,
            }
            setTimeout(() => {
                this.$refs.selectMonthYear.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('prepaid-card-extract.edit-item')
            this.editDialog = true
            this.extract = {
                id: item.id,
                year_month: item.year + '-' + item.month,
                credit: item.credit,
                credit_date: moment(item.credit_date).toDate(),
                remarks: item.remarks,
                prepaid_card_id: item.prepaid_card_id,
            }
            setTimeout(() => {
                this.$refs.selectMonthYear.focus()
            })
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.extract.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/prepaid-card/extract',
                {
                    year: this.extract.year_month.substring(0, 4),
                    month: this.extract.year_month.substring(5, 7),
                    credit: this.extract.credit,
                    credit_date: this.extract.credit_date,
                    remarks: this.extract.remarks,
                    prepaid_card_id: this.extract.prepaid_card_id,
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
                '/prepaid-card/extract/' + this.extract.id,
                {
                    credit: this.extract.credit,
                    credit_date: this.extract.credit_date,
                    remarks: this.extract.remarks,
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

        async confirmRemove(item) {
            this.deleteId = item.id
            if (
                await this.$refs.confirm.open(
                    this.$t('prepaid-card-extract.title-show'),
                    this.$t('default.confirm-delete-item')
                )
            ) {
                this.remove()
            }
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/prepaid-card/extract/${this.deleteId}`, {
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
