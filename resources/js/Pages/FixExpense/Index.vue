<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('fix-expense.title') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-card class="pa-4">
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
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
                        fixed-header
                    >
                        <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                        <template #[`item.share_value`]="{ item }">{{ currencyField(item.share_value) }}</template>
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

                        <template v-if="expenses.length" #tfoot>
                            <tr class="green--text">
                                <th class="title"></th>
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumField(expenses, 'value') }}</th>
                                <th class="title text-right">{{ sumField(expenses, 'share_value') }}</th>
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
                                    v-model="expense.description"
                                    :label="$t('default.description')"
                                    :rules="rules.textFieldRules"
                                    required
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-text-field
                                    v-model="expense.value"
                                    type="number"
                                    :label="$t('default.value')"
                                    min="0"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="expense.due_date"
                                    :label="$t('fix-expense.due-date')"
                                    :items="dueDateList"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
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
                            <v-col cols="12" sm="6" md="8">
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
                                    v-model:search="search_tag"
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

        <!-- Dialog delete -->
        <v-row justify="center">
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
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
</script>

<script>
import { sumField, currencyField } from '../../utils/utils.js'

export default {
    name: 'FixExpenseIndex',
    props: {
        expenses: {
            type: Array,
        },
        shareUsers: {
            type: Array,
        },
    },

    data() {
        return {
            breadcrumbs: [
                {
                    title: this.$t('menus.dashboard'),
                    disabled: false,
                    href: '/dashboard',
                },
                {
                    title: this.$t('menus.fix-expense'),
                    disabled: true,
                },
            ],
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('fix-expense.due-date'), align: 'center', key: 'due_date' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), align: 'end', key: 'action', sortable: false, width: 40 },
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
            modalEntryDateStart: false,
            expense: {
                id: null,
                description: null,
                value: 0,
                due_date: null,
                remarks: null,
                share_value: 0,
                share_user_id: null,
                tags: [],
            },
            dueDateList: [
                '01',
                '02',
                '03',
                '04',
                '05',
                '06',
                '06',
                '07',
                '08',
                '09',
                '10',
                '11',
                '12',
                '13',
                '14',
                '15',
                '16',
                '17',
                '18',
                '19',
                '20',
                '21',
                '22',
                '23',
                '24',
                '25',
                '26',
                '27',
                '28',
                '29',
                '30',
                '31',
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
            this.titleModal = this.$t('fix-expense.new-item')
            this.editDialog = true
            this.expense = {
                id: null,
                description: null,
                value: 0,
                due_date: null,
                remarks: null,
                share_value: 0,
                share_user_id: null,
                tags: [],
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('fix-expense.edit-item')
            this.editDialog = true
            this.expense = {
                id: item.id,
                description: item.description,
                value: Number(item.value),
                due_date: item.due_date,
                remarks: item.remarks,
                share_value: item.share_value ? Number(item.share_value) : 0,
                share_user_id: item.share_user_id,
                tags: item.tags,
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
                '/fix-expense',
                {
                    description: this.expense.description,
                    value: this.expense.value,
                    due_date: this.expense.due_date,
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
                }
            )
        },

        async update() {
            this.isLoading = true
            this.$inertia.put(
                '/fix-expense/' + this.expense.id,
                {
                    description: this.expense.description,
                    value: this.expense.value,
                    due_date: this.expense.due_date,
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
                }
            )
        },

        openDelete(item) {
            this.deleteId = item.id
            this.removeDialog = true
        },

        remove() {
            this.isLoading = true
            this.$inertia.delete(`/fix-expense/${this.deleteId}`, {
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
