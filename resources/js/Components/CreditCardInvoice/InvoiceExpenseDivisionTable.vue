<template>
    <v-row dense>
        <v-col md="12">
            <v-data-table
                :headers="headers"
                :items="divisions"
                :loading-text="$t('default.loading-text-table')"
                class="elevation-3"
                density="compact"
                :total-items="divisions.length"
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
                            {{ sumGroup(divisions, item.key, item.value, 'value') }}
                        </th>
                        <th class="title text-right">
                            {{ sumGroup(divisions, item.key, item.value, 'share_value') }}
                        </th>
                        <th :colspan="6"></th>
                    </tr>
                </template>

                <template v-if="divisions.length" #tfoot>
                    <tr class="text-green">
                        <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                        <th class="title text-right">{{ sumField(divisions, 'value') }}</th>
                        <th class="title text-right">
                            {{ sumField(divisions, 'share_value') }}
                        </th>
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
    import { currencyField, sumField, sumGroup } from '@/utils/utils.js';

    defineOptions({ name: 'InvoiceExpenseDivisionTable' })

    defineProps({
        headers: { type: Array, required: true },
        divisions: { type: Array, default: () => [] },
        search: { type: String, default: null },
        groupLabel: { type: Function, required: true },
    })

    defineEmits(['edit', 'remove', 'update:search'])
</script>
