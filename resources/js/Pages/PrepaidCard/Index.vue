<template>

    <Head title="Credit Card" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">{{ $t('prepaid-card.title') }}</h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <!-- Tabela com dados -->
    <v-card>
        <v-card-text>
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table :headers="headers" :items="prepaidCards"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]" :search="search" :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')" class="elevation-3" density="compact"
                        :total-items="prepaidCards.length" :no-data-text="$t('default.no-data-text')"
                        :no-results-text="$t('default.no-data-text')" :footer-props="{
                            'items-per-page-text': $t('default.itens-per-page'),
                            'page-text': $t('default.page-text'),
                        }" :header-props="{
                            sortByText: $t('default.sort-by'),
                        }" fixed-header>
                        <template #[`item.is_active`]="{ item }">{{
                            item.is_active ? $t('default.yes') : $t('default.no')
                            }}</template>
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('prepaid-card.extracts')" location="top">
                                <template #activator="{ props }">
                                    <Link :href="hrefExtract(item)" class="v-breadcrumbs-item--link">
                                        <v-icon v-bind="props" color="warning" icon="mdi-checkbook"
                                            size="small"></v-icon>
                                    </Link>
                                </template>
                            </v-tooltip>
                            <v-tooltip :text="$t('default.edit')" location="top">
                                <template #activator="{ props }">
                                    <v-icon v-bind="props" color="warning" icon="mdi-pencil" size="small" class="ml-1"
                                        @click="editItem(item)">
                                    </v-icon>
                                </template>
                            </v-tooltip>
                            <v-tooltip :text="$t('default.delete')" location="top">
                                <template #activator="{ props }">
                                    <v-icon v-bind="props" class="ml-1" color="error" icon="mdi-delete" size="small"
                                        @click="confirmRemove(item)">
                                    </v-icon>
                                </template>
                            </v-tooltip>
                        </template>

                        <template #top>
                            <v-toolbar density="comfortable">
                                <v-row dense>
                                    <v-col cols="12" lg="12" md="12" sm="12">
                                        <v-text-field v-model="search" :label="$t('default.search')"
                                            append-icon="mdi-magnify" single-line hide-details clearable
                                            @click:clear="search = null"></v-text-field>
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
                        <v-col cols="12" sm="12" md="6">
                            <v-text-field ref="txtName" v-model="prepaidCard.name" :label="$t('default.name')"
                                :rules="rules.textFieldRules" required density="comfortable"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field v-model="prepaidCard.digits" :label="$t('prepaid-card.4-digits')" :counter="4"
                                :maxlength="4" required :rules="rules.digitsFieldRules"
                                density="comfortable"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select v-model="prepaidCard.is_active" :label="$t('default.active')"
                                :items="isActiveOptions" item-title="name" item-value="value" clearable
                                :rules="rules.booleanFieldRules" density="comfortable"></v-select>
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
import { ref, computed, nextTick } from 'vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useCrudOperations } from '@/composables/useCrudOperations.js'

defineOptions({ name: 'PrepaidCardIndex', layout: AuthenticatedLayout })

defineProps({
    prepaidCards: {
        type: Array,
    },
})

const { t } = useI18n()

const { isLoading, editDialog, titleModal, confirmRemove: crudConfirmRemove } = useCrudOperations('/prepaid-card')

const breadcrumbs = computed(() => [
    {
        title: t('menus.dashboard'),
        disabled: false,
        href: '/dashboard',
    },
    {
        title: t('menus.prepaid-card'),
        disabled: true,
    },
])

const headers = computed(() => [
    { title: t('default.name'), key: 'name', groupable: false },
    { title: t('prepaid-card.digits'), key: 'digits' },
    { title: t('default.active'), key: 'is_active' },
    { title: t('default.action'), align: 'center', key: 'action', sortable: false },
])

const rules = {
    textFieldRules: [(v) => !!v || t('rules.required-text-field')],
    booleanFieldRules: [(v) => v !== null || t('rules.required-text-field')],
    digitsFieldRules: [
        (value) => {
            if (!value) return t('rules.required-text-field')
            if (!/^\d+$/.test(value)) return t('rules.only-numbers')

            return true
        },
    ],
}

const search = ref(null)

const prepaidCard = ref({
    id: null,
    name: null,
    digits: null,
    is_active: null,
})

const isActiveOptions = computed(() => [
    {
        name: t('default.no'),
        value: 0,
    },
    {
        name: t('default.yes'),
        value: 1,
    },
])

const form = ref(null)
const confirm = ref(null)
const txtName = ref(null)

function hrefExtract(item) {
    return '/prepaid-card/' + item.id + '/extract'
}

function newItem() {
    titleModal.value = t('prepaid-card.new-item')
    editDialog.value = true
    prepaidCard.value = {
        id: null,
        name: null,
        digits: null,
        is_active: null,
    }
    nextTick(() => {
        txtName.value?.focus()
    })
}

function editItem(item) {
    titleModal.value = t('prepaid-card.edit-item')
    editDialog.value = true
    prepaidCard.value = {
        id: item.id,
        name: item.name,
        digits: item.digits,
        is_active: item.is_active,
    }
    nextTick(() => {
        txtName.value?.focus()
    })
}

async function save() {
    let validate = await form.value.validate()
    if (validate.valid) {
        if (prepaidCard.value.id) {
            await update()
        } else {
            await create()
        }
    }
}

async function create() {
    isLoading.value = true
    router.post(
        '/prepaid-card',
        {
            name: prepaidCard.value.name,
            digits: prepaidCard.value.digits,
            is_active: prepaidCard.value.is_active,
        },
        {
            onSuccess: () => {
                editDialog.value = false
            },
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

async function update() {
    isLoading.value = true
    router.put(
        '/prepaid-card/' + prepaidCard.value.id,
        {
            name: prepaidCard.value.name,
            digits: prepaidCard.value.digits,
            is_active: prepaidCard.value.is_active,
        },
        {
            onSuccess: () => {
                editDialog.value = false
            },
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

function confirmRemove(item) {
    crudConfirmRemove(item, confirm.value, t('prepaid-card.item'), t('default.confirm-delete-item'))
}
</script>
