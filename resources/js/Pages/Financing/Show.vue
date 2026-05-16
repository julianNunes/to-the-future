<template>
    <Head title="Financing" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('financing-installment.title') }}</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <!-- Dados do Financiamento -->
    <v-card>
        <v-card-title class="bg-primary">
            <span class="text-h6">{{ $t('financing-installment.data-financing') }}</span>
        </v-card-title>
        <v-card-text class="pa-4">
            <v-row dense>
                <v-col cols="12" sm="12" md="12">
                    <v-text-field
                        v-model="financingDescription"
                        :label="$t('default.description')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-text-field
                        v-model="financingStartDate"
                        :label="$t('financing.start-date')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-text-field
                        v-model="financingTotal"
                        :label="$t('default.total')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-text-field
                        v-model="financingFeesMonthly"
                        :label="$t('financing.fees-monthly')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-text-field
                        v-model="financingPortionTotal"
                        :label="$t('financing.portion-total')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" md="12">
                    <v-text-field
                        v-model="financingRemarks"
                        :label="$t('default.remarks')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>

    <!-- Parcelas -->
    <v-card class="mt-4">
        <v-card-title class="bg-primary">
            <span class="text-h6">{{ $t('financing-installment.portions') }}</span>
        </v-card-title>
        <v-card-text class="pa-4">
            <v-row dense>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="installments"
                        :sort-by="[{ key: 'portion', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="installments.length"
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
                        <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                        <template #[`item.paid_value`]="{ item }">{{
                            item.paid_value ? currencyField(item.paid_value) : null
                        }}</template>
                        <template #[`item.date`]="{ item }">{{ moment(item.date).format('DD/MM/YYYY') }}</template>
                        <template #[`item.payment_date`]="{ item }">{{
                            item.payment_date ? moment(item.payment_date).format('DD/MM/YYYY') : null
                        }}</template>

                        <template #[`item.paid`]="{ item }">{{
                            item.paid ? $t('default.paid') : $t('default.open')
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
                        </template>

                        <template v-if="installments.length" #tfoot>
                            <tr class="text-green">
                                <th class="title"></th>
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumField(installments, 'value') }}</th>
                                <th class="title text-right">{{ sumField(installments, 'paid_value') }}</th>
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
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="installment.portion"
                                :label="$t('financing-installment.portion')"
                                :readonly="true"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-date-input
                                v-model="installment.date"
                                :label="$t('default.date')"
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
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="installment.value"
                                :label="$t('default.value')"
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
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="installment.paid"
                                label="Status"
                                :items="listStatus"
                                item-title="name"
                                item-value="value"
                                clearable
                                :rules="[
                                    (value) => {
                                        if (installment.paid == null) {
                                            return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-date-input
                                v-model="installment.payment_date"
                                :label="$t('financing-installment.payment-date')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                density="comfortable"
                                :rules="[
                                    (value) => {
                                        if (installment.paid == 1) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="installment.paid_value"
                                type="number"
                                :label="$t('financing-installment.paid-value')"
                                min="0"
                                required
                                :rules="[
                                    (value) => {
                                        if (installment.paid == 1) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
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
</template>

<script setup>
    import { ref, computed } from 'vue'
    import Breadcrumbs from '@/Components/Breadcrumbs.vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { Head, router } from '@inertiajs/vue3'
    import moment from 'moment'
    import { currencyField, formatDate, percentField, reverseFormatNumber, sumField } from '@/utils/utils.js'
    import { useI18n } from 'vue-i18n'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'

    defineOptions({ name: 'FinancingShow', layout: AuthenticatedLayout })

    const props = defineProps({
        financing: { type: Object },
        installments: { type: Array },
    })

    const { t } = useI18n()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/financing/installment')

    const search = ref(null)
    const form = ref(null)

    const installment = ref({
        id: null,
        portion: 0,
        value: 0,
        paid_value: 0,
        date: null,
        payment_date: null,
        paid: false,
    })

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.financing'), disabled: false, href: '/financing' },
        { title: t('financing-installment.title'), disabled: true },
    ])

    const headers = computed(() => [
        { title: t('financing-installment.portion'), align: 'start', key: 'portion' },
        { title: t('default.date'), align: 'center', key: 'date' },
        { title: t('default.value'), align: 'end', key: 'value' },
        { title: t('financing-installment.paid-value'), align: 'end', key: 'paid_value' },
        { title: t('financing-installment.payment-date'), align: 'center', key: 'payment_date' },
        { title: 'Status', align: 'start', key: 'paid' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ])

    const listStatus = computed(() => [
        { value: 0, name: t('default.open') },
        { value: 1, name: t('default.paid') },
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

    const financingDescription = computed(() => props.financing.description)
    const financingStartDate = computed(() => moment(props.financing.start_date).format('DD/MM/YYYY'))
    const financingTotal = computed(() => currencyField(props.financing.total))
    const financingFeesMonthly = computed(() => percentField(props.financing.fees_monthly))
    const financingPortionTotal = computed(() => props.financing.portion_total)
    const financingRemarks = computed(() => props.financing.remarks)

    function editItem(item) {
        titleModal.value = t('financing-installment.edit-item')
        editDialog.value = true
        installment.value = {
            id: item.id,
            portion: Number(item.portion),
            date: moment(item.date, 'YYYY-MM-DD').toDate(),
            value: Number(item.value),
            paid: item.paid ? 1 : 0,
            paid_value: item.paid_value ? Number(item.paid_value) : 0,
            payment_date: item.payment_date ? moment(item.payment_date, 'YYYY-MM-DD').toDate() : null,
        }
    }

    async function save() {
        const validate = await form.value.validate()
        if (validate.valid) {
            await _update()
        }
    }

    async function _update() {
        isLoading.value = true
        router.put(
            `/financing/installment/${installment.value.id}`,
            {
                date: moment(installment.value.date).format('YYYY-MM-DD'),
                value: installment.value.value,
                paid: !!installment.value.paid,
                payment_date: installment.value.payment_date
                    ? moment(installment.value.payment_date).format('YYYY-MM-DD')
                    : null,
                paid_value: installment.value.paid_value,
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
</script>
