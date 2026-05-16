<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-income.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-row dense>
                    <v-col v-if="!viewOnly" md="12">
                        <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    </v-col>
                    <v-col md="12">
                        <v-data-table
                            :headers="headers"
                            :items="incomes"
                            :search="search"
                            :loading="isLoading"
                            :loading-text="$t('default.loading-text-table')"
                            class="elevation-3"
                            density="compact"
                            :total-items="incomes.length"
                            :items-per-page="25"
                            :no-data-text="$t('default.no-data-text')"
                            :no-results-text="$t('default.no-data-text')"
                            :footer-props="{
                                'items-per-page-text': $t('default.itens-per-page'),
                                'page-text': $t('default.page-text'),
                            }"
                            :header-props="{
                                sortByText: $t('default.sort-by'),
                            }"
                            :row-props="itemRowFont"
                            fixed-header
                        >
                            <!-- Itens -->
                            <template #[`item.date`]="{ item }">{{
                                item.date ? moment(item.date).format('DD/MM/YYYY') : null
                            }}</template>
                            <template #[`item.description`]="{ item }">{{
                                item.description.match(/(\S+)\.(\S+)/gm) ? $t(item.description) : item.description
                            }}</template>
                            <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                            <template #[`item.remarks`]="{ item }">{{
                                item.remarks && item.remarks.match(/(\S+)\.(\S+)/gm) ? $t(item.remarks) : item.remarks
                            }}</template>
                            <template #[`item.tags`]="{ item }">{{
                                item.tags?.length ? item.tags.map((x) => x.name).join(' | ') : ''
                            }}</template>
                            <template #[`item.action`]="{ item }">
                                <v-tooltip v-if="item.id" :text="$t('default.edit')" location="top">
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
                                <v-tooltip v-if="item.id" :text="$t('default.delete')" location="top">
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
                            <!-- Footer -->
                            <template v-if="incomes.length" #tfoot>
                                <tr class="text-green">
                                    <th class="title"></th>
                                    <th class="title font-weight-bold text-right">Total</th>
                                    <th class="title text-right">{{ sumField(incomes, 'value') }}</th>
                                </tr>
                            </template>
                            <!-- Top -->
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
                                v-model="income.description"
                                :label="$t('default.description')"
                                :error-messages="incomeForm.errors.description ? [incomeForm.errors.description] : []"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="3" md="4">
                            <v-date-input
                                ref="inputDate"
                                v-model="income.date"
                                :label="$t('default.date')"
                                :error-messages="incomeForm.errors.date ? [incomeForm.errors.date] : []"
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
                        <v-col cols="12" sm="4" md="4">
                            <vuetify-money
                                v-model="income.value"
                                :label="$t('default.value')"
                                :error-messages="incomeForm.errors.value ? [incomeForm.errors.value] : []"
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
                        <v-col cols="12" md="12">
                            <v-text-field
                                v-model="income.remarks"
                                :label="$t('default.remarks')"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="income.tags"
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
                <v-btn color="error" flat :loading="isLoading || incomeForm.processing" @click="editDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="isLoading || incomeForm.processing" type="submit" @click="save">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import { currencyField, formatDate, sumField } from '@/utils/utils.js'
import { router, useForm } from '@inertiajs/vue3'
import moment from 'moment'
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    import { useCrudOperations } from '@/composables/useCrudOperations.js'
import { useValidationRules } from '@/composables/useFormConstants.js'
import { useTagSearch } from '@/composables/useTagSearch.js'

    defineOptions({ name: 'BudgetIncome' })

    const componentProps = defineProps({
        budgetId: { type: Number },
        yearMonth: { type: String },
        incomes: { type: Array, default: () => [] },
        viewOnly: { type: Boolean },
    })

    const { t } = useI18n()
    const rules = useValidationRules()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/budget-income')
    const { tags: listTags, searchTags: doSearchTags } = useTagSearch()

    const search = ref(null)
    const deleteId = ref(null)
    const panel = ref(1)
    const searchTag = ref('')
    const loadingData = ref(false)

    const income = ref({
        id: null,
        description: null,
        value: 0,
        date: null,
        remarks: null,
        budget_id: null,
        tags: [],
    })

    function createIncomeFormData() {
        return {
            description: null,
            date: null,
            value: 0,
            remarks: null,
            budget_id: componentProps.budgetId,
            tags: [],
        }
    }

    const incomeForm = useForm(createIncomeFormData())

    // Refs for template
    const txtDescription = ref(null)
    const inputDate = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const itemsTags = computed(() => listTags.value)

    const headers = computed(() => {
        let hdrs = [
            { title: t('default.description'), align: 'start', key: 'description', groupable: false },
            { title: t('budget-income.date'), align: 'center', key: 'date' },
            { title: t('default.value'), align: 'end', key: 'value' },
            { title: t('default.remarks'), key: 'remarks' },
            { title: t('default.tags'), key: 'tags' },
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

    function resetIncomeState(data = createIncomeFormData()) {
        income.value = data
        incomeForm.clearErrors()
    }

    function normalizeIncomeDate(value) {
        if (!value) {
            return null
        }

        const normalized = moment(value)

        return normalized.isValid() ? normalized.format('YYYY-MM-DD') : null
    }

    function buildIncomePayload() {
        return {
            description: income.value.description,
            date: normalizeIncomeDate(income.value.date),
            value: income.value.value,
            remarks: income.value.remarks,
            budget_id: income.value.budget_id,
            tags: income.value.tags || [],
        }
    }

    async function searchTags(val) {
        loadingData.value = true
        doSearchTags(val, income.value.tags)
        setTimeout(() => {
            loadingData.value = false
        }, 300)
    }

    function itemRowFont(row) {
        return { class: !row.item.id ? 'font-weight-bold' : '' }
    }

    function newItem() {
        titleModal.value = t('budget-income.new-item')
        editDialog.value = true
        resetIncomeState({
            id: null,
            description: null,
            value: 0,
            date: moment(componentProps.yearMonth + '-01', 'YYYY-MM-DD').toDate(),
            remarks: null,
            tags: [],
            budget_id: componentProps.budgetId,
        })
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('budget-income.edit-item')
        editDialog.value = true

        resetIncomeState({
            id: item.id,
            description: item.description,
            value: Number(item.value),
            date: moment(item.date, 'YYYY-MM-DD').toDate(),
            remarks: item.remarks,
            tags: item.tags,
            budget_id: item.budget_id,
        })
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    async function save() {
        let validate = await form.value.validate()
        if (validate.valid) {
            if (income.value.id) {
                await updateData()
            } else {
                await createData()
            }
        }
    }

    async function createData() {
        isLoading.value = true
        incomeForm
            .transform(() => buildIncomePayload())
            .post('/budget-income', {
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
        incomeForm
            .transform(() => buildIncomePayload())
            .put('/budget-income/' + income.value.id, {
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
        if (await confirm.value.open(t('budget-income.item'), t('default.confirm-delete-item'))) {
            removeData()
        }
    }

    function removeData() {
        isLoading.value = true
        router.delete(`/budget-income/${deleteId.value}`, {
            onSuccess: () => {},
            onError: () => {
                isLoading.value = false
            },
            onFinish: () => {
                isLoading.value = false
            },
            preserveScroll: true,
        })
    }
</script>
