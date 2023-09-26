<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('provision.title') }}</h5>
        </div>
        <v-card class="pa-4">
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :group-by="[{ key: 'week', order: 'asc' }]"
                        :headers="headers"
                        :items="listProvisions"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
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
                        <template #[`item.action`]="{ item }">
                            <v-icon
                                color="warning"
                                icon="mdi-pencil"
                                size="small"
                                class="me-2"
                                @click="openEdit(item)"
                            />
                            <v-icon
                                class="ml-2"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                @click="openDelete(item)"
                            />
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
                                    {{ item['week_show'] }}
                                </th>
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumGroup(item.week, 'value') }}</th>
                                <th class="title text-right">{{ sumGroup(item.week, 'share_value') }}</th>
                            </tr>
                        </template>

                        <template v-if="listProvisions.length" #tfoot>
                            <tr class="green--text">
                                <th class="title"></th>
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumField('value') }}</th>
                                <th class="title text-right">{{ sumField('share_value') }}</th>
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
                                    v-model="provision.description"
                                    :label="$t('default.description')"
                                    :rules="rules.textFieldRules"
                                    required
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <VCurrencyField
                                    v-model="provision.value"
                                    :label="$t('default.value')"
                                    required
                                    :rules="rules.currencyFieldRules"
                                    :options="{
                                        locale: 'pt-PT',
                                        currency: 'BRL',
                                        currencyDisplay: 'narrowSymbol',
                                        precision: 2,
                                        hideCurrencySymbolOnFocus: false,
                                        hideGroupingSeparatorOnFocus: false,
                                        autoDecimalDigits: true,
                                    }"
                                    density="comfortable"
                                />
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="provision.week"
                                    :label="$t('default.week')"
                                    :items="weekList"
                                    item-title="name"
                                    item-value="value"
                                    clearable
                                    :rules="rules.textFieldRules"
                                    density="comfortable"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-select
                                    v-model="provision.share"
                                    :label="$t('default.share')"
                                    :items="shareOptions"
                                    item-title="name"
                                    item-value="value"
                                    clearable
                                    density="comfortable"
                                ></v-select>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="provision.share_percentage"
                                    :label="$t('default.share-percentage')"
                                    type="number"
                                    min="0"
                                    max="100"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="3">
                                <v-text-field
                                    v-model="provision.share_value"
                                    :label="$t('default.share-value')"
                                    type="number"
                                    min="0"
                                    :rules="[
                                        (value) => {
                                            if (provision.share) {
                                                if (!value) return $t('rules.required-text-field')
                                                if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                            }
                                            return true
                                        },
                                    ]"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="6">
                                <v-select
                                    v-model="provision.share_user_id"
                                    :label="$t('default.share-user')"
                                    :items="shareUsers"
                                    item-title="share_user_name"
                                    item-value="share_user_id"
                                    clearable
                                    :rules="[
                                        (value) => {
                                            if (provision.share) {
                                                console.log('value provision.share_user_id', value)
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
                                    v-model="provision.remarks"
                                    :label="$t('default.remarks')"
                                    density="comfortable"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" :loading="isLoading" @click="editDialog = false">
                        {{ $t('default.cancel') }}
                    </v-btn>
                    <v-btn color="primary" :loading="isLoading" type="submit" @click="save">
                        {{ $t('default.save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Dialog delete -->
        <v-row justify="center">
            <v-dialog v-model="deleteDialog" persistent width="auto">
                <v-card>
                    <v-card-text>{{ $t('default.confirm-delete-item') }}</v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn color="error" text :loading="isLoading" @click="deleteDialog = false">Cancel</v-btn>
                        <v-btn color="primary" :loading="isLoading" text @click="save">Delete</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import VCurrencyField from '../../Components/VCurrencyField.vue'
</script>

<script>
// import { toRaw } from 'vue'
export default {
    name: 'PeopleIndex',
    props: {
        provisions: {
            type: Array,
        },
        shareUsers: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [
                // { title: this.$t('default.week'), align: 'start', key: 'week', groupable: false },
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.value'), align: 'end', key: 'value_show' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value_show' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.action'), key: 'action', sortable: false },
            ],
            breadcrumbs: [
                {
                    title: this.$t('menus.dashboard'),
                    disabled: false,
                    href: '/dashboard',
                },
                {
                    title: this.$t('menus.provision'),
                    disabled: true,
                },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                currencyFieldRules: [
                    (value) => {
                        // if (!value) return this.$t('rules.required-text-field')
                        if (parseFloat(value) <= 0) return this.$t('rules.required-currency-field')

                        return true
                    },
                ],
                shareValueRules: [
                    (value) => {
                        if (this.provision.share) {
                            if (!value) return this.$t('rules.required-text-field')
                            if (parseFloat(value) <= 0) return this.$t('rules.required-currency-field')
                        }
                        return true
                    },
                ],
                shareUserRules: [
                    (value) => {
                        if (this.provision.share) {
                            if (!value) return this.$t('rules.required-text-field')
                        }
                        return true
                    },
                ],
            },
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            listProvisions: [],
            provision: {
                id: null,
                description: null,
                value: 0,
                week: null,
                remarks: null,
                share: 0,
                share_percentage: 0,
                share_value: 0,
                share_user_id: null,
            },
            weekList: [
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
            shareOptions: [
                {
                    name: this.$t('default.no'),
                    value: 0,
                },
                {
                    name: this.$t('default.yes'),
                    value: 1,
                },
            ],
        }
    },

    watch: {
        'provision.share': function () {
            this.provision.share_percentage = 0
            this.provision.share_value = 0
            this.provision.share_user_id = null
        },
        'provision.share_percentage': function (value) {
            if (value > 0) {
                this.provision.share_value = (
                    (parseFloat(this.provision.value) * parseFloat(this.provision.share_percentage)) /
                    100
                ).toFixed(2)
            } else {
                this.provision.share_value = 0
            }
        },
    },

    async created() {
        if (this.provisions && this.provisions.length) {
            this.listProvisions = this.provisions.map((item) => {
                item.week_show = this.weekList.find((x) => x.value === item.week).name
                item.value_show = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
                    item.value
                )
                item.share_value_show = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
                    item.share_value
                )

                return item
            })

            console.log(this.listProvisions)
        }
    },

    async mounted() {},

    async beforeCreate() {},

    methods: {
        sumField(key) {
            // sum data in give key (property)
            let total = this.listProvisions.reduce((a, b) => (parseFloat(a) + (parseFloat(b[key]) || 0)).toFixed(2), 0)

            total = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total)
            return total
        },
        sumGroup(group, key) {
            console.log('group', group)
            // sum data in give key (property)
            let total = this.listProvisions
                .filter((x) => x.week === group)
                .reduce((a, b) => (parseFloat(a) + (parseFloat(b[key]) || 0)).toFixed(2), 0)
            console.log('total sumGroup', total)
            total = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total)
            return total
        },

        newItem() {
            this.titleModal = this.$t('provision.new-item')
            this.provision = {
                id: null,
                description: null,
                value: 0,
                week: null,
                remarks: null,
                share: 0,
                share_percentage: 0,
                share_value: 0,
                share_user_id: null,
            }
            this.editDialog = true
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('provision.edit-item')
            this.provision = {
                id: item.id,
                description: item.description,
                value: item.value,
                week: item.week,
                remarks: item.remarks,
                share: item.share,
                share_percentage: item.share
                    ? parseFloat((parseFloat(item.value) * 100) / parseFloat(item.share_percentage))
                    : 0,
                share_value: item.share_value,
                share_user_id: item.share_user_id,
            }
            this.editDialog = true
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
                console.log('provision', this.provision)
                // this.isLoading = true
                // this.$inertia.post(
                //     '/provision',
                //     {
                //         description: this.provision.description,
                //         value: this.provision.value,
                //         week: this.provision.week,
                //         remarks: this.provision.remarks,
                //         share_value: this.provision.share_value,
                //         share_user_id: this.provision.share_user_id,
                //     },
                //     {
                //         onSuccess: () => {
                //             this.editDialog = false
                //             this.isLoading = false
                //         },
                //         onError: (errors) => {
                //             console.log('errors', errors)
                //             this.isLoading = false
                //         },
                //         onFinish: () => {
                //             console.log('onFinish')
                //             this.isLoading = false
                //         },
                //     }
                // )
            }
        },

        async update(item) {
            console.log('update', item)
        },

        openDelete(item) {
            this.deleteId = item.value
            this.deleteDialog = true
        },

        submitDelete() {
            this.isLoading = true
            this.$inertia.delete(`/people/${this.deleteId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoading = false
                    this.deleteDialog = false
                },
            })
        },
    },
}
</script>
