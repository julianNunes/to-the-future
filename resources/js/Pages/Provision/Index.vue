<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('menus.provision') }}</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-card class="pa-4">
            <v-row>
                <v-col md="12">
                    <v-spacer />
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="listProvisions"
                        :search="search"
                        :items-per-page="-1"
                        item-key="id"
                        :no-data-text="$t('default.no-data-text')"
                        :no-results-text="$t('default.no-data-text')"
                        :footer-props="{
                            'items-per-page-text': $t('default.itens-per-page'),
                            'page-text': $t('default.page-text'),
                        }"
                        :header-props="{
                            sortByText: $t('default.sort-by'),
                        }"
                        class="elevation-3"
                        fixed-header
                    >
                        <template #[`item.action`]="{ item }">
                            <v-icon color="warning" icon="mdi-pencil" size="small" @click="openEdit(item)" />
                            <v-icon
                                class="ml-2"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                @click="openDelete(item)"
                            />
                        </template>
                        <template #top>
                            <v-toolbar flat>
                                <v-row>
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
        <v-dialog v-model="editDialog" persistent width="1024">
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ titleModal }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12" sm="12" md="12">
                                <v-text-field
                                    v-model="provision.description"
                                    :label="$t('default.description')"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="provision.value"
                                    :label="$t('default.value')"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-select
                                    v-model="provision.week"
                                    :label="$t('default.week')"
                                    :items="weekList"
                                    item-title="name"
                                    item-value="value"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-select
                                    v-model="provision.share"
                                    :label="$t('default.share')"
                                    :items="shareOptions"
                                    item-title="name"
                                    item-value="value"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="provision.share_percentage"
                                    :label="$t('default.share-percentage')"
                                    type="number"
                                    min="0"
                                    max="100"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="provision.share_value"
                                    :label="$t('default.share-percentage')"
                                    type="number"
                                    min="0"
                                ></v-text-field>
                            </v-col>
                            <v-col v-if="provision.share" cols="12" sm="6" md="6">
                                <v-select
                                    v-model="provision.share_user_id"
                                    :label="$t('default.share-user')"
                                    :items="listUsersShare"
                                    item-title="name"
                                    item-value="id"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-text-field v-model="provision.remarks" :label="$t('default.remarks')"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="error" @click="editDialog = false">
                        {{ $t('default.cancel') }}
                    </v-btn>
                    <v-btn color="primary" :disabled="validItem()" @click="submitDelete">
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
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
</script>

<script>
export default {
    name: 'PeopleIndex',
    props: {
        data: {
            type: Object,
        },
        usersShare: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.description'), key: 'description' },
                { title: this.$t('default.value'), key: 'value' },
                { title: this.$t('default.week'), key: 'week' },
                { title: this.$t('default.share'), key: 'share_percentage' },
                { title: this.$t('default.share-value'), key: 'share_value' },
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
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            listProvisions: [],
            listUsersShare: [],
            provision: {
                id: null,
                description: null,
                value: 0,
                week: null,
                remarks: null,
                share: null,
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
                this.provision.share_value =
                    (parseFloat(this.provision.value) * parseFloat(this.provision.share_percentage)) / 100
            } else {
                this.provision.share_value = 0
            }
        },
    },

    async created() {
        console.log('created')
        console.log('this.data', this.data)
    },

    async mounted() {
        console.log('mounted')
        console.log('this.data', this.data)
    },

    methods: {
        getAll() {
            this.isLoading = true

            this.$inertia.get('/provision', null, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoading = false
                },
            })
        },

        newItem() {
            this.titleModal = this.$t('provision.new-item')
            this.provision = {
                id: null,
                description: null,
                value: 0,
                week: null,
                remarks: null,
                share: null,
                share_percentage: 0,
                share_value: 0,
                share_user_id: null,
            }
            this.editDialog = true
        },

        closeItem() {
            this.editDialog = false
        },

        validItem() {},

        save() {
            console.log('save')
        },

        update(item) {
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
