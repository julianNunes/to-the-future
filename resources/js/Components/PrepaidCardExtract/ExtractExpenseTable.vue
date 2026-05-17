<template>
    <v-row dense>
        <v-col md="12">
            <v-data-table
                :group-by="[{ key: 'group', order: 'asc' }]"
                :headers="headers"
                :items="expenses"
                :sort-by="[{ key: 'created_at', order: 'asc' }]"
                :search="search"
                :loading="isLoading"
                :loading-text="$t('default.loading-text-table')"
                class="elevation-3"
                density="compact"
                :total-items="expenses.length"
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
                <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                <template #[`item.share_value`]="{ item }">{{ currencyField(item.share_value) }}</template>
                <template #[`item.date`]="{ item }">{{ moment(item.date).format('DD/MM/YYYY') }}</template>
                <template #[`item.group`]="{ item }">{{ groupLabel(item.group) }}</template>
                <template #[`item.tags`]="{ item }">{{
                    item.tags.length ? item.tags.map((tag) => tag.name).join(' | ') : ''
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
                                class="me-2"
                                :disabled="viewOnly"
                                @click="$emit('edit', item)"
                            />
                        </template>
                    </v-tooltip>
                    <v-tooltip :text="$t('default.delete')" location="top">
                        <template #activator="{ props }">
                            <v-icon
                                v-bind="props"
                                class="ml-2"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                :disabled="viewOnly"
                                @click="$emit('remove', item)"
                            />
                        </template>
                    </v-tooltip>
                </template>

                <template #group-header="{ item, toggleGroup, isGroupOpen }">
                    <tr>
                        <th class="title" style="width: auto">
                            <v-btn
                                size="small"
                                variant="text"
                                :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                @click="toggleGroup(item)"
                            />
                            {{ groupLabel(item.value) }}
                        </th>
                        <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                        <th class="title text-right">
                            {{ sumGroup(expenses, item.key, item.value, 'value') }}
                        </th>
                        <th class="title text-right">
                            {{ sumGroup(expenses, item.key, item.value, 'share_value') }}
                        </th>
                        <th :colspan="6"></th>
                    </tr>
                </template>

                <template v-if="expenses.length" #tfoot>
                    <tr class="text-green">
                        <th class="title"></th>
                        <th colspan="2" class="title font-weight-bold text-right">Total</th>
                        <th class="title text-right">{{ sumField(expenses, 'value') }}</th>
                        <th class="title text-right">{{ sumField(expenses, 'share_value') }}</th>
                    </tr>
                </template>

                <template #top>
                    <v-toolbar density="comfortable">
                        <v-row dense>
                            <v-col cols="12" lg="12" md="12" sm="12">
                                <v-text-field
                                    :model-value="search"
                                    :label="$t('default.search')"
                                    append-icon="mdi-magnify"
                                    single-line
                                    hide-details
                                    clearable
                                    @update:model-value="$emit('update:search', $event)"
                                    @click:clear="$emit('update:search', null)"
                                />
                            </v-col>
                        </v-row>
                    </v-toolbar>
                </template>
            </v-data-table>
        </v-col>
    </v-row>
</template>

<script setup>
    import { currencyField, sumField, sumGroup } from '@/utils/utils.js'
    import moment from 'moment'

    defineOptions({ name: 'ExtractExpenseTable' })

    defineProps({
        headers: { type: Array, required: true },
        expenses: { type: Array, default: () => [] },
        search: { type: String, default: null },
        isLoading: { type: Boolean, default: false },
        viewOnly: { type: Boolean, default: false },
        groupLabel: { type: Function, required: true },
    })

    defineEmits(['edit', 'remove', 'update:search'])
</script>
