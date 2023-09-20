<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">Provision</h5>
            <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
        </div>
        <v-card class="pa-4">
            <div class="d-flex flex-wrap align-center">
                <v-text-field
                    v-model="search"
                    label="Search"
                    variant="underlined"
                    prepend-inner-icon="mdi-magnify"
                    hide-details
                    clearable
                    single-line
                />
                <v-spacer />
                <Link href="/people/create" as="div">
                    <v-btn color="primary">Create</v-btn>
                </Link>
            </div>

            <v-data-table
                :headers="headers"
                :items="data.data"
                :search="search"
                show-select
                :items-per-page="-1"
                item-key="doc_no"
                sort-by="doc_no"
                :sort-desc="true"
                :no-data-text="$t('DEFAULT.NoDataAvailable')"
                :no-results-text="$t('DEFAULT.NoDataAvailable')"
                :footer-props="{
                    'items-per-page-text': $t('DEFAULT.Grid.ItensPerPage'),
                    'page-text': $t('DEFAULT.Grid.PageText'),
                }"
                :header-props="{
                    sortByText: $t('DEFAULT.Grid.sortBy'),
                }"
                class="dataTable elevation-3"
                :height="calcHeight()"
                fixed-header
            >
                <template #[`item.action`]="{ item }">
                    <v-icon color="warning" icon="mdi-pencil" size="small" @click="openEdit(item)" />
                    <v-icon class="ml-2" color="error" icon="mdi-delete" size="small" @click="deleteItem(item)" />
                </template>
                <template #top>
                    <v-toolbar flat>
                        <v-row>
                            <v-col cols="12" lg="12" md="12" sm="12">
                                <v-text-field
                                    v-model="search"
                                    :label="$t('DEFAULT.btnSearch')"
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
        </v-card>

        <!-- Dialog Criacao/Edicao -->

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
                { title: 'Gender', key: 'gender' },
                { title: 'Email', key: 'email' },
                { title: 'Phone Number', key: 'phone' },
                { title: 'Created At', key: 'created_at' },
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
            isLoadingTable: false,
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
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
