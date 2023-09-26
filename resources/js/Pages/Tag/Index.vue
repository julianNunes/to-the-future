<template>
    <Head title="Provision" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('tag.title') }}</h5>
        </div>
        <v-card class="pa-4">
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table
                        :headers="headers"
                        :items="tags"
                        :sort-by="[{ key: 'name', order: 'asc' }]"
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
                                @click="editItem(item.raw)"
                            />
                            <v-icon
                                class="ml-2"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                @click="openDelete(item.raw)"
                            />
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
                                    ref="txtName"
                                    v-model="tag.name"
                                    :label="$t('default.name')"
                                    :rules="rules.textFieldRules"
                                    required
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
                        <v-btn color="primary" :loading="isLoading" text @click="this.delete()">Delete</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
</script>

<script>
export default {
    name: 'PeopleIndex',
    props: {
        tags: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [{ title: this.$t('default.name'), align: 'start', key: 'name' }],
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
            },
            search: null,
            editDialog: false,
            deleteDialog: false,
            isLoading: false,
            deleteId: null,
            listProvisions: [],
            tag: {
                id: null,
                name: null,
            },
        }
    },

    async created() {},

    async mounted() {},

    methods: {
        newItem() {
            this.titleModal = this.$t('tag.new-item')
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
                this.$refs.txtName.focus()
            })
        },

        editItem(item) {
            console.log('item', item)
            this.titleModal = this.$t('provision.edit-item')
            this.provision = {
                id: item.id,
                description: item.description,
                value: parseFloat(item.value),
                week: item.week,
                remarks: item.remarks,
                share_value: item.share_value ? parseFloat(item.share_value) : 0,
                share_user_id: item.share_user_id,
            }
            this.editDialog = true
            setTimeout(() => {
                this.$refs.txtName.focus()
            })
        },

        closeItem() {
            this.editDialog = false
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                this.isLoading = true
                this.$inertia.post(
                    '/provision',
                    {
                        description: this.provision.description,
                        value: this.provision.value,
                        week: this.provision.week,
                        remarks: this.provision.remarks,
                        share_value: this.provision.share_value,
                        share_user_id: this.provision.share_user_id,
                    },
                    {
                        onSuccess: () => {
                            this.editDialog = false
                            this.isLoading = false
                        },
                        onError: () => {
                            this.isLoading = false
                        },
                        onFinish: () => {
                            this.isLoading = false
                        },
                    }
                )
            }
        },

        async update() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                this.isLoading = true
                this.$inertia.post(
                    '/provision/' + this.provision.id,
                    {
                        description: this.provision.description,
                        value: this.provision.value,
                        week: this.provision.week,
                        remarks: this.provision.remarks,
                        share_value: this.provision.share_value,
                        share_user_id: this.provision.share_user_id,
                    },
                    {
                        onSuccess: () => {
                            this.editDialog = false
                            this.isLoading = false
                        },
                        onError: () => {
                            this.isLoading = false
                        },
                        onFinish: () => {
                            this.isLoading = false
                        },
                    }
                )
            }
        },

        openDelete(item) {
            this.deleteId = item.id
            this.deleteDialog = true
        },

        submitDelete() {
            this.isLoading = true
            this.$inertia.delete(`/provision/${this.deleteId}`, {
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
