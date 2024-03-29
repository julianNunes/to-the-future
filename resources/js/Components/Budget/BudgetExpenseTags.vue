<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-expense-tags.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-tabs v-model="tab" bg-color="light-blue" density="comfortable">
                    <v-tab value="one">{{ $t('default.data') }}</v-tab>
                    <v-tab value="two">{{ $t('default.charts') }}</v-tab>
                </v-tabs>
                <v-window v-model="tab">
                    <v-window-item value="one">
                        <v-row dense>
                            <v-col md="12">
                                <v-data-table
                                    :headers="headers"
                                    :items="expenseToTags"
                                    :sort-by="[{ key: 'tag', order: 'asc' }]"
                                    :search="search"
                                    :loading="isLoading"
                                    :loading-text="$t('default.loading-text-table')"
                                    class="elevation-3"
                                    density="compact"
                                    :total-items="expenseToTags.length"
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
</template>

<script setup>
import { currencyField } from '../../utils/utils.js'
import BarChart from '../../Components/BarChart.vue'
</script>

<script>
export default {
    name: 'BudgetExpenseTags',

    components: {
        BarChart,
    },

    props: {
        expenseToTags: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [
                { title: 'Tag', align: 'start', key: 'tag' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
            ],
            search: null,
            isLoading: false,
            panel: 1,
            tab: null,
        }
    },

    computed: {
        chartOptions() {
            return {
                chart: {
                    id: 'basic-bar',
                    heigth: 10,
                },
                colors: ['#FB8C00'],
                xaxis: {
                    categories: this.expenseToTags?.length ? this.expenseToTags.map((x) => x.tag) : [],
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
                    text: this.$t('default.no-data-text'),
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                },
            }
        },
        chartSeries() {
            if (this.expenseToTags?.length) {
                return [
                    {
                        name: 'Despesas',
                        data: this.expenseToTags.map((x) => x.value),
                    },
                ]
            }

            return []
        },
    },

    async created() {},

    async mounted() {},

    methods: {},
}
</script>
