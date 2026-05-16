<template>
    <Head title="Prepaid Card Extract" />
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
                                        <v-icon v-bind="props" color="warning" icon="mdi-eye" size="small"> </v-icon>
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
                                v-model="extractForm.yearMonth"
                                type="month"
                                :label="$t('prepaid-card-extract.year-month')"
                                :error-messages="extractYearMonthErrors"
                                clearable
                                :rules="rules.textFieldRules"
                                density="comfortable"
                                :disabled="Boolean(extractForm.id)"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <vuetify-money
                                v-model="extractForm.credit"
                                :label="$t('prepaid-card-extract.credit')"
                                :error-messages="extractCreditErrors"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :options="{
                                    locale: 'pt-BR',
                                    prefix: 'R$',
                                    suffix: '',
                                    length: 11,
                                    precision: 2,
                                }"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="4">
                            <v-date-input
                                v-model="extractForm.credit_date"
                                :label="$t('financing.start-date')"
                                :error-messages="extractCreditDateErrors"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-textarea
                                v-model="extractForm.remarks"
                                :label="$t('default.remarks')"
                                density="comfortable"
                            ></v-textarea>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="extractForm.processing" @click="editDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="extractForm.processing" type="submit" @click="save">
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
    import { currencyField, formatDate, reverseFormatNumber } from '@/utils/utils.js'
    import { Head, Link, useForm } from '@inertiajs/vue3'
    import moment from 'moment'
    import { computed, nextTick, ref } from 'vue'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'PrepaidCardExtractIndex', layout: AuthenticatedLayout })

    const props = defineProps({
        prepaidCard: { type: Object },
        extracts: { type: Array },
    })

    const { t } = useI18n()

    const {
        isLoading,
        editDialog,
        titleModal,
        confirmRemove: crudConfirmRemove,
    } = useCrudOperations('/prepaid-card/extract')

    const prepaid_card = ref({ ...props.prepaidCard })
    const search = ref(null)
    const selectMonthYear = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    function createEmptyExtract() {
        return {
            id: null,
            yearMonth: null,
            credit: 0,
            credit_date: null,
            remarks: null,
            prepaid_card_id: props.prepaidCard.id,
        }
    }

    const extractForm = useForm(createEmptyExtract())

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.prepaid-card'), disabled: false, href: '/prepaid-card' },
        { title: t('prepaid-card-extract.title-index'), disabled: true },
    ])

    const headers = computed(() => [
        { title: t('default.year-month'), key: 'year_month' },
        { title: t('prepaid-card-extract.credit-date'), key: 'credit_date' },
        { title: t('prepaid-card-extract.credit'), key: 'credit' },
        { title: t('default.remarks'), key: 'remarks' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ])

    const rules = {
        textFieldRules: [(v) => !!v || t('rules.required-text-field')],
        currencyFieldRules: [
            (value) => {
                value = reverseFormatNumber(value)
                if (!value) return t('rules.required-text-field')
                if (Number(value) <= 0) return t('rules.required-currency-field')
                return true
            },
        ],
    }

    const isActive = computed(() => (prepaid_card.value.is_active ? t('default.yes') : t('default.no')))

    const extractYearMonthErrors = computed(() => {
        return [extractForm.errors.year, extractForm.errors.month, extractForm.errors.error].filter(Boolean)
    })

    const extractCreditErrors = computed(() => {
        return [extractForm.errors.credit].filter(Boolean)
    })

    const extractCreditDateErrors = computed(() => {
        return [extractForm.errors.credit_date].filter(Boolean)
    })

    function hrefExtractShow(item) {
        return '/prepaid-card/extract/' + item.id
    }

    function resetExtractForm() {
        extractForm.reset()
        extractForm.clearErrors()
    }

    function formatExtractDate(value) {
        if (!value) {
            return null
        }

        if (typeof value === 'string') {
            const normalized = value.trim()
            if (!normalized) {
                return null
            }

            if (/^\d{4}-\d{1,2}-\d{1,2}$/.test(normalized)) {
                return moment(normalized, 'YYYY-M-D').format('YYYY-MM-DD')
            }
        }

        const formatted = moment(value)

        return formatted.isValid() ? formatted.format('YYYY-MM-DD') : null
    }

    function buildExtractCreatePayload() {
        const [year, month] = (extractForm.yearMonth ?? '').split('-')

        return {
            year: year ?? null,
            month: month ?? null,
            credit: extractForm.credit,
            credit_date: formatExtractDate(extractForm.credit_date),
            remarks: extractForm.remarks,
            prepaid_card_id: props.prepaidCard.id,
        }
    }

    function buildExtractUpdatePayload() {
        return {
            credit: extractForm.credit,
            credit_date: formatExtractDate(extractForm.credit_date),
            remarks: extractForm.remarks,
        }
    }

    function newItem() {
        titleModal.value = t('prepaid-card-extract.new-item')
        editDialog.value = true
        resetExtractForm()
        form.value?.resetValidation()
        nextTick(() => selectMonthYear.value?.focus())
    }

    function editItem(item) {
        titleModal.value = t('prepaid-card-extract.edit-item')
        editDialog.value = true
        resetExtractForm()
        Object.assign(extractForm, createEmptyExtract(), {
            id: item.id,
            yearMonth: item.year + '-' + item.month,
            credit: item.credit,
            credit_date: moment(item.credit_date, 'YYYY-MM-DD').toDate(),
            remarks: item.remarks,
            prepaid_card_id: item.prepaid_card_id,
        })
        form.value?.resetValidation()
        nextTick(() => selectMonthYear.value?.focus())
    }

    async function save() {
        const validate = await form.value.validate()
        if (validate.valid) {
            if (extractForm.id) {
                await _update()
            } else {
                await _create()
            }
        }
    }

    async function _create() {
        extractForm
            .transform(() => buildExtractCreatePayload())
            .post('/prepaid-card/extract', {
                preserveScroll: true,
                onSuccess: () => {
                    editDialog.value = false
                    resetExtractForm()
                },
            })
    }

    async function _update() {
        extractForm
            .transform(() => buildExtractUpdatePayload())
            .put('/prepaid-card/extract/' + extractForm.id, {
                preserveScroll: true,
                onSuccess: () => {
                    editDialog.value = false
                    resetExtractForm()
                },
            })
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('prepaid-card-extract.title-show'), t('default.confirm-delete-item'))
    }
</script>
