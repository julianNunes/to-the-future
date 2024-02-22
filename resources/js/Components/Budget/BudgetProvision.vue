<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-income.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    </v-col>
                    <v-col md="12">
                        <v-data-table
                            :group-by="[{ key: 'group', order: 'asc' }]"
                            :headers="headers"
                            :items="provisions"
                            :sort-by="[{ key: 'created_at', order: 'asc' }]"
                            :search="search"
                            :loading="isLoading"
                            :loading-text="$t('default.loading-text-table')"
                            class="elevation-3"
                            density="compact"
                            :total-items="provisions.length"
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

                            <template #group-header="{ item, toggleGroup, isGroupOpen }">
                                <tr>
                                    <th class="title">
                                        <VBtn
                                            size="small"
                                            variant="text"
                                            :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                            @click="toggleGroup(item)"
                                        ></VBtn>
                                        {{ convertGroup(item.value) }}
                                    </th>
                                    <th class="title font-weight-bold text-right">Total</th>
                                    <th class="title text-right">
                                        {{ sumGroup(provisions, item.key, item.value, 'value') }}
                                    </th>
                                    <th class="title text-right">
                                        {{ sumGroup(provisions, item.key, item.value, 'share_value') }}
                                    </th>
                                    <th :colspan="3"></th>
                                </tr>
                            </template>

                            <template v-if="provisions.length" #tfoot>
                                <tr class="green--text">
                                    <th class="title"></th>
                                    <th class="title font-weight-bold text-right">Total</th>
                                    <th class="title text-right">{{ sumField(provisions, 'value') }}</th>
                                    <th class="title text-right">{{ sumField(provisions, 'share_value') }}</th>
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
                            <v-text-field
                                ref="inputDate"
                                v-model="income.date"
                                :label="$t('default.date')"
                                type="date"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="4" md="4">
                            <v-text-field
                                v-model="income.value"
                                type="number"
                                :label="$t('default.value')"
                                min="0"
                                required
                                :rules="rules.currencyFieldRules"
                                density="comfortable"
                            ></v-text-field>
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
import { sumField, currencyField } from '../../utils/utils.js'
</script>

<script>
export default {
    name: 'BudgetProvision',
    props: {
        budgetId: {
            type: Number,
        },
        provisions: {
            type: Array,
        },
        shareUsers: {
            type: Array,
        },
        viewOnly: {
            type: Boolean,
        },
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), align: 'end', key: 'action', sortable: false },
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
            provision: {
                id: null,
                description: null,
                value: 0,
                group: null,
                remarks: null,
                share_value: 0,
                share_user_id: null,
                tags: [],
                budget_id: null,
            },
            groupList: [
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

            if (
                this.provision.tags &&
                this.provision.tags.length > 0 &&
                this.provision.tags.find((x) => x.name == val)
            ) {
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
            this.titleModal = this.$t('provision.new-item')
            this.editDialog = true
            this.provision = {
                id: null,
                description: null,
                value: 0,
                group: null,
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
            this.titleModal = this.$t('provision.edit-item')
            this.editDialog = true
            this.provision = {
                id: item.id,
                description: item.description,
                value: Number(item.value),
                group: item.group,
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
                if (this.provision.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/provision',
                {
                    description: this.provision.description,
                    value: this.provision.value,
                    group: this.provision.group,
                    remarks: this.provision.remarks,
                    share_value: this.provision.share_value,
                    share_user_id: this.provision.share_user_id,
                    tags: this.provision.tags,
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
                '/provision/' + this.provision.id,
                {
                    description: this.provision.description,
                    value: this.provision.value,
                    group: this.provision.group,
                    remarks: this.provision.remarks,
                    share_value: this.provision.share_value,
                    share_user_id: this.provision.share_user_id,
                    tags: this.provision.tags,
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
            this.$inertia.delete(`/provision/${this.deleteId}`, {
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