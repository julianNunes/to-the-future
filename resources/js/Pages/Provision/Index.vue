<template>

    <Head title="Provision" />
    <div class="mb-5">
        <h5 class="text-h5 font-weight-bold">
            {{ $t('provision.title') }}
        </h5>
        <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <v-card>
        <v-card-text>
            <v-row dense>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">
                        {{ $t('default.new') }}
                    </v-btn>
                </v-col>
                <v-col md="12">
                    <v-data-table :group-by="[{ key: 'group', order: 'asc' }]" :headers="headers" :items="provisions"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]" :search="search" :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')" class="elevation-3" density="compact"
                        :total-items="provisions.length" :no-data-text="$t('default.no-data-text')"
                        :no-results-text="$t('default.no-data-text')" :footer-props="{
                            'items-per-page-text': $t('default.itens-per-page'),
                            'page-text': $t('default.page-text'),
                        }" :header-props="{
                                sortByText: $t('default.sort-by'),
                            }" :items-per-page="50" fixed-header>
                        <template #[`item.value`]="{ item }">
                            {{ currencyField(item.value) }}
                        </template>
                        <template #[`item.share_value`]="{ item }">
                            {{ currencyField(item.share_value) }}
                        </template>
                        <template #[`item.group`]="{ item }">
                            {{ convertGroup(item.group) }}
                        </template>
                        <template #[`item.tags`]="{ item }">
                            {{item.tags.length ? item.tags.map((x) => x.name).join(' | ') : ''}}
                        </template>
                        <template #[`item.share_user_id`]="{ item }">
                            {{ item.share_user ? item.share_user.name : '' }}
                        </template>
                        <template #[`item.action`]="{ item }">
                            <v-tooltip :text="$t('default.edit')" location="top">
                                <template #activator="{ props }">
                                    <v-icon v-bind="props" color="war
                                        ning" icon="mdi-pencil" size="small" @click="editItem(item)">
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

                        <template #group-header="{ item, toggleGroup, isGroupOpen }">
                            <tr>
                                <th class="title">
                                    v
                                    <VBtn size="small" variant="text" :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                        @click="toggleGroup(item)"></VBtn>
                                    {{ convertGroup(item.value) }}
                                </th>
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">
                                    {{ sumGroup(provisions, item.key, item.value, 'value') }}
                                </th>
                                <th class="title text-right">
                                    {{ sumGroup(provisions, item.key, item.value, 'share_value') }}
                                </th>
                                <th :colspan="3" />
                            </tr>
                        </template>

                        <template v-if="provisions.length" #tfoot>
                            <tr class="text-green">
                                <th class="title" />
                                <th class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">
                                    {{ sumField(provisions, 'value') }}
                                </th>
                                <th class="title text-right">
                                    {{ sumField(provisions, 'share_value') }}
                                </th>
                            </tr>
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
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field ref="txtDescription" v-model="provision.description"
                                :label="$t('default.description')" :rules="rules.textFieldRules" required
                                density="comfortable" />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money v-model="provision.value" :label="$t('default.value')" density="comfortable"
                                :rules="rules.currencyFieldRules" :options="{
                                    locale: 'pt-BR',
                                    prefix: 'R$',
                                    suffix: '',
                                    length: 11,
                                    precision: 2,
                                }" />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select v-model="provision.group" :label="$t('default.group')" :items="groupList"
                                item-title="name" item-value="value" clearable :rules="rules.textFieldRules"
                                density="comfortable"></v-select>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field v-model="percentage" type="number" :label="$t('default.percentage-share')"
                                density="comfortable" @blur="calculeShareValue"></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money v-model="provision.share_value" :label="$t('default.share-value')"
                                density="comfortable" :rules="[
                                    (value) => {
                                        if (provision.share_user_id) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]" :options="{
                                        locale: 'pt-BR',
                                        prefix: 'R$',
                                        suffix: '',
                                        length: 11,
                                        precision: 2,
                                    }" />
                        </v-col>
                        <v-col cols="12" sm="6" md="8">
                            <v-select v-model="provision.share_user_id" :label="$t('default.share-user')"
                                :items="shareUsers" item-title="share_user_name" item-value="share_user_id" clearable
                                :rules="[
                                    (value) => {
                                        if (provision.share_value && parseFloat(provision.share_value) > 0) {
                                            if (!value) return $t('rules.required-text-field')
                                        }
                                        return true
                                    },
                                ]" density="comfortable"></v-select>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-text-field v-model="provision.remarks" :label="$t('default.remarks')"
                                density="comfortable"></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete v-model="provision.tags" v-model:search="searchTag"
                                :label="$t('default.tags')" :items="itemsTags" :loading="loadingData" item-title="name"
                                item-value="name" clearable multiple chips :closable-chips="true"
                                :clear-on-select="true" return-object hide-no-data hide-selected
                                placeholder="Start typing to Search" prepend-icon="mdi-database-search"
                                @update:search="searchTags" @update:model-value="searchTag = ''"></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
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
import { logger } from '@/utils/logger.js'
import { Head, router } from '@inertiajs/vue3'
import { currencyField, reverseFormatNumber, sumField, sumGroup } from '@/utils/utils.js'
import { useI18n } from 'vue-i18n'
import { useCrudOperations } from '@/composables/useCrudOperations.js'

defineOptions({ name: 'ProvisionIndex', layout: AuthenticatedLayout })

defineProps({
    provisions: { type: Array },
    shareUsers: { type: Array },
})

const { t } = useI18n()

const { isLoading, editDialog, titleModal, confirmRemove: crudConfirmRemove } = useCrudOperations('/provision')

const search = ref(null)
const percentage = ref(null)
const searchTag = ref('')
const loadingData = ref(false)
const listTags = ref([])
const txtDescription = ref(null)
const form = ref(null)
const confirm = ref(null)
let timeOut = null

const provision = ref({
    id: null,
    description: null,
    value: 0,
    group: null,
    remarks: null,
    share_value: 0,
    share_user_id: null,
    tags: [],
})

const breadcrumbs = computed(() => [
    { title: t('menus.dashboard'), disabled: false, href: '/dashboard' },
    { title: t('menus.provision'), disabled: true },
])

const headers = computed(() => [
    { title: t('default.description'), align: 'start', key: 'description', groupable: false },
    { title: t('default.value'), align: 'end', key: 'value' },
    { title: t('default.share-value'), align: 'end', key: 'share_value' },
    { title: t('default.share-user'), key: 'share_user_id' },
    { title: t('default.remarks'), key: 'remarks' },
    { title: t('default.tags'), key: 'tags' },
    { title: t('default.action'), align: 'center', key: 'action', sortable: false },
])

const groupList = computed(() => [
    { name: t('default.monthly'), value: 'MONTHLY' },
    { name: t('default.week-1'), value: 'WEEK_1' },
    { name: t('default.week-2'), value: 'WEEK_2' },
    { name: t('default.week-3'), value: 'WEEK_3' },
    { name: t('default.week-4'), value: 'WEEK_4' },
])

const rules = {
    textFieldRules: [(v) => !!v || t('rules.required-text-field')],
    currencyFieldRules: [
        (value) => {
            value = reverseFormatNumber(value)
            if (!value) return t('rules.required-text-field')
            if (Number(value) <= 0) return t('rules.required-currency-field')
            return true
        },
    ],
}

const itemsTags = computed(() => listTags.value)

function calculeShareValue(evt) {
    if (provision.value.value) {
        provision.value.share_value = parseFloat((provision.value.value * evt.target.value) / 100).toFixed(2)
    }
}

function convertGroup(group) {
    return groupList.value.find((x) => x.value === group)?.name ?? group
}

async function searchTags(val) {
    if (loadingData.value) return

    if (!val || val.length <= 1) {
        listTags.value = []
        clearTimeout(timeOut)
        return
    }

    if (
        provision.value.tags &&
        provision.value.tags.length > 0 &&
        provision.value.tags.find((x) => x.name == val)
    ) {
        return
    }

    clearTimeout(timeOut)
    timeOut = setTimeout(async () => {
        loadingData.value = true
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
                logger.error('error', error)
            })

        listTags.value = searchFieldsData
        loadingData.value = false
    }, 300)
}

function newItem() {
    titleModal.value = t('provision.new-item')
    editDialog.value = true
    provision.value = {
        id: null,
        description: null,
        value: 0,
        group: null,
        remarks: null,
        share_value: 0,
        share_user_id: null,
        tags: [],
    }
    nextTick(() => txtDescription.value?.focus())
}

function editItem(item) {
    titleModal.value = t('provision.edit-item')
    editDialog.value = true
    provision.value = {
        id: item.id,
        description: item.description,
        value: Number(item.value),
        group: item.group,
        remarks: item.remarks,
        share_value: item.share_value ? Number(item.share_value) : 0,
        share_user_id: item.share_user_id,
        tags: item.tags,
    }
    nextTick(() => txtDescription.value?.focus())
}

async function save() {
    const validate = await form.value.validate()
    if (validate.valid) {
        if (provision.value.id) {
            await _update()
        } else {
            await _create()
        }
    }
}

async function _create() {
    isLoading.value = true
    router.post(
        '/provision',
        {
            description: provision.value.description,
            value: provision.value.value,
            group: provision.value.group,
            remarks: provision.value.remarks,
            share_value: provision.value.share_value,
            share_user_id: provision.value.share_user_id,
            tags: provision.value.tags,
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

async function _update() {
    isLoading.value = true
    router.put(
        '/provision/' + provision.value.id,
        {
            description: provision.value.description,
            value: provision.value.value,
            group: provision.value.group,
            remarks: provision.value.remarks,
            share_value: provision.value.share_value,
            share_user_id: provision.value.share_user_id,
            tags: provision.value.tags,
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
    crudConfirmRemove(item, confirm.value, t('provision.item'), t('default.confirm-delete-item'))
}
</script>
