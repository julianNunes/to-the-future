<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-provision.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-row dense>
                    <v-col v-if="!viewOnly" md="12">
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
                                            @click="confirmRemove(item)"
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
                                        >
                                        </VBtn>
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
                                <tr class="text-green">
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
                            <v-autocomplete
                                ref="txtDescription"
                                v-model="provision.description"
                                v-model:search="searchDescription"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                                :items="itemsDescriptions"
                                :loading="loadingData"
                                item-title="description"
                                item-value="description"
                                clearable
                                return-object
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                auto-select-first
                                @update:search="searchDescriptions"
                                @update:model-value="selectedDescription"
                            >
                                <template #item="{ props, item }">
                                    <v-list-item
                                        v-bind="props"
                                        :subtitle="item.raw.resume"
                                        :title="item.raw.description"
                                    ></v-list-item>
                                </template>
                            </v-autocomplete>
                        </v-col>
                        <v-col cols="12" sm="4" md="3">
                            <vuetify-money
                                v-model="provision.value"
                                :label="$t('default.value')"
                                density="comfortable"
                                :rules="rules.currencyFieldRules"
                                :options="{
                                    locale: 'pt-BR',
                                    prefix: 'R$',
                                    suffix: '',
                                    length: 11,
                                    precision: 2,
                                }"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="provision.group"
                                :label="$t('default.group')"
                                :items="groupList"
                                item-title="name"
                                item-value="value"
                                clearable
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-select>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="percentage"
                                type="number"
                                :label="$t('default.percentage-share')"
                                density="comfortable"
                                @blur="calculeShareValue"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="provision.share_value"
                                :label="$t('default.share-value')"
                                density="comfortable"
                                :rules="[
                                    (value) => {
                                        if (provision.share_user_id) {
                                            value = reverseFormatNumber(value)
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                :options="{
                                    locale: 'pt-BR',
                                    prefix: 'R$',
                                    suffix: '',
                                    length: 11,
                                    precision: 2,
                                }"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="provision.share_user_id"
                                :label="$t('default.share-user')"
                                :items="shareUsers"
                                item-title="share_user_name"
                                item-value="share_user_id"
                                clearable
                                :rules="[
                                    (value) => {
                                        if (provision.share_value && parseFloat(provision.share_value) > 0) {
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
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="provision.tags"
                                v-model:search="searchTag"
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
                                @update:model-value="searchTag = ''"
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

    <ConfirmDialog ref="confirm" />
</template>

<script setup>
    import { currencyField, sumField, sumGroup } from '@/utils/utils.js'
import { router } from '@inertiajs/vue3'
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'

    import { useCrudOperations } from '@/composables/useCrudOperations.js'
import { useDescriptionSearch } from '@/composables/useDescriptionSearch.js'
import { useValidationRules } from '@/composables/useFormConstants.js'
import { useShareCalculation } from '@/composables/useShareCalculation.js'
import { useTagSearch } from '@/composables/useTagSearch.js'

    defineOptions({ name: 'BudgetProvision' })

    const componentProps = defineProps({
        budgetId: { type: Number },
        provisions: {
            type: Array,
            default: () => [],
        },
        yearMonth: { type: String },
        shareUsers: { type: Array },
        budgetWeeks: { type: Array },
        viewOnly: { type: Boolean, default: false },
    })

    const { t } = useI18n()
    const rules = useValidationRules()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/budget-provision')

    const { tags: listTags, isSearching: isTagSearching, searchTags: doSearchTags } = useTagSearch()
    const {
        descriptions: listDescriptions,
        isSearching: isDescriptionSearching,
        searchDescriptions: doSearchDescriptions,
    } = useDescriptionSearch('budget-provision')
    const { calculateShareValue } = useShareCalculation()

    const search = ref(null)
    const panel = ref(1)
    const percentage = ref(null)
    const deleteId = ref(null)
    const searchTag = ref('')
    const searchDescription = ref('')

    const provision = ref({
        id: null,
        description: null,
        value: 0,
        group: null,
        remarks: null,
        share_value: 0,
        share_user_id: null,
        tags: [],
        budget_id: null,
    })

    const groupList = [
        { name: t('default.monthly'), value: 'MONTHLY' },
        { name: t('default.week-1'), value: 'WEEK_1' },
        { name: t('default.week-2'), value: 'WEEK_2' },
        { name: t('default.week-3'), value: 'WEEK_3' },
        { name: t('default.week-4'), value: 'WEEK_4' },
    ]

    // Refs for template
    const txtDescription = ref(null)
    const form = ref(null)
    const confirm = ref(null)

    const loadingData = computed(() => isTagSearching.value || isDescriptionSearching.value)
    const itemsTags = computed(() => listTags.value)

    const itemsDescriptions = computed(() => {
        let result = []
        if (listDescriptions.value?.length) {
            result = listDescriptions.value.map((x) => {
                let resume = ''
                if (x.value) resume += t('default.value') + ': ' + currencyField(x.value)
                if (x.share_value) resume += ' | ' + t('default.share-value') + ': ' + currencyField(x.share_value)
                if (x.tags && x.tags.length > 0)
                    resume += ' | ' + t('default.tags') + ': ' + x.tags.map((tag) => tag.name).join(', ')
                if (x.remarks) resume += ' | ' + t('default.remarks') + ': ' + x.remarks
                return {
                    description: x.description,
                    resume: resume,
                    data: x,
                }
            })
        }
        return result
    })

    const headers = computed(() => {
        let hdrs = [
            { title: t('default.description'), align: 'start', key: 'description', groupable: false },
            { title: t('default.value'), align: 'end', key: 'value' },
            { title: t('default.share-value'), align: 'end', key: 'share_value' },
            { title: t('default.share-user'), key: 'share_user_id' },
            { title: t('default.remarks'), key: 'remarks' },
            { title: t('default.tags'), key: 'tags' },
        ]

        if (!componentProps.viewOnly) {
            hdrs.push({
                title: t('default.action'),
                align: 'end',
                key: 'action',
                sortable: false,
                width: 40,
            })
        }
        return hdrs
    })

    function calculeShareValue(evt) {
        if (provision.value.value) {
            provision.value.share_value = calculateShareValue(provision.value.value, evt.target.value)
        }
    }

    function convertGroup(group) {
        if (componentProps.budgetWeeks?.length && componentProps.budgetWeeks.find((x) => x.value === group)) {
            return (
                groupList.find((x) => x.value === group)?.name +
                ' (' +
                componentProps.budgetWeeks.find((x) => x.value === group).text +
                ')'
            )
        }
        return groupList.find((x) => x.value === group)?.name || group
    }

    async function searchTags(val) {
        const existing = provision.value.tags ? provision.value.tags : []
        doSearchTags(val, existing)
    }

    async function searchDescriptions(val) {
        doSearchDescriptions(val)
    }

    async function selectedDescription(item) {
        if (item?.data) {
            provision.value.value = item.data.value
            provision.value.share_value = item.data.share_value
            provision.value.share_user_id = item.data.share_user_id
            provision.value.remarks = item.data.remarks
            provision.value.tags = item.data.tags
        }
    }

    function newItem() {
        titleModal.value = t('budget-provision.new-item')
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
            budget_id: componentProps.budgetId,
        }
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('budget-provision.edit-item')
        editDialog.value = true
        provision.value = {
            id: item.id,
            description: item.description?.description || item.description,
            value: Number(item.value),
            group: item.group,
            remarks: item.remarks,
            share_value: item.share_value ? Number(item.share_value) : 0,
            share_user_id: item.share_user_id,
            tags: item.tags || [],
            budget_id: componentProps.budgetId,
        }
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    async function save() {
        let validate = await form.value.validate()
        if (validate.valid) {
            if (provision.value.id) {
                await updateData()
            } else {
                await createData()
            }
        }
    }

    async function createData() {
        isLoading.value = true
        router.post(
            '/budget-provision',
            {
                description: provision.value.description?.description || provision.value.description,
                value: provision.value.value,
                group: provision.value.group,
                remarks: provision.value.remarks,
                share_value: provision.value.share_value,
                share_user_id: provision.value.share_user_id,
                tags: provision.value.tags,
                budget_id: provision.value.budget_id,
            },
            {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            }
        )
    }

    async function updateData() {
        isLoading.value = true
        router.put(
            '/budget-provision/' + provision.value.id,
            {
                description: provision.value.description?.description || provision.value.description,
                value: provision.value.value,
                group: provision.value.group,
                remarks: provision.value.remarks,
                share_value: provision.value.share_value,
                share_user_id: provision.value.share_user_id,
                tags: provision.value.tags,
                budget_id: provision.value.budget_id,
            },
            {
                onSuccess: () => {
                    editDialog.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            }
        )
    }

    async function confirmRemove(item) {
        deleteId.value = item.id
        if (await confirm.value.open(t('budget-provision.item'), t('default.confirm-delete-item'))) {
            removeData()
        }
    }

    function removeData() {
        isLoading.value = true
        router.delete(`/budget-provision/${deleteId.value}`, {
            onSuccess: () => {},
            onError: () => {
                isLoading.value = false
            },
            onFinish: () => {
                isLoading.value = false
            },
            preserveScroll: true,
        })
    }
</script>
