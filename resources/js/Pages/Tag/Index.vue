<template>
    <Head title="Tag" />
    <AuthenticatedLayout>
        <div class="mb-5">
            <h5 class="text-h5 font-weight-bold">{{ $t('tag.title') }}</h5>
        </div>

        <v-card>
            <v-card-text>
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                        <v-btn color="info" class="ml-1" @click="exportExcel">{{ $t('default.export-excel') }}</v-btn>
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
                            :total-items="tags.length"
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
            </v-card-text>
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
                                    v-model="tagName"
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
</script>

<script>
import { upperCase } from '../../utils/utils.js'
import writeXlsxFile from 'write-excel-file'

export default {
    name: 'TagIndex',
    props: {
        tags: {
            type: Array,
        },
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.name'), align: 'start', key: 'name', groupable: false },
                { title: this.$t('default.action'), align: 'end', width: '100', key: 'action', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
            },
            search: null,
            editDialog: false,
            removeDialog: false,
            isLoading: false,
            deleteId: null,
            tag: {
                id: null,
                name: null,
            },
        }
    },

    computed: {
        tagName: {
            get() {
                return this.tag.name
            },
            set(value) {
                this.tag.name = upperCase(value)
            },
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        newItem() {
            this.titleModal = this.$t('tag.new-item')
            this.editDialog = true
            this.tag = {
                id: null,
                name: null,
            }
            setTimeout(() => {
                this.$refs.txtName.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('tag.edit-item')
            this.editDialog = true
            this.tag = {
                id: item.id,
                name: item.name,
            }
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
                if (this.tag.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/tag',
                {
                    name: this.tag.name,
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
                '/tag/' + this.tag.id,
                {
                    name: this.tag.name,
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
            this.$inertia.delete(`/tag/${this.deleteId}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoading = false
                    this.removeDialog = false
                },
            })
        },

        async exportExcel() {
            if (this.tags && this.tags.length) {
                let data = []
                data.push([{ value: this.$t('default.name') }])

                this.tags.forEach((item) => {
                    data.push([{ type: String, value: item.name }])
                })

                await writeXlsxFile(data, {
                    data, // (optional) column widths, etc.
                    fileName: 'export-tags.xlsx',
                })
            }
        },
    },
}
</script>
