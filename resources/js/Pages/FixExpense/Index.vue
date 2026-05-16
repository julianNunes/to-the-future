<template>
    <Head title="Fix Expense" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">
            {{ $t('fix-expense.title') }}
        </h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <!-- Tabela com dados -->
    <v-card>
        <v-card-text>
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">
                        {{ $t('default.new') }}
                    </v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="expenses"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="expenses.length"
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
                        <template #[`item.value`]="{ item }">
                            {{ currencyField(item.value) }}
                        </template>
                        <template #[`item.share_value`]="{ item }">
                            {{ currencyField(item.share_value) }}
                        </template>
                        <template #[`item.tags`]="{ item }">
                            {{ item.tags.length ? item.tags.map((x) => x.name).join(' | ') : '' }}
                        </template>
                        <template #[`item.share_user_id`]="{ item }">
                            {{ item.share_user ? item.share_user.name : '' }}
                        </template>
                        <template #[`item.action`]="{ item }">
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
                                        @click="confirmRemove(item)"
                                    >
                                    </v-icon>
                                </template>
                            </v-tooltip>
                        </template>

                        <template v-if="expenses.length" #tfoot>
                            <tr class="text-green">
                                <th class="title" />
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">
                                    {{ sumField(expenses, 'value') }}
                                </th>
                                <th class="title text-right">
                                    {{ sumField(expenses, 'share_value') }}
                                </th>
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
                                        />
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
                                v-model="expenseForm.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                :error-messages="expenseForm.errors.description"
                                required
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="expenseForm.value"
                                :label="$t('default.value')"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :error-messages="expenseForm.errors.value"
                                :options="currencyConfig"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="expenseForm.due_date"
                                :label="$t('fix-expense.due-date')"
                                :items="dueDateList"
                                clearable
                                :rules="rules.selectFieldRules"
                                :error-messages="expenseForm.errors.due_date"
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
                                v-model="expenseForm.share_value"
                                :label="$t('default.share-value')"
                                density="comfortable"
                                :rules="shareValueRules"
                                :error-messages="expenseForm.errors.share_value"
                                :options="currencyConfig"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="8">
                            <v-select
                                v-model="expenseForm.share_user_id"
                                :label="$t('default.share-user')"
                                :items="shareUsers"
                                item-title="share_user_name"
                                item-value="share_user_id"
                                clearable
                                :rules="shareUserRules"
                                :error-messages="expenseForm.errors.share_user_id"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-text-field
                                v-model="expenseForm.remarks"
                                :label="$t('default.remarks')"
                                :error-messages="expenseForm.errors.remarks"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="expenseForm.tags"
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
                            />
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn color="error" flat :loading="expenseForm.processing" @click="editDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="expenseForm.processing" type="submit" @click="save">
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
import { useCurrencyConfig, useDaysList, useValidationRules } from '@/composables/useFormConstants.js'
import { useTagSearch } from '@/composables/useTagSearch.js'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { currencyField, sumField } from '@/utils/utils.js'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'FixExpenseIndex', layout: AuthenticatedLayout })

    defineProps({
        expenses: { type: Array },
        shareUsers: { type: Array },
    })

    const { t } = useI18n()

    const { isLoading, editDialog, titleModal, confirmRemove: crudConfirmRemove } = useCrudOperations('/fix-expense')
    const {
        tags: listTags,
        tagSearch: searchTag,
        isSearching: loadingData,
        searchTags: doSearchTags,
        clearTags,
    } = useTagSearch()

    const search = ref(null)
    const percentage = ref(null)
    const txtDescription = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const dueDateList = useDaysList()
    const currencyConfig = useCurrencyConfig()
    const baseRules = useValidationRules()

    function createEmptyExpense() {
        return {
            id: null,
            description: null,
            value: 0,
            due_date: null,
            remarks: null,
            share_value: 0,
            share_user_id: null,
            tags: [],
        }
    }

    const expenseForm = useForm(createEmptyExpense())

    const breadcrumbs = computed(() => [
        { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
        { title: t('menus.fix-expense'), disabled: true },
    ])

    const headers = computed(() => [
        { title: t('default.description'), align: 'start', key: 'description', groupable: false },
        { title: t('fix-expense.due-date'), align: 'center', key: 'due_date' },
        { title: t('default.value'), align: 'end', key: 'value' },
        { title: t('default.share-value'), align: 'end', key: 'share_value' },
        { title: t('default.share-user'), key: 'share_user_id' },
        { title: t('default.remarks'), key: 'remarks' },
        { title: t('default.tags'), key: 'tags' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false, width: 40 },
    ])

    function normalizeCurrencyValue(value) {
        if (value === null || value === undefined || value === '') {
            return 0
        }

        if (typeof value === 'number') {
            return value
        }

        const normalized = String(value)
            .replace(/[^\d,-]/g, '')
            .replace(',', '.')

        return normalized ? Number(normalized) : 0
    }

    const shareValueRules = [
        (value) => {
            if (expenseForm.share_user_id) {
                if (!value) return t('rules.required-text-field')
                if (normalizeCurrencyValue(value) <= 0) return t('rules.required-currency-field')
            }

            return true
        },
    ]

    const shareUserRules = [
        (value) => {
            if (normalizeCurrencyValue(expenseForm.share_value) > 0 && !value) {
                return t('rules.required-text-field')
            }

            return true
        },
    ]

    const rules = {
        ...baseRules,
        shareValueRules,
        shareUserRules,
    }

    const itemsTags = computed(() => listTags.value)

    function calculeShareValue(evt) {
        if (expenseForm.value) {
            expenseForm.share_value = Number(((expenseForm.value * evt.target.value) / 100).toFixed(2))
        }
    }

    async function searchTags(val) {
        doSearchTags(val, expenseForm.tags ?? [])
    }

    function newItem() {
        titleModal.value = t('fix-expense.new-item')
        editDialog.value = true
        percentage.value = null
        clearTags()
        Object.assign(expenseForm, createEmptyExpense())
        expenseForm.clearErrors()
        form.value?.resetValidation()
        nextTick(() => txtDescription.value?.focus())
    }

    function editItem(item) {
        titleModal.value = t('fix-expense.edit-item')
        editDialog.value = true
        percentage.value =
            item.share_value && item.value
                ? Number(((Number(item.share_value) / Number(item.value)) * 100).toFixed(2))
                : null
        Object.assign(expenseForm, createEmptyExpense(), {
            id: item.id,
            description: item.description,
            value: Number(item.value),
            due_date: item.due_date,
            remarks: item.remarks,
            share_value: item.share_value ? Number(item.share_value) : 0,
            share_user_id: item.share_user_id,
            tags: item.tags ?? [],
        })
        clearTags()
        expenseForm.clearErrors()
        form.value?.resetValidation()
        nextTick(() => txtDescription.value?.focus())
    }

    async function save() {
        const validate = await form.value.validate()
        if (validate.valid) {
            if (expenseForm.id) {
                await _update()
            } else {
                await _create()
            }
        }
    }

    async function _create() {
        expenseForm.post('/fix-expense', {
            preserveScroll: true,
            onSuccess: () => {
                editDialog.value = false
                clearTags()
                Object.assign(expenseForm, createEmptyExpense())
                expenseForm.clearErrors()
            },
        })
    }

    async function _update() {
        expenseForm.put('/fix-expense/' + expenseForm.id, {
            preserveScroll: true,
            onSuccess: () => {
                editDialog.value = false
                clearTags()
                expenseForm.clearErrors()
            },
        })
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('fix-expense.item'), t('default.confirm-delete-item'))
    }
</script>
