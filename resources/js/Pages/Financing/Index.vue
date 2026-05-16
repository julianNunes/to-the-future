<template>
    <Head title="Financing" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('financing.title') }}</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <!-- Tabela com dados -->
    <v-card>
        <v-card-text>
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
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescription"
                                v-model="financingForm.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                :error-messages="financingForm.errors.description"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-date-input
                                v-model="financingForm.start_date"
                                :label="$t('financing.start-date')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                :rules="rules.textFieldRules"
                                :error-messages="financingForm.errors.start_date"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="financingForm.total"
                                :label="$t('default.total')"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :error-messages="financingForm.errors.total"
                                :options="currencyConfig"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="financingForm.fees_monthly"
                                :label="$t('financing.fees-monthly')"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :error-messages="financingForm.errors.fees_monthly"
                                :options="percentageConfig"
                            />
                        </v-col>
                        <v-col v-if="!financingForm.id" cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="financingForm.portion_total"
                                type="number"
                                :label="$t('financing.portion-total')"
                                min="2"
                                required
                                :rules="rules.numberFieldRules"
                                :error-messages="financingForm.errors.portion_total"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col v-if="!financingForm.id" cols="12" sm="6" md="3">
                            <v-date-input
                                v-model="financingForm.start_date_installment"
                                :label="$t('financing.start-date')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                :rules="rules.textFieldRules"
                                :error-messages="financingForm.errors.start_date_installment"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col v-if="!financingForm.id" cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="financingForm.value_installment"
                                :label="$t('financing.installment-value')"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :error-messages="financingForm.errors.value_installment"
                                :options="currencyConfig"
                            />
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-text-field
                                v-model="financingForm.remarks"
                                :label="$t('default.remarks')"
                                :error-messages="financingForm.errors.remarks"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="financingForm.processing" @click="editDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="financingForm.processing" type="submit" @click="save">
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
    import { useCurrencyConfig, useValidationRules } from '@/composables/useFormConstants.js'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { currencyField, formatDate, percentField } from '@/utils/utils.js'
    import { Head, Link, useForm } from '@inertiajs/vue3'
    import moment from 'moment'
    import { computed, nextTick, ref } from 'vue'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'FinancingIndex', layout: AuthenticatedLayout })

    defineProps({
        financings: { type: Array },
    })

    const { t } = useI18n()

    const { isLoading, editDialog, titleModal, confirmRemove: crudConfirmRemove } = useCrudOperations('/financing')

    const search = ref(null)
    const txtDescription = ref(null)
    const form = ref(null)
    const confirm = ref(null)
    const currencyConfig = useCurrencyConfig()
    const percentageConfig = {
        ...currencyConfig,
        prefix: '',
        suffix: '%',
    }
    const rules = useValidationRules()

    function createEmptyFinancing() {
        return {
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
    }

    const financingForm = useForm(createEmptyFinancing())

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.financing'), disabled: true },
    ])

    const headers = computed(() => [
        { title: t('default.description'), align: 'start', key: 'description' },
        { title: t('financing.start-date'), align: 'center', key: 'start_date' },
        { title: t('financing.fees-monthly'), align: 'end', key: 'fees_monthly' },
        { title: t('financing.portion-total'), align: 'end', key: 'portion_total' },
        { title: t('default.total'), align: 'end', key: 'total' },
        { title: t('default.remarks'), key: 'remarks' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ])

    function hrefInstalmment(item) {
        return '/financing/' + item.id + '/installment'
    }

    function formatRequestDate(value) {
        return value ? moment(value).format('YYYY-MM-DD') : null
    }

    function newItem() {
        titleModal.value = t('financing.new-item')
        editDialog.value = true
        Object.assign(financingForm, createEmptyFinancing())
        financingForm.clearErrors()
        form.value?.resetValidation()
        nextTick(() => txtDescription.value?.focus())
    }

    function editItem(item) {
        titleModal.value = t('financing.edit-item')
        editDialog.value = true
        Object.assign(financingForm, createEmptyFinancing(), {
            id: item.id,
            description: item.description,
            start_date: moment(item.start_date, 'YYYY-MM-DD').toDate(),
            total: Number(item.total),
            fees_monthly: item.fees_monthly ? Number(item.fees_monthly) : 0,
            portion_total: Number(item.portion_total),
            remarks: item.remarks,
        })
        financingForm.clearErrors()
        form.value?.resetValidation()
        nextTick(() => txtDescription.value?.focus())
    }

    async function save() {
        const validate = await form.value.validate()
        if (validate.valid) {
            if (financingForm.id) {
                await _update()
            } else {
                await _create()
            }
        }
    }

    async function _create() {
        financingForm
            .transform((data) => ({
                description: data.description,
                start_date: formatRequestDate(data.start_date),
                total: data.total,
                fees_monthly: data.fees_monthly,
                portion_total: data.portion_total,
                remarks: data.remarks,
                start_date_installment: formatRequestDate(data.start_date_installment),
                value_installment: data.value_installment,
            }))
            .post('/financing', {
                preserveScroll: true,
                onSuccess: () => {
                    editDialog.value = false
                    Object.assign(financingForm, createEmptyFinancing())
                    financingForm.clearErrors()
                },
            })
    }

    async function _update() {
        financingForm
            .transform((data) => ({
                description: data.description,
                start_date: formatRequestDate(data.start_date),
                total: data.total,
                fees_monthly: data.fees_monthly,
                remarks: data.remarks,
            }))
            .put(`/financing/${financingForm.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    editDialog.value = false
                    financingForm.clearErrors()
                },
            })
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('financing.item'), t('default.confirm-delete-item'))
    }
</script>
