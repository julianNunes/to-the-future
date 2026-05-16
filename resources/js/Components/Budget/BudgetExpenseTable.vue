<template>
    <v-row dense>
        <v-col v-if="!viewOnly" md="12">
            <v-btn color="primary" @click="$emit('new')">{{ $t('default.new') }}</v-btn>
        </v-col>
        <v-col md="12">
            <v-data-table
                :headers="headers"
                :items="expenses"
                :search="search"
                :loading="isLoading"
                :loading-text="$t('default.loading-text-table')"
                class="elevation-3"
                density="compact"
                :total-items="expenses.length"
                :items-per-page="25"
                :no-data-text="$t('default.no-data-text')"
                :no-results-text="$t('default.no-data-text')"
                :footer-props="{
                    'items-per-page-text': $t('default.itens-per-page'),
                    'page-text': $t('default.page-text'),
                }"
                :header-props="{
                    sortByText: $t('default.sort-by'),
                }"
                :row-props="rowProps"
                fixed-header
            >
                <template #[`item.description`]="{ item }">
                    {{ item.description?.match(/(\S+)\.(\S+)/gm) ? $t(item.description) : item.description }}
                </template>
                <template #[`item.date`]="{ item }">
                    {{ item.date ? moment(item.date).format('DD/MM/YYYY') : null }}
                </template>
                <template #[`item.value`]="{ item }">{{ currencyField(item.value) }}</template>
                <template #[`item.share_value`]="{ item }">
                    {{ currencyField(item.share_value) }}
                </template>
                <template #[`item.group`]="{ item }">
                    {{ item.group ? groupLabel(item.group) : null }}
                </template>
                <template #[`item.remarks`]="{ item }">
                    {{ item.remarks?.match(/(\S+)\.(\S+)/gm) ? $t(item.remarks) : item.remarks }}
                </template>
                <template #[`item.paid`]="{ item }">
                    {{ item.paid === null ? null : item.paid == true ? $t('default.paid') : $t('default.open') }}
                </template>
                <template #[`item.tags`]="{ item }">
                    {{ item.tags.length ? item.tags.map((tag) => tag.name).join(' | ') : '' }}
                </template>
                <template #[`item.share_user_id`]="{ item }">
                    {{ item.share_user ? item.share_user.name : '' }}
                </template>
                <template #[`item.action`]="{ item }">
                    <v-tooltip v-if="item.id" :text="$t('default.edit')" location="top">
                        <template #activator="{ props }">
                            <v-icon
                                v-bind="props"
                                color="warning"
                                icon="mdi-pencil"
                                size="small"
                                @click="$emit('edit', item)"
                            />
                        </template>
                    </v-tooltip>
                    <v-tooltip v-if="item.id" :text="$t('default.delete')" location="top">
                        <template #activator="{ props }">
                            <v-icon
                                v-bind="props"
                                class="ml-1"
                                color="error"
                                icon="mdi-delete"
                                size="small"
                                @click="$emit('remove', item)"
                            />
                        </template>
                    </v-tooltip>
                </template>

                <template v-if="expenses.length" #tfoot>
                    <tr class="text-green">
                        <th class="title"></th>
                        <th class="title font-weight-bold text-right">Total</th>
                        <th class="title text-right">
                            {{ sumField(expenses, 'value') }}
                        </th>
                        <th class="title text-right">
                            {{ sumField(expenses, 'share_value') }}
                        </th>
                    </tr>
                </template>

                <template #[`item.data-table-expand`]="{ item, internalItem, isExpanded, toggleExpand }">
                    <v-btn
                        v-if="item.financing_installment"
                        color="black"
                        size="small"
                        :icon="isExpanded(internalItem) ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                        variant="text"
                        @click="toggleExpand(internalItem)"
                    />
                </template>

                <template #expanded-row="{ columns, item }">
                    <tr>
                        <td :colspan="columns.length">
                            {{ installmentLabel(item.financing_installment) }}
                        </td>
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
    import { currencyField, sumField } from '@/utils/utils.js'
import moment from 'moment'

    defineOptions({ name: 'BudgetExpenseTable' })

    defineProps({
        headers: { type: Array, required: true },
        expenses: { type: Array, default: () => [] },
        search: { type: String, default: null },
        isLoading: { type: Boolean, default: false },
        viewOnly: { type: Boolean, default: false },
        rowProps: { type: Function, required: true },
        groupLabel: { type: Function, required: true },
        installmentLabel: { type: Function, required: true },
    })

    defineEmits(['new', 'edit', 'remove', 'update:search'])
</script>
