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
                                <tr class="green--text">
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
import moment from 'moment'
import { sumField, currencyField, formatDate, reverseFormatNumber } from '../../utils/utils.js'
</script>

<script>
export default {
    name: 'BudgetIncome',
    props: {
        budgetId: {
            type: Number,
        },
        yearMonth: {
            type: String,
        },
        incomes: {
            type: Array,
            default: new Array(),
        },
        viewOnly: {
            type: Boolean,
        },
    },

    data() {
        return {
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                currencyFieldRules: [
                    (value) => {
                        value = reverseFormatNumber(value)
                        if (!value) return this.$t('rules.required-text-field')
                        if (Number(value) <= 0) return this.$t('rules.required-currency-field')

                        return true
                    },
                ],
            },
            search: null,
            editDialog: false,
            isLoading: false,
            deleteId: null,
            panel: 1,
            income: {
                id: null,
                description: null,
                value: 0,
                date: null,
                remarks: null,
                budget_id: null,
                tags: [],
            },
            listTags: [],
            searchFieldsData: [],
            searchTag: '',
            loadingData: false,
        }
    },

    computed: {
        itemsTags() {
            return this.listTags
        },
        headers() {
            let headers = [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('budget-income.date'), align: 'center', key: 'date' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
            ]

            if (!this.viewOnly) {
                headers.push({
                    title: this.$t('default.action'),
                    align: 'end',
                    key: 'action',
                    sortable: false,
                    width: 40,
                })
            }

            return headers
        },
        monthToDateInput() {
            return moment(this.yearMonth + '-01').month()
        },
        yearToDateInput() {
            return moment(this.yearMonth + '-01').year()
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        async searchTags(val) {
            if (this.loadingData) return

            if (!val || val.length <= 1) {
                this.listTags = []
                clearTimeout(this.timeOut)
                return
            }

            if (this.income.tags && this.income.tags.length > 0 && this.income.tags.find((x) => x.name == val)) {
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

        itemRowFont(row) {
            return { class: !row.item.id ? 'font-weight-bold' : '' }
        },

        newItem() {
            this.titleModal = this.$t('budget-income.new-item')
            this.editDialog = true
            this.income = {
                id: null,
                description: null,
                value: 0,
                date: moment(this.yearMonth + '-01', 'YYYY-MM-DD'),
                remarks: null,
                tags: [],
                budget_id: this.budgetId,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('budget-income.edit-item')
            this.editDialog = true

            this.income = {
                id: item.id,
                description: item.description,
                value: Number(item.value),
                date: moment(item.date, 'YYYY-MM-DD'),
                remarks: item.remarks,
                tags: item.tags,
                budget_id: item.budget_id,
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
                if (this.income.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/budget-income',
                {
                    description: this.income.description,
                    date: this.income.date.format('YYYY-MM-DD'),
                    value: this.income.value,
                    remarks: this.income.remarks,
                    budget_id: this.income.budget_id,
                    tags: this.income.tags,
                },
                {
                    onSuccess: () => {
                        this.editDialog = false
                    },
                    onFinish: () => {
                        this.isLoading = false
                    },
                    preserveScroll: true,
                }
            )
        },

        async update() {
            this.isLoading = true
            this.$inertia.put(
                '/budget-income/' + this.income.id,
                {
                    description: this.income.description,
                    value: this.income.value,
                    date: this.income.date.format('YYYY-MM-DD'),
                    remarks: this.income.remarks,
                    tags: this.income.tags,
                },
                {
                    onSuccess: () => {
                        this.editDialog = false
                    },
                    onFinish: () => {
                        this.isLoading = false
                    },
                    preserveScroll: true,
                }
            )
        },

        async confirmRemove(item) {
            this.deleteId = item.id
            if (await this.$refs.confirm.open(this.$t('budget-income.item'), this.$t('default.confirm-delete-item'))) {
                this.remove()
            }
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/budget-income/${this.deleteId}`, {
                onSuccess: () => {},
                onError: () => {
                    this.isLoading = false
                },
                onFinish: () => {
                    this.isLoading = false
                },
                preserveScroll: true,
            })
        },
    },
}
</script>
