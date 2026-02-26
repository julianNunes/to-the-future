<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-expense-tag-options.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-tabs v-model="tab" bg-color="light-blue" density="comfortable">
                    <v-tab value="one">{{ $t('default.data') }}</v-tab>
                    <v-tab value="two">{{ $t('default.charts') }}</v-tab>
                </v-tabs>
                <v-window v-model="tab">
                    <v-window-item value="one">
                        <v-row dense class="mt-1">
                            <v-col v-if="!viewOnly" md="12">
                                <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                            </v-col>
                            <v-col md="12">
                                <v-data-table
                                    :headers="headers"
                                    :items="tagsOptions"
                                    :sort-by="[{ key: 'tag', order: 'asc' }]"
                                    :search="search"
                                    :loading="isLoading"
                                    :loading-text="$t('default.loading-text-table')"
                                    class="elevation-3"
                                    density="compact"
                                    :total-items="tagsOptions.length"
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
                                    <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                                    <template #[`item.tags`]="{ item }">{{
                                        item.tags.length ? item.tags.map((x) => x.name).join(' | ') : ''
                                    }}</template>
                                    <template #[`item.count_share`]="{ item }">{{
                                        item.count_share ? $t('default.yes') : $t('default.no')
                                    }}</template>
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
                    </v-window-item>
                    <v-window-item value="two">
                        <v-row dense>
                            <v-col md="12">
                                <BarChart :options="chartOptions" :series="chartSeries" />
                            </v-col>
                        </v-row>
                    </v-window-item>
                </v-window>
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
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                v-model="goal.tags"
                                v-model:search="searchTag"
                                :label="$t('default.tags')"
                                :items="itemsTags"
                                :loading="loadingData"
                                item-title="name"
                                item-value="name"
                                :rules="rules.textFieldRules"
                                required
                                clearable
                                return-object
                                :closable-chips="true"
                                :clear-on-select="true"
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                @update:search="handleSearchTags"
                                @update:model-value="searchTag = ''"
                            ></v-autocomplete>
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="goal.group"
                                :label="$t('default.group')"
                                :items="groupList"
                                item-title="name"
                                item-value="value"
                                clearable
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col md="6">
                            <v-checkbox
                                v-model="goal.count_share"
                                :label="$t('budget-expense-tag-options.count-share')"
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
                <v-btn color="primary" flat :loading="isLoading" type="submit" @click="handleSave">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import { ref, computed } from 'vue'
    import { useI18n } from 'vue-i18n'
    import BarChart from '@/Components/BarChart.vue'
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
    import { currencyField } from '@/utils/utils.js'
    import { useValidationRules, useGroupList } from '@/composables/useFormConstants.js'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'
    import { useTagSearch } from '@/composables/useTagSearch.js'

    const componentProps = defineProps({
        budgetId: { type: Number },
        tagsOptions: { type: Array, default: () => [] },
        tagsOptionsChats: { type: Array, default: () => [] },
        viewOnly: { type: Boolean, default: false },
    })

    const { t } = useI18n()
    const rules = useValidationRules()
    const groupList = useGroupList()

    const {
        isLoading,
        editDialog,
        titleModal,
        confirmRemove: handleCrudConfirmRemove,
        save,
    } = useCrudOperations('/budget-expense-tag-option')

    const { tags: itemsTags, searchTags: doSearchTags } = useTagSearch()

    const headers = [
        { title: t('default.group'), align: 'end', key: 'group' },
        { title: t('default.value'), align: 'end', key: 'value' },
        { title: t('budget-expense-tag-options.count-share'), key: 'count_share' },
        { title: t('default.tag'), key: 'tags' },
        { title: t('default.action'), align: 'center', key: 'action', sortable: false },
    ]

    const search = ref(null)
    const panel = ref(1)
    const tab = ref(null)
    const goal = ref({
        id: null,
        description: null,
        value: 0,
        group: null,
        count_share: false,
        budget_id: componentProps.budgetId,
        tags: null,
    })
    const searchTag = ref('')
    const loadingData = ref(false)

    const form = ref(null)
    const confirm = ref(null)

    const handleSearchTags = (val) => {
        loadingData.value = true
        const existing = goal.value.tags ? [goal.value.tags] : []
        doSearchTags(val, existing)
        setTimeout(() => {
            loadingData.value = false
        }, 350)
    }

    const newItem = () => {
        titleModal.value = t('budget-goal.new-item')
        editDialog.value = true
        goal.value = {
            id: null,
            description: null,
            value: 0,
            group: null,
            count_share: false,
            budget_id: componentProps.budgetId,
            tags: null,
        }
    }

    const editItem = (item) => {
        titleModal.value = t('budget-goal.edit-item')
        editDialog.value = true
        goal.value = {
            id: item.id,
            description: item.description,
            value: Number(item.value),
            group: item.group,
            count_share: Boolean(item.count_share),
            tags: Array.isArray(item.tags) && item.tags.length > 0 ? item.tags[0] : item.tags,
            budget_id: componentProps.budgetId,
        }
    }

    const handleSave = () => {
        const data = {
            id: goal.value.id,
            description: goal.value.description,
            value: Number(goal.value.value),
            group: goal.value.group,
            count_share: goal.value.count_share,
            tags: goal.value.tags ? [goal.value.tags] : [],
            budget_id: componentProps.budgetId,
        }
        save(form.value, data)
    }

    const confirmRemove = (item) => {
        handleCrudConfirmRemove(item, confirm.value, t('budget-goal.item'), t('default.confirm-delete-item'))
    }

    const chartOptions = computed(() => {
        return {
            chart: {
                id: 'basic-bar',
                heigth: 10,
            },
            colors: ['#FB8C00'],
            xaxis: {
                categories: componentProps.tagsOptionsChats?.length
                    ? componentProps.tagsOptionsChats.map((x) => x.description)
                    : [],
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return currencyField(value)
                    },
                },
            },
            responsive: [
                {
                    breakpoint: 1280,
                },
            ],
            plotOptions: {
                bar: {
                    dataLabels: {
                        position: 'top',
                    },
                },
            },
            dataLabels: {
                enabled: true,
                style: {
                    colors: ['#333'],
                },
                offsetY: -20,
                formatter: function (val) {
                    return currencyField(val)
                },
            },
            noData: {
                text: t('default.no-data-text'),
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
            },
        }
    })

    const chartSeries = computed(() => {
        if (componentProps.tagsOptionsChats?.length) {
            return [
                {
                    name: 'Despesas',
                    data: componentProps.tagsOptionsChats.map((x) => x.value),
                },
            ]
        }
        return []
    })
</script>
