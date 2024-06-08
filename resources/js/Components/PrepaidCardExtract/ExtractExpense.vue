<template>
    <!-- Dados do cartão -->
    <v-expansion-panels v-model="panel" :readonly="!titleCard" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <template #default="{ expanded }">
                    <v-row no-gutters>
                        <span class="text-h6">
                            {{ $t('prepaid-card-extract.data-extract') }}
                            {{ !expanded && titleCard ? ' - ' + prepaidCardName : '' }}
                        </span>
                    </v-row>
                </template>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-4">
                <v-row dense>
                    <v-col cols="12" sm="12" md="2">
                        <v-text-field
                            ref="txtName"
                            v-model="prepaidCardName"
                            :label="$t('prepaid-card.item')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-text-field
                            v-model="extractYearMonth"
                            :label="$t('prepaid-card-extract.year-month')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="extractCreditDate"
                            :label="$t('prepaid-card-extract.credit-date')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="2">
                        <v-text-field
                            v-model="extractCredit"
                            :label="$t('prepaid-card-extract.credit')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" :disabled="viewOnly" @click="newItem">{{ $t('default.new') }}</v-btn>
                        <v-btn
                            color="info"
                            class="ml-1"
                            href="/storage/template/template-prepaid-card.xlsx"
                            download
                            :disabled="viewOnly"
                        >
                            {{ $t('credit-card-invoice.download-template') }}
                        </v-btn>
                        <v-btn color="info" class="ml-1" :disabled="viewOnly" @click="clickImportFile">{{
                            $t('credit-card-invoice.import-excel')
                        }}</v-btn>
                        <input ref="fileInput" type="file" class="d-none" accept="xlxs/*" @change="selectFile" />
                        <v-btn v-if="viewOnly" color="warning" class="ml-1" @click="updateInvoice(false)">
                            {{ $t('credit-card-invoice.open-invoice') }}
                        </v-btn>
                    </v-col>
                </v-row>
                <v-row dense>
                    <v-col md="12">
                        <v-data-table
                            :group-by="[{ key: 'group', order: 'asc' }]"
                            :headers="headers"
                            :items="extract.expenses"
                            :sort-by="[{ key: 'created_at', order: 'asc' }]"
                            :search="search"
                            :loading="isLoading"
                            :loading-text="$t('default.loading-text-table')"
                            class="elevation-3"
                            density="compact"
                            :total-items="extract.expenses"
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
                            <template #[`item.share_value`]="{ item }">{{ currencyField(item.share_value) }}</template>
                            <template #[`item.date`]="{ item }">{{ moment(item.date).format('DD/MM/YYYY') }}</template>
                            <template #[`item.group`]="{ item }">{{ convertGroup(item.group) }}</template>
                            <template #[`item.tags`]="{ item }">{{
                                item.tags.length ? item.tags.map((x) => x.name).join(' | ') : ''
                            }}</template>
                            <template #[`item.share_user_id`]="{ item }">{{
                                item.share_user ? item.share_user.name : ''
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
                                            :disabled="viewOnly"
                                            @click="editItem(item)"
                                        >
                                        </v-icon>
                                    </template>
                                </v-tooltip>
                                <v-tooltip :text="$t('default.delete')" location="top">
                                    <template #activator="{ props }">
                                        <v-icon
                                            v-bind="props"
                                            class="ml-2"
                                            color="error"
                                            icon="mdi-delete"
                                            size="small"
                                            :disabled="viewOnly"
                                            @click="confirmRemove(item)"
                                        >
                                        </v-icon>
                                    </template>
                                </v-tooltip>
                            </template>

                            <template #group-header="{ item, toggleGroup, isGroupOpen }">
                                <tr>
                                    <th class="title" style="width: auto">
                                        <VBtn
                                            size="small"
                                            variant="text"
                                            :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                            @click="toggleGroup(item)"
                                        ></VBtn>
                                        {{ convertGroup(item.value) }}
                                    </th>
                                    <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                                    <th class="title text-right">
                                        {{ sumGroup(extract.expenses, item.key, item.value, 'value') }}
                                    </th>
                                    <th class="title text-right">
                                        {{ sumGroup(extract.expenses, item.key, item.value, 'share_value') }}
                                    </th>
                                    <th :colspan="6"></th>
                                </tr>
                            </template>

                            <template v-if="extract.expenses.length" #tfoot>
                                <tr class="green--text">
                                    <th class="title"></th>
                                    <th colspan="2" class="title font-weight-bold text-right">Total</th>
                                    <th class="title text-right">{{ sumField(extract.expenses, 'value') }}</th>
                                    <th class="title text-right">{{ sumField(extract.expenses, 'share_value') }}</th>
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
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels>

    <!-- Dialog Criacao/Edicao -->
    <v-dialog v-model="editDialog" persistent width="800" class="ma-4">
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
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-date-input
                                ref="inputDate"
                                v-model="expense.date"
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
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.value"
                                :label="$t('default.value')"
                                type="number"
                                min="0"
                                :rules="rules.currencyFieldRules"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="expense.group"
                                :label="$t('default.group')"
                                :items="groupList"
                                item-title="name"
                                item-value="value"
                                clearable
                                :rules="rules.textFieldRules"
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
                            <v-text-field
                                v-model="expense.share_value"
                                :label="$t('default.share-value')"
                                type="number"
                                min="0"
                                :rules="[
                                    (value) => {
                                        if (expense.share_user_id) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="expense.share_user_id"
                                :label="$t('default.share-user')"
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
                                return-object
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                @update:search="searchTags"
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
import { useToast } from 'vue-toastification'
import { sumField, sumGroup, currencyField } from '../../utils/utils.js'
import readXlsxFile from 'read-excel-file'
</script>

<script>
export default {
    name: 'InvoiceExpenses',
    props: {
        extract: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
        budgetWeeks: {
            type: Array,
        },
        titleCard: {
            type: Boolean,
            default: false,
        },
        yearMonth: {
            type: String,
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
                { title: this.$t('default.date'), key: 'date' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), align: 'center', key: 'action', width: '100', sortable: false },
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
            groupList: [
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
            toast: null,
            panel: this.titleCard ? 1 : 0,
            search: null,
            timeOut: null,
            searchTag: '',
            editDialog: false,
            isLoading: false,
            loadingData: false,
            deleteId: null,
            editedIndex: -1,
            listTags: [],
            searchFieldsData: [],
            percentage: null,
            expense: {
                id: null,
                description: null,
                date: null,
                value: null,
                group: null,
                remarks: null,
                share_value: null,
                share_user_id: null,
                extract_id: null,
                tags: [],
            },
        }
    },

    computed: {
        itemsTags() {
            return this.listTags
        },
        prepaidCardName() {
            return this.extract.prepaid_card.name
        },
        itemsExpenses() {
            return this.extract.expenses
        },
        extractCreditDate() {
            return moment(this.extract.credit_date).format('DD/MM/YYYY')
        },
        extractCredit() {
            return currencyField(this.extract.credit)
        },
        extractYearMonth() {
            return this.extract.year_month
        },
        monthToDateInput() {
            return moment(this.yearMonth + '-01').month()
        },
        yearToDateInput() {
            return moment(this.yearMonth + '-01').year()
        },
    },

    watch: {},

    async created() {},

    async mounted() {
        this.toast = useToast()
    },

    methods: {
        calculeShareValue(evt) {
            if (this.expense.value) {
                this.expense.share_value = parseFloat((this.expense.value * evt.target.value) / 100).toFixed(2)
            }
        },

        convertGroup(group) {
            if (this.budgetWeeks?.length && this.budgetWeeks.find((x) => x.value === group)) {
                return (
                    this.groupList.find((x) => x.value === group).name +
                    ' (' +
                    this.budgetWeeks.find((x) => x.value === group).text +
                    ')'
                )
            }

            return this.groupList.find((x) => x.value === group).name
        },

        async searchTags(val) {
            if (this.loadingData) return

            if (!val || val.length <= 1) {
                this.listTags = []
                clearTimeout(this.timeOut)
                return
            }

            if (this.expense.tags && this.expense.tags.length > 0 && this.expense.tags.find((x) => x.name == val)) {
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
            this.titleModal = this.$t('prepaid-card-extract-expense.new-item')
            this.editDialog = true
            this.expense = {
                id: null,
                description: null,
                date: moment(this.yearMonth + '-01').toDate(),
                value: null,
                group: null,
                remarks: null,
                share_value: null,
                extract_id: null,
                share_user_id: null,
                tags: [],
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('prepaid-card-extract-expense.edit-item')
            this.editDialog = true
            this.expense = {
                id: item.id,
                description: item.description,
                date: moment(item.date).toDate(),
                value: item.value,
                group: item.group,
                remarks: item.remarks,
                share_value: item.share_value,
                extract_id: item.extract_id,
                share_user_id: item.share_user_id,
                tags: item.tags,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.expense.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/prepaid-card/extract/expense',
                {
                    prepaid_card_id: this.extract.prepaid_card.id,
                    extract_id: this.extract.id,
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    tags: this.expense.tags,
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
                '/prepaid-card/extract/expense/' + this.expense.id,
                {
                    prepaid_card_id: this.extract.prepaid_card.id,
                    extract_id: this.extract.id,
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    tags: this.expense.tags,
                    divisions: this.expense.divisions,
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
            if (
                await this.$refs.confirm.open(
                    this.$t('prepaid-card-extract-expense.item'),
                    this.$t('default.confirm-delete-item')
                )
            ) {
                this.remove()
            }
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete('/prepaid-card/extract/expense/' + this.deleteId, {
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

        // Referentes ao arquivos
        downloadTemplate() {
            fetch('/prepaid-card/extract/download-template')
                .then((res) => res.blob())
                .then((blob) => {
                    const file = window.URL.createObjectURL(blob)
                    window.location.assign(file)
                })
        },

        clickImportFile() {
            this.$refs.fileInput.click()
        },

        async selectFile(event) {
            const file = event.target.files[0]
            if (file) {
                let data_excel = []

                await readXlsxFile(file).then(async (rows) => {
                    rows.forEach((element, key) => {
                        if (key > 0) {
                            data_excel.push({
                                date: moment(element[0]).format('YYYY-MM-DD'),
                                description: element[1],
                                value: element[2],
                                share_value: element[3],
                                remarks: element[4],
                                group: this.convertGroupToExcel(element[5]),
                                share_user_id: element[6],
                                tags: element[7]
                                    ? element[7].split(',').map((x) => {
                                          return { name: x.toUpperCase() }
                                      })
                                    : null,
                            })
                        }
                    })
                })

                if (this.validateImportExcel(data_excel)) {
                    await this.importExcel(data_excel)
                }
            }
        },

        convertGroupToExcel(group) {
            if (group) {
                if (group === 'SEMANA 1') return 'WEEK_1'
                else if (group === 'SEMANA 2') return 'WEEK_2'
                else if (group === 'SEMANA 3') return 'WEEK_3'
                else if (group === 'SEMANA 4') return 'WEEK_4'
            }
            return ''
        },

        validateImportExcel(data_excel) {
            for (const [key, element] of Object.entries(data_excel)) {
                // Description
                if (!element.description) {
                    this.toast.error(this.$tc('prepaid-card-extract-expense.excel.description', { key: key + 1 }))
                    return false
                }

                // Date
                if (!element.date) {
                    this.toast.error(this.$tc('prepaid-card-extract-expense.excel.date', { key: key + 1 }))
                    return false
                }

                // Value
                if (!element.value) {
                    this.toast.error(this.$tc('prepaid-card-extract-expense.excel.date', { key: key + 1 }))
                    return false
                }

                // group
                if (!element.group) {
                    this.toast.error(this.$tc('prepaid-card-extract-expense.excel.group', { key: key + 1 }))
                    return false
                }

                // share_value and share_user_id
                if (element.share_value || element.share_user_id) {
                    if (!element.share_value) {
                        this.toast.error(this.$tc('prepaid-card-extract-expense.excel.share-value', { key: key + 1 }))
                        return false
                    }

                    if (!element.share_user_id) {
                        this.toast.error(this.$tc('prepaid-card-extract-expense.excel.share-user', { key: key + 1 }))
                        return false
                    }

                    if (!this.shareUsers.find((x) => x.share_user_id == element.share_user_id)) {
                        this.toast.error(this.$tc('prepaid-card-extract-expense.excel.share-user', { key: key + 1 }))
                        return false
                    }
                }
            }

            return true
        },

        async importExcel(data_excel) {
            this.isLoading = true
            this.$inertia.post(
                '/prepaid-card/extract/expense/import-excel',
                {
                    data: data_excel,
                    extract_id: this.extract.id,
                },
                {
                    onSuccess: () => {},
                    onFinish: () => {
                        this.isLoading = false
                    },
                }
            )
        },
    },
}
</script>
