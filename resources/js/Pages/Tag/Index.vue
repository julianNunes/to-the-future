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
                            :items-per-page="50"
                            fixed-header
                        >
                            <template #[`item.action`]="{ item }">
                                <v-tooltip v-if="item.user_id" :text="$t('default.edit')" location="top">
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
                                <v-tooltip v-if="item.user_id" :text="$t('default.delete')" location="top">
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
                            <!-- Barra de pesquisa -->
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

        <ConfirmDialog ref="confirm" />
    </AuthenticatedLayout>
</template>

<script setup>
    import { ref, computed, nextTick } from 'vue'
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import ConfirmDialog from '@/Components/ConfirmDialog.vue'
    import { Head } from '@inertiajs/vue3'
    import writeXlsxFile from 'write-excel-file'
    import { upperCase } from '@/utils/utils.js'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'
    import { useI18n } from 'vue-i18n'

    defineOptions({ name: 'TagIndex' })

    const componentProps = defineProps({
        tags: {
            type: Array,
        },
    })

    const { t } = useI18n()

    const {
        isLoading,
        editDialog,
        titleModal,
        confirmRemove: crudConfirmRemove,
        save: crudSave,
    } = useCrudOperations('/tag')

    const search = ref(null)
    const tag = ref({ id: null, name: null })
    const txtName = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const headers = computed(() => [
        { title: t('default.name'), align: 'start', key: 'name', groupable: false },
        { title: t('default.action'), align: 'center', width: '100', key: 'action', sortable: false },
    ])

    const rules = {
        textFieldRules: [(v) => !!v || t('rules.required-text-field')],
    }

    const tagName = computed({
        get() {
            return tag.value.name
        },
        set(value) {
            tag.value.name = upperCase(value)
        },
    })

    function newItem() {
        titleModal.value = t('tag.new-item')
        editDialog.value = true
        tag.value = {
            id: null,
            name: null,
        }
        nextTick(() => {
            txtName.value?.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('tag.edit-item')
        editDialog.value = true
        tag.value = {
            id: item.id,
            name: item.name,
        }
        nextTick(() => {
            txtName.value?.focus()
        })
    }

    function save() {
        crudSave(form.value, {
            id: tag.value.id,
            name: tag.value.name,
        })
    }

    function confirmRemove(item) {
        crudConfirmRemove(item, confirm.value, t('tag.item'), t('default.confirm-delete-item'))
    }

    async function exportExcel() {
        if (componentProps.tags && componentProps.tags.length) {
            let data = []
            data.push([{ value: t('default.name') }])

            componentProps.tags.forEach((item) => {
                data.push([{ type: String, value: item.name }])
            })

            await writeXlsxFile(data, {
                data,
                fileName: 'export-tags.xlsx',
            })
        }
    }
</script>
