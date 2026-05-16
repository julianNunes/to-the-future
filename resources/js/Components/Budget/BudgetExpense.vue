<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-expense.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <BudgetExpenseTable
                    v-model:search="search"
                    :headers="headers"
                    :expenses="expenses"
                    :is-loading="isLoading"
                    :view-only="viewOnly"
                    :row-props="itemRowFont"
                    :group-label="convertGroup"
                    :installment-label="infoInstallment"
                    @new="newItem"
                    @edit="editItem"
                    @remove="confirmRemove"
                />
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels>

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
                                :error-messages="expenseForm.errors.description ? [expenseForm.errors.description] : []"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="3" md="3">
                            <v-date-input
                                ref="inputDate"
                                v-model="expense.date"
                                :label="$t('default.date')"
                                :error-messages="expenseForm.errors.date ? [expenseForm.errors.date] : []"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :year="yearToDateInput"
                                :month="monthToDateInput"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" sm="4" md="3">
                            <vuetify-money
                                v-model="expense.value"
                                :label="$t('default.value')"
                                :error-messages="expenseForm.errors.value ? [expenseForm.errors.value] : []"
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
                            <v-text-field
                                v-model="expense.portion"
                                type="number"
                                :label="$t('default.portion')"
                                :error-messages="expenseForm.errors.portion ? [expenseForm.errors.portion] : []"
                                :disabled="expense.id ? true : false"
                                min="0"
                                step="1"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.portion_total"
                                type="number"
                                :label="$t('default.portion-total')"
                                :error-messages="
                                    expenseForm.errors.portion_total ? [expenseForm.errors.portion_total] : []
                                "
                                :disabled="expense.id ? true : false"
                                min="0"
                                step="1"
                                required
                                :rules="[
                                    (value) => {
                                        if (expense.portion) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                            if (parseFloat(value) === 1) return $t('rules.minimum-portion')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="expense.group"
                                :label="$t('default.group')"
                                :error-messages="expenseForm.errors.group ? [expenseForm.errors.group] : []"
                                :items="groupList"
                                item-title="name"
                                item-value="value"
                                clearable
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="4" md="3">
                            <v-select
                                v-model="expense.paid"
                                label="Status"
                                :error-messages="expenseForm.errors.paid ? [expenseForm.errors.paid] : []"
                                :items="listStatus"
                                item-title="name"
                                item-value="value"
                                clearable
                                required
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="percentage"
                                type="number"
                                :label="$t('default.percentage-share')"
                                density="comfortable"
                                @blur="calculeShareValue"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="expense.share_value"
                                :label="$t('default.share-value')"
                                :error-messages="expenseForm.errors.share_value ? [expenseForm.errors.share_value] : []"
                                density="comfortable"
                                :rules="[
                                    (value) => {
                                        if (expense.share_user_id) {
                                            value = reverseFormatNumber(value)
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                :options="{
                                    locale: 'pt-BR',
                                    prefix: 'R$',
                                    suffix: '',
                                    length: 11,
                                    precision: 2,
                                }"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="expense.share_user_id"
                                :label="$t('default.share-user')"
                                :error-messages="
                                    expenseForm.errors.share_user_id ? [expenseForm.errors.share_user_id] : []
                                "
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
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="expense.tags"
                                v-model:search="searchTag"
                                :label="$t('default.tags')"
                                :items="itemsTags"
                                :loading="loadingData"
                                item-title="name"
                                item-value="name"
                                clearable
                                multiple
                                chips
                                :closable-chips="true"
                                :clear-on-select="true"
                                return-object
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                @update:search="searchTags"
                                @update:model-value="searchTag = ''"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="isLoading || expenseForm.processing" @click="editDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="isLoading || expenseForm.processing" type="submit" @click="save">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import { router, useForm } from '@inertiajs/vue3'
import moment from 'moment'
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    import { currencyField, formatDate, reverseFormatNumber } from '@/utils/utils.js'

    import BudgetExpenseTable from './BudgetExpenseTable.vue'

    import { useCrudOperations } from '@/composables/useCrudOperations.js'
import { useValidationRules } from '@/composables/useFormConstants.js'
import { useShareCalculation } from '@/composables/useShareCalculation.js'
import { useTagSearch } from '@/composables/useTagSearch.js'

    defineOptions({ name: 'BudgetExpense' })

    const componentProps = defineProps({
        budgetId: { type: Number },
        yearMonth: { type: String },
        expenses: {
            type: Array,
            default: () => [],
        },
        shareUsers: { type: Array },
        installments: { type: Array },
        viewOnly: { type: Boolean, default: false },
    })

    const { t } = useI18n()
    const rules = useValidationRules()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/budget-expense')

    const { tags: listTags, searchTags: doSearchTags } = useTagSearch()

    const { calculateShareValue } = useShareCalculation()

    const search = ref(null)
    const panel = ref(1)
    const percentage = ref(null)
    const deleteId = ref(null)
    const loadingData = ref(false)
    const searchTag = ref('')
    const deleteAllPortions = ref(false)
    const deleteDialog = ref(false)

    const expense = ref({
        id: null,
        description: null,
        value: 0,
        portion: null,
        portion_total: null,
        group: null,
        date: null,
        paid: 0,
        remarks: null,
        share_value: 0,
        share_user_id: null,
        budget_id: null,
        tags: [],
    })

    function createExpenseFormData() {
        return {
            description: null,
            date: null,
            value: 0,
            portion: null,
            portion_total: null,
            group: null,
            paid: 0,
            remarks: null,
            share_value: 0,
            share_user_id: null,
            budget_id: componentProps.budgetId,
            tags: [],
        }
    }

    const expenseForm = useForm(createExpenseFormData())

    const listInstallments = ref([])

    const listStatus = [
        { value: 0, name: t('default.open') },
        { value: 1, name: t('default.paid') },
    ]

    const groupList = [
        { name: t('default.monthly'), value: 'MONTHLY' },
        { name: t('default.individual'), value: 'INDIVIDUAL' },
    ]

    // Refs for template
    const txtDescription = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const itemsTags = computed(() => listTags.value)

    const headers = computed(() => {
        let hdrs = [
            { title: t('default.description'), align: 'start', key: 'description', groupable: false },
            { title: t('budget-expense.due-date'), align: 'center', key: 'date' },
            { title: t('default.value'), align: 'end', key: 'value' },
            { title: t('default.portion'), key: 'portion' },
            { title: t('default.group'), align: 'start', key: 'group' },
            { title: t('default.share-value'), align: 'end', key: 'share_value' },
            { title: t('default.share-user'), key: 'share_user_id' },
            { title: t('default.remarks'), key: 'remarks' },
            { title: t('default.tags'), key: 'tags' },
            { title: 'Status', key: 'paid' },
            { title: t('budget-expense.finaning-installment'), align: 'center', key: 'data-table-expand' },
        ]

        if (!componentProps.viewOnly) {
            hdrs.push({
                title: t('default.action'),
                align: 'end',
                key: 'action',
                sortable: false,
                width: 40,
            })
        }
        return hdrs
    })

    const monthToDateInput = computed(() => moment(componentProps.yearMonth + '-01').month())
    const yearToDateInput = computed(() => moment(componentProps.yearMonth + '-01').year())

    function resetExpenseState(data = createExpenseFormData()) {
        expense.value = data
        expenseForm.clearErrors()
    }

    function normalizeExpenseDate(value) {
        if (!value) {
            return null
        }

        const normalized = moment(value)

        return normalized.isValid() ? normalized.format('YYYY-MM-DD') : null
    }

    function buildExpensePayload() {
        return {
            description: expense.value.description,
            date: normalizeExpenseDate(expense.value.date),
            value: expense.value.value,
            portion: expense.value.portion ? Number(expense.value.portion) : null,
            portion_total: expense.value.portion_total ? Number(expense.value.portion_total) : null,
            paid: Boolean(expense.value.paid),
            group: expense.value.group,
            remarks: expense.value.remarks,
            share_value: expense.value.share_value,
            share_user_id: expense.value.share_user_id,
            budget_id: expense.value.budget_id,
            tags: expense.value.tags || [],
        }
    }

    function calculeShareValue(evt) {
        if (expense.value.value) {
            expense.value.share_value = calculateShareValue(expense.value.value, evt.target.value)
        }
    }

    function convertGroup(group) {
        return groupList.find((x) => x.value === group)?.name || group
    }

    async function searchTags(val) {
        loadingData.value = true
        const existing = expense.value.tags ? expense.value.tags : []
        doSearchTags(val, existing)
        setTimeout(() => {
            loadingData.value = false
        }, 300)
    }

    function itemRowFont(row) {
        return { class: !row.item.id ? 'font-weight-bold ' : '' }
    }

    function infoInstallment(item) {
        if (item) {
            return (
                t('default.description') +
                ': ' +
                item.financing.description +
                ' | ' +
                t('budget-expense.due-date') +
                ': ' +
                moment(item.date).format('DD/MM/YYYY') +
                ' | ' +
                t('default.value') +
                ': ' +
                currencyField(item.value) +
                ' | ' +
                t('financing-installment.portion') +
                ': ' +
                item.portion
            )
        } else {
            return 'nao tem item'
        }
    }

    function newItem() {
        titleModal.value = t('budget-expense.new-item')
        editDialog.value = true
        listInstallments.value = componentProps.installments || []
        resetExpenseState({
            id: null,
            description: null,
            value: 0,
            portion: null,
            portion_total: null,
            date: moment(componentProps.yearMonth + '-01', 'YYYY-MM-DD').toDate(),
            group: null,
            remarks: null,
            paid: 0,
            share_value: 0,
            share_user_id: null,
            tags: [],
            budget_id: componentProps.budgetId,
        })
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('budget-expense.edit-item')
        editDialog.value = true
        let data = componentProps.installments ? [...componentProps.installments] : []

        if (item.financing_installment) {
            data.unshift(item.financing_installment)
        }

        listInstallments.value = data
        resetExpenseState({
            id: item.id,
            description: item.description,
            value: Number(item.value),
            portion: item.portion,
            portion_total: item.portion_total,
            date: moment(item.date, 'YYYY-MM-DD').toDate(),
            paid: item.paid ? 1 : 0,
            group: item.group,
            remarks: item.remarks,
            share_value: item.share_value ? Number(item.share_value) : 0,
            share_user_id: item.share_user_id,
            tags: item.tags || [],
            budget_id: item.budget_id,
        })
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    async function save() {
        let validate = await form.value.validate()
        if (validate.valid) {
            if (expense.value.id) {
                await updateData()
            } else {
                await createData()
            }
        }
    }

    async function createData() {
        isLoading.value = true
        expenseForm
            .transform(() => buildExpensePayload())
            .post('/budget-expense', {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
    }

    async function updateData() {
        isLoading.value = true
        expenseForm
            .transform(() => buildExpensePayload())
            .put('/budget-expense/' + expense.value.id, {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
    }

    async function confirmRemove(item) {
        deleteId.value = item.id
        deleteAllPortions.value = false
        expense.value = { ...item }
        deleteDialog.value = true
        if (await confirm.value.open(t('budget-expense.budget-expense'), t('default.confirm-delete-item'))) {
            removeData()
        }
    }

    function removeData() {
        isLoading.value = true

        if (deleteAllPortions.value) {
            router.delete(`/budget-expense/${deleteId.value}/delete-all-portions`, {
                onSuccess: () => {
                    editDialog.value = false
                },
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
        } else {
            router.delete(`/budget-expense/${deleteId.value}`, {
                onSuccess: () => {
                    editDialog.value = false
                },
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
        }
    }
</script>
