<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-goal.title') }}</span>
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
                                    :items="goals"
                                    :sort-by="[{ key: 'created_at', order: 'asc' }]"
                                    :search="search"
                                    :loading="isLoading"
                                    :loading-text="$t('default.loading-text-table')"
                                    class="elevation-3"
                                    density="compact"
                                    :total-items="goals.length"
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
                                    <template #[`item.group`]="{ item }">{{
                                        item.group ? convertGroup(item.group) : null
                                    }}</template>
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
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescription"
                                v-model="goal.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="4" md="6">
                            <vuetify-money
                                v-model="goal.value"
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
                                @update:search="searchTags"
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
                            <v-checkbox v-model="goal.count_share" :label="$t('budget-goal.count-share')"></v-checkbox>
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
</template>

<script setup>
    import { currencyField } from '@/utils/utils.js'
    import { router } from '@inertiajs/vue3'
    import { computed, defineAsyncComponent, nextTick, ref } from 'vue'
    import { useI18n } from 'vue-i18n'

    import { useCrudOperations } from '@/composables/useCrudOperations.js'
    import { useValidationRules } from '@/composables/useFormConstants.js'
    import { useTagSearch } from '@/composables/useTagSearch.js'

    const BarChart = defineAsyncComponent(() => import('@/Components/BarChart.vue'))

    defineOptions({ name: 'BudgetGoal' })

    const componentProps = defineProps({
        budgetId: { type: Number },
        goals: {
            type: Array,
            default: () => [],
        },
        goalsCharts: { type: Array },
        viewOnly: { type: Boolean, default: false },
    })

    const { t } = useI18n()
    const rules = useValidationRules()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/budget-goal')
    const { tags: listTags, searchTags: doSearchTags } = useTagSearch()

    const search = ref(null)
    const deleteId = ref(null)
    const panel = ref(1)
    const tab = ref(null)
    const searchTag = ref('')
    const loadingData = ref(false)

    const goal = ref({
        id: null,
        description: null,
        value: 0,
        group: null,
        count_share: false,
        budget_id: null,
        tags: [],
    })

    // Refs for template
    const txtDescription = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const groupList = [
        { name: t('default.in-installments'), value: 'PORTION' },
        { name: t('default.monthly'), value: 'MONTHLY' },
        { name: t('default.week-1'), value: 'WEEK_1' },
        { name: t('default.week-2'), value: 'WEEK_2' },
        { name: t('default.week-3'), value: 'WEEK_3' },
        { name: t('default.week-4'), value: 'WEEK_4' },
    ]

    const headers = computed(() => {
        let hdrs = [
            { title: t('default.description'), align: 'start', key: 'description', groupable: false },
            { title: t('default.value'), align: 'end', key: 'value' },
            { title: t('default.group'), align: 'end', key: 'group' },
            { title: t('budget-goal.count-share'), key: 'count_share' },
            { title: t('default.tag'), key: 'tags' },
        ]

        if (!componentProps.viewOnly) {
            hdrs.push({ title: t('default.action'), align: 'center', key: 'action', sortable: false })
        }
        return hdrs
    })

    const itemsTags = computed(() => listTags.value)

    const chartOptions = computed(() => {
        return {
            chart: {
                id: 'basic-bar',
            },
            colors: ['#43A047', '#FB8C00'],
            xaxis: {
                categories: componentProps.goalsCharts?.length
                    ? componentProps.goalsCharts.map((x) => x.description)
                    : [],
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return currencyField(value)
                    },
                },
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent'],
            },
            responsive: [
                {
                    breakpoint: 1280,
                },
            ],
            fill: {
                opacity: 1,
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        position: 'top',
                    },
                    columnWidth: '55%',
                    endingShape: 'rounded',
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
            legend: {
                show: true,
                position: 'bottom',
                onItemHover: {
                    highlightDataSeries: true,
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
        if (componentProps.goalsCharts?.length) {
            return [
                {
                    name: t('budget-goal.expenses'),
                    data: componentProps.goalsCharts.map((x) => x.value),
                },
                {
                    name: t('budget-goal.limit'),
                    data: componentProps.goalsCharts.map((x) => x.total),
                },
            ]
        }
        return []
    })

    function convertGroup(group) {
        return groupList.find((x) => x.value === group)?.name || group
    }

    async function searchTags(val) {
        loadingData.value = true
        const existing = []
        if (goal.value.tags && goal.value.tags.name) {
            existing.push(goal.value.tags)
        }
        doSearchTags(val, existing)
        setTimeout(() => {
            loadingData.value = false
        }, 300)
    }

    function newItem() {
        titleModal.value = t('budget-goal.new-item')
        editDialog.value = true
        goal.value = {
            id: null,
            description: null,
            value: 0,
            group: null,
            count_share: false,
            budget_id: componentProps.budgetId,
            tags: [],
        }
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('budget-goal.edit-item')
        editDialog.value = true
        goal.value = {
            id: item.id,
            description: item.description,
            value: Number(item.value),
            group: item.group,
            count_share: item.count_share,
            tags: item.tags && item.tags.length ? item.tags[0] : [],
            budget_id: componentProps.budgetId,
        }
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    async function save() {
        let validate = await form.value.validate()
        if (validate.valid) {
            if (goal.value.id) {
                await updateData()
            } else {
                await createData()
            }
        }
    }

    async function createData() {
        isLoading.value = true
        router.post(
            '/budget-goal',
            {
                description: goal.value.description,
                value: Number(goal.value.value),
                group: goal.value.group,
                count_share: goal.value.count_share,
                tags: [goal.value.tags],
                budget_id: componentProps.budgetId,
            },
            {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            }
        )
    }

    async function updateData() {
        isLoading.value = true
        router.put(
            '/budget-goal/' + goal.value.id,
            {
                id: goal.value.id,
                description: goal.value.description,
                value: Number(goal.value.value),
                group: goal.value.group,
                count_share: goal.value.count_share,
                tags: [goal.value.tags],
                budget_id: componentProps.budgetId,
            },
            {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            }
        )
    }

    async function confirmRemove(item) {
        deleteId.value = item.id
        if (await confirm.value.open(t('budget-goal.item'), t('default.confirm-delete-item'))) {
            removeData()
        }
    }

    function removeData() {
        isLoading.value = true
        router.delete(`/budget-goal/${deleteId.value}`, {
            preserveScroll: true,
            onSuccess: () => {},
            onError: () => {
                isLoading.value = false
            },
            onFinish: () => {
                isLoading.value = false
            },
        })
    }
</script>
