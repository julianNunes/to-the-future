<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">Provision</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-card class="pa-4">
            <v-row>
                <v-col md="12">
                    <v-btn text color="primary">Novo</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="data.data"
                        :search="search"
                        show-select
                        :items-per-page="-1"
                        item-key="id"
                        :no-data-text="`NAO EXISTE NADA`"
                        :no-results-text="'Nao existem registros'"
                        :footer-props="{
                            'items-per-page-text': 'Itens por Pagina',
                            'page-text': 'Page Text',
                        }"
                        :header-props="{
                            sortByText: 'Sort By',
                        }"
                        class="dataTable elevation-3"
                        fixed-header
                    >
                        <template #[`item.action`]="{ item }">
                            <v-icon color="warning" icon="mdi-pencil" size="small" @click="openEdit(item)" />
                            <v-icon
                                class="ml-2"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                @click="deleteItem(item)"
                            />
                        </template>
                        <template #top>
                            <v-toolbar flat>
                                <v-row>
                                    <v-col cols="12" lg="12" md="12" sm="12">
                                        <v-text-field
                                            v-model="search"
                                            label="Buscar"
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
                            <v-col cols="12" sm="6" md="4">
                                <v-text-field label="Description" required></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-text-field label="Value" hint="example of helper text only on focus"></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="4">
                                <v-text-field
                                    label="Legal last name*"
                                    hint="example of persistent helper text"
                                    persistent-hint
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field label="Email*" required></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field label="Password*" type="password" required></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-select :items="['0-17', '18-29', '30-54', '54+']" label="Age*" required></v-select>
                            </v-col>
                            <v-col cols="12" sm="6">
                                <v-autocomplete
                                    :items="[
                                        'Skiing',
                                        'Ice hockey',
                                        'Soccer',
                                        'Basketball',
                                        'Hockey',
                                        'Reading',
                                        'Writing',
                                        'Coding',
                                        'Basejump',
                                    ]"
                                    label="Interests"
                                    multiple
                                ></v-autocomplete>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>*indicates required field</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue-darken-1" variant="text" @click="dialog = false"> Close </v-btn>
                    <v-btn color="blue-darken-1" variant="text" @click="dialog = false"> Save </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Dialog delete -->
        <v-row justify="center">
            <v-dialog v-model="deleteDialog" persistent width="auto">
                <v-card>
                    <v-card-text>Are you sure you want to delete this item?</v-card-text>
                    <v-card-actions>
                        <v-spacer />
                        <v-btn color="error" text @click="deleteDialog = false">Cancel</v-btn>
                        <v-btn color="primary" :loading="isLoading" text @click="submitDelete">Delete</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </AuthenticatedLayout>
</template>

<script>
export default {
    name: 'PeopleIndex',
    props: {
        data: {
            type: Object,
        },
    },

    data() {
        return {
            headers: [
                { title: 'Description', key: 'description' },
                { title: 'Value', key: 'value' },
                { title: 'Week', key: 'week' },
                { title: 'Share', key: 'share_percentage' },
                { title: 'Share Value', key: 'share_value' },
                { title: 'Remarks', key: 'remarks' },
                { title: 'Action', key: 'action', sortable: false },
            ],
            breadcrumbs: [
                {
                    title: 'Dashboard',
                    disabled: false,
                    href: '/dashboard',
                },
                {
                    title: 'Provision',
                    disabled: true,
                },
            ],
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            provision: {
                id: null,
                description: null,
                value: 0,
                week: null,
                remarks: null,
                share_percentage: false,
                share_value: 0,
                share_user_id: null,
            },
            weekList: [
                {
                    name: 'Semana 1',
                    value: 'WEEK_1',
                },
                {
                    name: 'Semana 2',
                    value: 'WEEK_2',
                },
                {
                    name: 'Semana 3',
                    value: 'WEEK_3',
                },
                {
                    name: 'Semana 4',
                    value: 'WEEK_4',
                },
            ],
        }
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
            this.isLoadingTable = true

            this.$inertia.get('/provision', null, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoadingTable = false
                },
            })
        },

        newItem() {
            this.editDialog = true
        },

        closeItem() {
            this.editDialog = false
        },

        save() {
            console.log('save')
        },

        update(item) {
            console.log('update', item)
        },

        deleteItem(item) {
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
