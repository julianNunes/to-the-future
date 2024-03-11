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
                            <v-text-field
                                v-model="goal.value"
                                type="number"
                                :label="$t('budget-goal.limit-expenses')"
                                min="0"
                                required
                                :rules="rules.currencyFieldRules"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-autocomplete
                                v-model="goal.tags"
                                v-model:search="search_tag"
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

    <!-- Dialog delete -->
    <v-row v-if="removeDialog" justify="center">
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
</template>

<script setup>
import { currencyField } from '../../utils/utils.js'
</script>

<script>
import BarChart from '../../Components/BarChart.vue'

export default {
    name: 'BudgetGoal',
    props: {
        budgetId: {
            type: Number,
        },
        goals: {
            type: Array,
            default: new Array(),
        },
        goalsCharts: {
            type: Array,
        },
        viewOnly: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.group'), align: 'end', key: 'group' },
                { title: this.$t('budget-goal.count-share'), key: 'count_share' },
                { title: this.$t('default.tag'), key: 'tags' },
                { title: this.$t('default.action'), align: 'center', key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                currencyFieldRules: [
                    (value) => {
                        if (!value) return this.$t('rules.required-text-field')
                        if (Number(value) <= 0) return this.$t('rules.required-currency-field')

                        return true
                    },
                ],
            },
            search: null,
            editDialog: false,
            removeDialog: false,
            isLoading: false,
            deleteId: null,
            panel: 1,
            tab: null,
            goal: {
                id: null,
                description: null,
                value: 0,
                group: null,
                count_share: false,
                budget_id: null,
                tags: [],
            },
            groupList: [
                {
                    name: this.$t('default.in-installments'),
                    value: 'PORTION',
                },
                {
                    name: this.$t('default.monthly'),
                    value: 'MONTHLY',
                },
                {
                    name: this.$t('default.week-1'),
                    value: 'WEEK_1',
                },
                {
                    name: this.$t('default.week-2'),
                    value: 'WEEK_2',
                },
                {
                    name: this.$t('default.week-3'),
                    value: 'WEEK_3',
                },
                {
                    name: this.$t('default.week-4'),
                    value: 'WEEK_4',
                },
            ],
            listTags: [],
            searchFieldsData: [],
            search_tag: '',
            loadingData: false,
        }
    },

    computed: {
        itemsTags() {
            return this.listTags
        },
        chartOptions() {
            return {
                chart: {
                    id: 'basic-bar',
                },
                colors: ['#43A047', '#FB8C00'],
                xaxis: {
                    categories: this.goalsCharts?.length ? this.goalsCharts.map((x) => x.description) : [],
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
                    text: this.$t('default.no-data-text'),
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                },
            }
        },
        chartSeries() {
            if (this.goalsCharts?.length) {
                return [
                    {
                        name: this.$t('budget-goal.expenses'),
                        data: this.goalsCharts.map((x) => x.value),
                    },
                    {
                        name: this.$t('budget-goal.limit'),
                        data: this.goalsCharts.map((x) => x.total),
                    },
                ]
            }

            return []
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        convertGroup(group) {
            return this.groupList.find((x) => x.value === group).name
        },

        async searchTags(val) {
            if (this.loadingData) return

            if (!val || val.length <= 1) {
                this.listTags = []
                clearTimeout(this.timeOut)
                return
            }

            if (this.goal.tags && this.goal.tags.length > 0 && this.goal.tags.find((x) => x.name == val)) {
                return
            }

            clearTimeout(this.timeOut)
            this.timeOut = setTimeout(async () => {
                this.loadingData = true
                let searchFieldsData = []
                await window.axios
                    .get('/tag/search/' + val)
                    .then(function (response) {
                        if (response.data && response.data.length > 0) {
                            searchFieldsData = response.data
                        }

                        if (
                            (searchFieldsData &&
                                searchFieldsData.length > 0 &&
                                !searchFieldsData.find((x) => x.name == val.toUpperCase())) ||
                            !searchFieldsData ||
                            searchFieldsData.length == 0
                        ) {
                            searchFieldsData.unshift({ name: val.toUpperCase() })
                        }
                    })
                    .catch(function (error) {
                        console.log('error', error)
                    })

                this.listTags = searchFieldsData
                this.loadingData = false
            }, 300)
        },

        newItem() {
            this.titleModal = this.$t('budget-goal.new-item')
            this.editDialog = true
            this.goal = {
                id: null,
                description: null,
                value: 0,
                group: null,
                count_share: false,
                budget_id: this.budgetId,
                tags: [],
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('budget-goal.edit-item')
            this.editDialog = true
            this.goal = {
                id: item.id,
                description: item.description,
                value: Number(item.value),
                group: item.group,
                count_share: item.count_share,
                tags: item.tags,
                budget_id: this.budgetId,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        closeItem() {
            this.editDialog = false
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.goal.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/budget-goal',
                {
                    description: this.goal.description,
                    value: Number(this.goal.value),
                    group: this.goal.group,
                    count_share: this.goal.count_share,
                    tags: [this.goal.tags],
                    budget_id: this.budgetId,
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
                '/budget-goal/' + this.goal.id,
                {
                    id: this.goal.id,
                    description: this.goal.description,
                    value: Number(this.goal.value),
                    group: this.goal.group,
                    count_share: this.goal.count_share,
                    tags: [this.goal.tags],
                    budget_id: this.budgetId,
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
            this.$inertia.delete(`/budget-goal/${this.deleteId}`, {
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
