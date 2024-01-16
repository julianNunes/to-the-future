<template>
    <Head title="Credit Card" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('credit-card.title') }}</h5>
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
                        :items="creditCards"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="creditCards.length"
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
                        <template #[`item.is_active`]="{ item }">{{
                            item.is_active ? $t('default.yes') : $t('default.no')
                        }}</template>
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('credit-card.invoices')" location="top">
                                <template #activator="{ props }">
                                    <Link :href="hrefInvoice(item)" class="v-breadcrumbs-item--link">
                                        <v-icon
                                            v-bind="props"
                                            color="warning"
                                            icon="mdi-checkbook"
                                            size="small"
                                            class="me-2"
                                        >
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
                                        class="me-2 ml-2"
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
                            <v-col cols="12" sm="12" md="6">
                                <v-text-field
                                    ref="txtName"
                                    v-model="creditCard.name"
                                    :label="$t('default.name')"
                                    :rules="rules.textFieldRules"
                                    required
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="creditCard.digits"
                                    :label="$t('credit-card.4-digits')"
                                    :counter="4"
                                    :maxlength="4"
                                    required
                                    :rules="rules.digitsFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="creditCard.due_date"
                                    :label="$t('credit-card.due-date')"
                                    :items="days"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="creditCard.closing_date"
                                    :label="$t('credit-card.closing-date')"
                                    :items="days"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="creditCard.is_active"
                                    :label="$t('default.active')"
                                    :items="isActiveOptions"
                                    item-title="name"
                                    item-value="value"
                                    clearable
                                    :rules="rules.booleanFieldRules"
                                    density="comfortable"
                                ></v-select>
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
import { Head, Link } from '@inertiajs/vue3'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
</script>

<script>
export default {
    name: 'ProvisionIndex',
    props: {
        creditCards: {
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
                    title: this.$t('menus.credit-card'),
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.name'), key: 'name', groupable: false },
                { title: this.$t('credit-card.digits'), key: 'digits' },
                { title: this.$t('credit-card.due-date'), key: 'due_date' },
                { title: this.$t('credit-card.closing-date'), key: 'closing_date' },
                { title: this.$t('default.active'), key: 'is_active' },
                { title: this.$t('default.action'), align: 'end', key: 'action', sortable: false },
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
            removeDialog: false,
            isLoading: false,
            deleteId: null,
            creditCard: {
                id: null,
                name: null,
                digits: null,
                due_date: null,
                closing_date: null,
                is_active: null,
            },
            days: [
                '1',
                '2',
                '3',
                '4',
                '5',
                '6',
                '7',
                '8',
                '9',
                '10',
                '11',
                '12',
                '13',
                '14',
                '15',
                '16',
                '17',
                '18',
                '19',
                '20',
                '21',
                '22',
                '23',
                '24',
                '25',
                '26',
                '27',
                '28',
                '29',
                '30',
                '31',
            ],
            isActiveOptions: [
                {
                    name: this.$t('default.no'),
                    value: 0,
                },
                {
                    name: this.$t('default.yes'),
                    value: 1,
                },
            ],
        }
    },

    async created() {},

    async mounted() {},

    methods: {
        hrefInvoice(item) {
            return '/credit-card/' + item.id + '/invoice'
        },

        newItem() {
            this.titleModal = this.$t('credit-card.new-item')
            this.editDialog = true
            this.creditCard = {
                id: null,
                name: null,
                digits: null,
                due_date: null,
                closing_date: null,
                is_active: null,
            }
            setTimeout(() => {
                this.$refs.txtName.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('credit-card.edit-item')
            this.editDialog = true
            this.creditCard = {
                id: item.id,
                name: item.name,
                digits: item.digits,
                due_date: item.due_date,
                closing_date: item.closing_date,
                is_active: item.is_active,
            }
            setTimeout(() => {
                this.$refs.txtName.focus()
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
                '/credit-card',
                {
                    name: this.creditCard.name,
                    digits: this.creditCard.digits,
                    due_date: this.creditCard.due_date,
                    closing_date: this.creditCard.closing_date,
                    is_active: this.creditCard.is_active,
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
                '/credit-card/' + this.creditCard.id,
                {
                    name: this.creditCard.name,
                    digits: this.creditCard.digits,
                    due_date: this.creditCard.due_date,
                    closing_date: this.creditCard.closing_date,
                    is_active: this.creditCard.is_active,
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
            this.$inertia.delete(`/credit-card/${this.deleteId}`, {
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
