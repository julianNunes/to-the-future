<template>
    <Head title="Credit Card" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('credit-card-invoice.title-index') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>

        <!-- Dados do cartão de credito -->
        <v-card>
            <v-card-title class="bg-primary">
                <span class="text-h6">{{ $t('credit-card-invoice.credit-card-title') }}</span>
            </v-card-title>
            <v-card-text class="pa-4">
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
            <v-card-text class="pa-4">
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    </v-col>
                    <v-col md="12">
                        <v-data-table
                            :headers="headers"
                            :items="invoices"
                            :sort-by="[{ key: 'due_date', order: 'desc' }]"
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
                            :items-per-page="50"
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
                                            <v-icon
                                                v-bind="props"
                                                color="warning"
                                                icon="mdi-eye"
                                                size="small"
                                                class="me-2"
                                            >
                                            </v-icon>
                                        </Link>
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
                                    v-model="yearMonth"
                                    type="month"
                                    :label="$t('credit-card-invoice.year-month')"
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

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
    import { ref, computed, nextTick } from 'vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
    import { Head, Link, router } from '@inertiajs/vue3'
    import moment from 'moment'
    import { currencyField } from '@/utils/utils.js'
    import { useI18n } from 'vue-i18n'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'

    defineOptions({ name: 'CreditCardInvoiceIndex' })

    const componentProps = defineProps({
        creditCard: {
            type: Object,
        },
        invoices: {
            type: Array,
        },
    })

    const { t } = useI18n()

    const {
        isLoading,
        editDialog,
        titleModal,
        confirmRemove: crudConfirmRemove,
    } = useCrudOperations('/credit-card/invoice')

    const breadcrumbs = computed(() => [
        {
            title: t('menus.dashboard'),
            disabled: false,
            href: '/dashboard',
        },
        {
            title: t('menus.credit-card'),
            disabled: false,
            href: '/credit-card',
        },
        {
            title: t('credit-card-invoice.title-index'),
            disabled: true,
        },
    ])

    const headers = computed(() => [
        { title: t('credit-card-invoice.closing-date'), key: 'closing_date' },
        { title: t('credit-card-invoice.due-date'), key: 'due_date' },
        { title: 'Total', key: 'total' },
        { title: t('default.total-paid'), key: 'total_paid' },
        { title: t('credit-card-invoice.closed'), key: 'closed' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ])

    const rules = {
        textFieldRules: [(v) => !!v || t('rules.required-text-field')],
    }

    const automaticGenerate = ref(false)
    const yearMonth = ref(null)
    const search = ref(null)

    const credit_card = ref(componentProps.creditCard)

    const form = ref(null)
    const confirm = ref(null)
    const selectMonthYear = ref(null)

    const isActive = computed(() => {
        return credit_card.value.is_active ? t('default.yes') : t('default.no')
    })

    function hrefInvoiceShow(item) {
        return '/credit-card/invoice/' + item.id
    }

    function newItem() {
        titleModal.value = t('credit-card-invoice.new-item')
        editDialog.value = true
        yearMonth.value = null
        automaticGenerate.value = false
        nextTick(() => {
            selectMonthYear.value?.focus()
        })
    }

    async function save() {
        let validate = await form.value.validate()
        if (validate.valid) {
            isLoading.value = true
            router.post(
                '/credit-card/invoice',
                {
                    due_date: yearMonth.value + '-' + credit_card.value.due_date,
                    closing_date: yearMonth.value + '-' + credit_card.value.closing_date,
                    year: yearMonth.value.substring(0, 4),
                    month: yearMonth.value.substring(5, 7),
                    credit_card_id: credit_card.value.id,
                    automatic_generate: automaticGenerate.value,
                },
                {
                    onSuccess: () => {
                        editDialog.value = false
                    },
                    onFinish: () => {
                        isLoading.value = false
                    },
                }
            )
        }
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('credit-card-invoice.title-show'), t('default.confirm-delete-item'))
    }
</script>
