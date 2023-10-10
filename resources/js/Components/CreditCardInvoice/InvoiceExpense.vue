<template>
    <v-card class="">
        <v-card-title class="bg-primary">
            <span class="text-h6">{{ $t('credit-card-invoice.data-invoice') }}</span>
        </v-card-title>
        <v-card-text class="pt-4">
            <v-row dense>
                <v-col cols="12" sm="12" md="2">
                    <v-text-field
                        ref="txtName"
                        v-model="creditCardname"
                        :label="$t('default.credit-card')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoiceDueDate"
                        :label="$t('credit-card.due-date')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoiceClosindDate"
                        :label="$t('credit-card.closing-date')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoiceTotal"
                        :label="$t('default.total')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="invoiceTotalPaid"
                        :label="$t('default.total-paid')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6" md="2">
                    <v-text-field
                        v-model="isClosed"
                        :label="$t('default.closed')"
                        :readonly="true"
                        density="comfortable"
                    ></v-text-field>
                </v-col>
            </v-row>
            <v-row dense>
                <v-col md="12">
                    <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                    <span class="text-h6">{{ $t('credit-card-invoice-expense.title') }}</span>
                    <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                </v-col>
            </v-row>
            <v-row>
                <v-col md="12">
                    <v-btn color="primary" @click="newItem">{{ $t('default.new') }}</v-btn>
                    <v-btn color="info" class="ml-1" @click="downloadTemplate">{{
                        $t('credit-card-invoice.download-template')
                    }}</v-btn>
                    <v-btn color="info" class="ml-1" @click="importExcel">{{
                        $t('credit-card-invoice.import-excel')
                    }}</v-btn>
                </v-col>
            </v-row>
            <v-row dense>
                <v-col md="12">
                    <v-data-table
                        :group-by="[{ key: 'group', order: 'asc' }]"
                        :headers="headers"
                        :items="invoice.expenses"
                        :sort-by="[{ key: 'created_at', order: 'asc' }]"
                        :search="search"
                        :loading="isLoading"
                        :loading-text="$t('default.loading-text-table')"
                        class="elevation-3"
                        density="compact"
                        :total-items="invoice.expenses"
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
                        <template #[`item.date`]="{ item }">{{ moment(item.date).format('DD/MM/YYYY') }}</template>
                        <template #[`item.group`]="{ item }">{{ convertGroup(item.group) }}</template>
                        <template #[`item.tags`]="{ item }">{{
                            item.tags.length ? item.tags.map((x) => x.name).join(' | ') : ''
                        }}</template>
                        <template #[`item.potion`]="{ item }">{{
                            item.portion ? item.portion + '/' + item.portion_total : ''
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
                                        @click="editItem(item)"
                                    >
                                    </v-icon>
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
                                        @click="openDelete(item)"
                                    >
                                    </v-icon>
                                </template>
                            </v-tooltip>
                        </template>

                        <template #group-header="{ item, toggleGroup, isGroupOpen }">
                            <tr>
                                <th class="title" style="width: auto">
                                    <VBtn
                                        size="small"
                                        variant="text"
                                        :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                        @click="toggleGroup(item)"
                                    ></VBtn>
                                    {{ convertGroup(item.value) }}
                                </th>
                                <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">
                                    {{ sumGroup(invoice.expenses, item.key, item.value, 'value') }}
                                </th>
                                <th class="title text-right">
                                    {{ sumGroup(invoice.expenses, item.key, item.value, 'share_value') }}
                                </th>
                                <th :colspan="6"></th>
                            </tr>
                        </template>

                        <template v-if="invoice.expenses.length" #tfoot>
                            <tr class="green--text">
                                <th class="title"></th>
                                <th colspan="2" class="title font-weight-bold text-right">Total</th>
                                <th class="title text-right">{{ sumField(invoice.expenses, 'value') }}</th>
                                <th class="title text-right">{{ sumField(invoice.expenses, 'share_value') }}</th>
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
        </v-card-text>
    </v-card>

    <!-- Dialog Criacao/Edicao -->
    <v-dialog v-model="editDialog" persistent :fullscreen="true" class="ma-4">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ titleModal }}</span>
            </v-card-title>
            <v-card-text>
                <v-form ref="form" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescription"
                                v-model="expense.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.date"
                                type="date"
                                :label="$t('default.date')"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.value"
                                :label="$t('default.value')"
                                type="number"
                                min="0"
                                :rules="rules.currencyFieldRules"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.portion"
                                type="number"
                                :label="$t('default.portion')"
                                :disabled="expense.id ? true : false"
                                min="0"
                                step="1"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="expense.portion_total"
                                type="number"
                                :label="$t('default.portion-total')"
                                :disabled="expense.id ? true : false"
                                min="0"
                                step="1"
                                required
                                :rules="[
                                    (value) => {
                                        if (expense.portion) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                            if (parseFloat(value) === 1) return $t('rules.minimum-portion')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-select
                                v-model="expense.group"
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
                                v-model="expense.share_value"
                                :label="$t('default.share-value')"
                                type="number"
                                min="0"
                                :rules="[
                                    (value) => {
                                        if (expense.share_user_id) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="expense.share_user_id"
                                :label="$t('default.share-user')"
                                :items="shareUsers"
                                item-title="share_user_name"
                                item-value="share_user_id"
                                clearable
                                :rules="[
                                    (value) => {
                                        if (expense.share_value && parseFloat(expense.share_value) > 0) {
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
                                v-model="expense.remarks"
                                :label="$t('default.remarks')"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="expense.tags"
                                v-model:search="search_tag"
                                :label="$t('default.tags')"
                                :items="itemsTags"
                                :loading="loadingData"
                                item-title="name"
                                item-value="name"
                                clearable
                                multiple
                                chips
                                :closable-chips="true"
                                return-object
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                @update:search="searchTags"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                    <v-row dense>
                        <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                        <v-col md="12">
                            <span class="text-h6">{{ $t('credit-card-invoice-expense.title-division') }}</span>
                        </v-col>
                    </v-row>
                    <v-row dense>
                        <v-col md="12">
                            <v-btn color="primary" @click="newItemDivision">{{ $t('default.new') }}</v-btn>
                        </v-col>
                    </v-row>
                    <v-row dense>
                        <v-col md="12">
                            <v-data-table
                                :headers="headersDivision"
                                :items="expense.divisions"
                                :loading="isLoading"
                                :loading-text="$t('default.loading-text-table')"
                                class="elevation-3"
                                density="compact"
                                :total-items="expense.divisions"
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
                                <template #[`item.share_value`]="{ item }">{{
                                    currencyField(item.share_value)
                                }}</template>
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
                                                class="me-2"
                                                @click="editDivisionItem(item)"
                                            >
                                            </v-icon>
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
                                                @click="openDivisionDelete(item)"
                                            >
                                            </v-icon>
                                        </template>
                                    </v-tooltip>
                                </template>

                                <template #group-header="{ item, toggleGroup, isGroupOpen }">
                                    <tr>
                                        <th class="title" style="width: auto">
                                            <VBtn
                                                size="small"
                                                variant="text"
                                                :icon="isGroupOpen(item) ? '$expand' : '$next'"
                                                @click="toggleGroup(item)"
                                            ></VBtn>
                                            {{ convertGroup(item.value) }}
                                        </th>
                                        <th :colspan="2" class="title font-weight-bold text-right">Total</th>
                                        <th class="title text-right">
                                            {{ sumGroup(expense.divisions, item.key, item.value, 'value') }}
                                        </th>
                                        <th class="title text-right">
                                            {{ sumGroup(expense.divisions, item.key, item.value, 'share_value') }}
                                        </th>
                                        <th :colspan="6"></th>
                                    </tr>
                                </template>

                                <template v-if="expense.divisions.length" #tfoot>
                                    <tr class="green--text">
                                        <th class="title"></th>
                                        <th colspan="2" class="title font-weight-bold text-right">Total</th>
                                        <th class="title text-right">{{ sumField(expense.divisions, 'value') }}</th>
                                        <th class="title text-right">
                                            {{ sumField(expense.divisions, 'share_value') }}
                                        </th>
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
        <v-dialog v-model="deleteDialog" persistent width="auto">
            <v-card>
                <v-card-text>
                    <v-row>
                        <v-col md="12">
                            {{ $t('default.confirm-delete-item') }}
                        </v-col>
                        <v-col v-show="expense.portion_total > 0" md="12">
                            <v-col cols="12" md="12">
                                <v-checkbox
                                    v-model="deleteAllPortions"
                                    :label="$t('credit-card-invoice.automatic-generate')"
                                ></v-checkbox>
                            </v-col>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn color="error" elevated :loading="isLoading" @click="deleteDialog = false">
                        {{ $t('default.cancel') }}</v-btn
                    >
                    <v-btn color="primary" elevated :loading="isLoading" text @click="this.delete()">
                        {{ $t('default.delete') }}</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>

    <!-- Dialog Criacao/Edicao de Divisão -->
    <v-dialog v-model="editDivisionDialog" persistent :fullscreen="true" class="ma-4">
        <v-card>
            <v-card-title>
                <span class="text-h5">{{ titleDivisionModal }}</span>
            </v-card-title>
            <v-card-text>
                <v-form ref="form" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescription"
                                v-model="division.description"
                                :label="$t('default.description')"
                                :rules="rules.textFieldRules"
                                required
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="division.value"
                                :label="$t('default.value')"
                                type="number"
                                min="0"
                                :rules="rules.currencyFieldRules"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <v-text-field
                                v-model="division.share_value"
                                :label="$t('default.share-value')"
                                type="number"
                                min="0"
                                :rules="[
                                    (value) => {
                                        if (division.share_user_id) {
                                            if (!value) return $t('rules.required-text-field')
                                            if (parseFloat(value) <= 0) return $t('rules.required-currency-field')
                                        }
                                        return true
                                    },
                                ]"
                                density="comfortable"
                            />
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-select
                                v-model="division.share_user_id"
                                :label="$t('default.share-user')"
                                :items="shareUsers"
                                item-title="share_user_name"
                                item-value="share_user_id"
                                clearable
                                :rules="[
                                    (value) => {
                                        if (division.share_value && parseFloat(division.share_value) > 0) {
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
                                v-model="division.remarks"
                                :label="$t('default.remarks')"
                                density="comfortable"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="division.tags"
                                v-model:search="search_tag"
                                :label="$t('default.tags')"
                                :items="itemsTags"
                                :loading="loadingData"
                                item-title="name"
                                item-value="name"
                                clearable
                                multiple
                                chips
                                :closable-chips="true"
                                return-object
                                hide-no-data
                                hide-selected
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                @update:search="searchTags"
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="error" flat :loading="isLoading" @click="editDivisionDialog = false">
                    {{ $t('default.cancel') }}
                </v-btn>
                <v-btn color="primary" flat :loading="isLoading" type="submit" @click="saveDivison">
                    {{ $t('default.save') }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Dialog delete Division -->
    <v-row justify="center">
        <v-dialog v-model="deleteDivisionDialog" persistent width="auto">
            <v-card>
                <v-card-text>
                    <v-row>
                        <v-col md="12">
                            {{ $t('default.confirm-delete-item') }}
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn color="error" elevated :loading="isLoading" @click="deleteDivisionDialog = false">
                        {{ $t('default.cancel') }}</v-btn
                    >
                    <v-btn color="primary" elevated :loading="isLoading" text @click="deleteeDivision()">
                        {{ $t('default.delete') }}</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script setup>
// import { Link } from '@inertiajs/vue3'
</script>

<script>
import moment from 'moment'
import { sumField, sumGroup, currencyField } from '../../utils/utils.js'

export default {
    name: 'InvoiceExpenses',
    props: {
        invoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
    },

    setup(props) {
        return props
    },

    data() {
        return {
            headers: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.date'), key: 'date' },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.portion'), key: 'portion' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), key: 'action', width: '100', sortable: false },
            ],
            headersDivision: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), key: 'action', width: '100', sortable: false },
            ],
            rules: {
                textFieldRules: [(v) => !!v || this.$t('rules.required-text-field')],
                booleanFieldRules: [(v) => v !== null || this.$t('rules.required-text-field')],
                digitsFieldRules: [
                    (value) => {
                        if (!value) return this.$t('rules.required-text-field')
                        if (!/^\d+$/.test(value)) return this.$t('rules.only-numbers')

                        return true
                    },
                ],
                currencyFieldRules: [
                    (value) => {
                        if (!value) return this.$t('rules.required-text-field')
                        if (Number(value) <= 0) return this.$t('rules.required-currency-field')

                        return true
                    },
                ],
            },
            groupList: [
                {
                    name: this.$t('default.portion'),
                    value: 'PORTION',
                },
                {
                    name: this.$t('default.week-1'),
                    value: 'WEEK_1',
                },
                {
                    name: this.$t('default.week-2'),
                    value: 'WEEK_2',
                },
                {
                    name: this.$t('default.week-3'),
                    value: 'WEEK_3',
                },
                {
                    name: this.$t('default.week-4'),
                    value: 'WEEK_4',
                },
            ],
            search: null,
            timeOut: null,
            search_tag: '',
            editDialog: false,
            editDivisionDialog: false,
            deleteDialog: false,
            deleteDivisionDialog: false,
            isLoading: false,
            loadingData: false,
            deleteId: null,
            editedIndex: -1,
            listTags: [],
            searchFieldsData: [],
            deleteAllPortions: false,
            expense: {
                id: null,
                description: null,
                date: null,
                value: null,
                group: null,
                portion: null,
                portion_total: null,
                remarks: null,
                share_value: null,
                share_user_id: null,
                invoice_id: null,
                tags: [],
                divisions: [],
            },
            division: {
                id: null,
                description: null,
                value: null,
                remarks: null,
                share_value: null,
                share_user_id: null,
                expense_id: null,
                tags: [],
            },
        }
    },

    computed: {
        isClosed() {
            return this.invoice.closed ? this.$t('default.yes') : this.$t('default.no')
        },
        itemsTags() {
            return this.listTags
        },
        creditCardname() {
            return this.invoice.credit_card.name
        },
        itemsExpenses() {
            return this.invoice.expenses
        },
        invoiceDueDate() {
            return moment(this.invoice.due_date).format('DD/MM/YYYY')
        },
        invoiceClosindDate() {
            return moment(this.invoice.closing_date).format('DD/MM/YYYY')
        },
        invoiceTotal() {
            return currencyField(this.invoice.total)
        },
        invoiceTotalPaid() {
            return currencyField(this.invoice.total_paid)
        },
    },

    async created() {},

    async mounted() {},

    methods: {
        convertGroup(group) {
            return this.groupList.find((x) => x.value === group).name
        },

        showTags(tags) {
            if (tags.length) {
                return tags.map((x) => x.name).join('|')
            }

            return ''
        },

        async searchTags(val) {
            if (this.loadingData) return

            if (!val || val.length <= 1) {
                this.listTags = []
                clearTimeout(this.timeOut)
                return
            }

            // if (this.expense.tags && this.expense.tags.length > 0 && this.expense.tags.includes(val)) {
            //     return
            // }

            clearTimeout(this.timeOut)
            this.timeOut = setTimeout(async () => {
                this.loadingData = true
                let searchFieldsData = []
                await window.axios
                    .get('/tag/search/' + val)
                    .then(function (response) {
                        if (response.data && response.data.length > 0) {
                            searchFieldsData = response.data
                        }

                        searchFieldsData.unshift({ name: val.toUpperCase() })
                    })
                    .catch(function (error) {
                        console.log('error', error)
                    })

                this.listTags = searchFieldsData
                this.loadingData = false
            }, 300)
        },

        newItem() {
            this.titleModal = this.$t('credit-card-invoice-expense.new-item')
            this.editDialog = true
            this.expense = {
                id: null,
                description: null,
                date: null,
                value: null,
                group: null,
                portion: null,
                portion_total: null,
                remarks: null,
                share_value: null,
                invoice_id: null,
                share_user_id: null,
                tags: [],
                divisions: [],
            }
            setTimeout(() => {
                this.$refs.txtName.focus()
            })
        },

        editItem(item) {
            this.titleModal = this.$t('credit-card-invoice-expense.edit-item')
            this.editDialog = true
            this.expense = {
                id: item.id,
                description: item.description,
                date: item.date,
                value: item.value,
                group: item.group,
                portion: item.portion,
                portion_total: item.portion_total,
                remarks: item.remarks,
                share_value: item.share_value,
                invoice_id: item.invoice_id,
                share_user_id: item.share_user_id,
                tags: item.tags,
                divisions: item.divisions,
            }
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.expense.id) {
                    await this.update()
                } else {
                    await this.create()
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/credit-card/' + this.invoice.credit_card.id + '/invoice/' + this.invoice.id + '/expense',
                {
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    invoice_id: this.invoice.id,
                    tags: this.expense.tags,
                    divisions: this.expense.divisions,
                },
                {
                    only: ['invoice'],
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
                '/credit-card/' +
                    this.invoice.credit_card.id +
                    '/invoice/' +
                    this.invoice.id +
                    '/expense/' +
                    this.expense.id,
                {
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    invoice_id: this.invoice.id,
                    tags: this.expense.tags,
                    divisions: this.expense.divisions,
                },
                {
                    only: ['invoice'],
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
            this.deleteAllPortions = false
            this.expense = item
            this.deleteDialog = true
        },

        delete() {
            this.isLoading = true

            if (this.deleteAllPortions) {
                this.$inertia.delete(
                    '/credit-card/' +
                        this.invoice.credit_card.id +
                        '/invoice/' +
                        this.invoice.id +
                        '/expense/' +
                        this.deleteId +
                        '/delete-all-portions',
                    {
                        onSuccess: () => {
                            this.deleteDialog = false
                            this.editDialog = false
                        },
                        onError: () => {
                            this.isLoading = false
                        },
                        onFinish: () => {
                            this.isLoading = false
                        },
                    }
                )
            } else {
                this.$inertia.delete(
                    '/credit-card/' +
                        this.invoice.credit_card.id +
                        '/invoice/' +
                        this.invoice.id +
                        '/expense/' +
                        this.deleteId,
                    {
                        onSuccess: () => {
                            this.deleteDialog = false
                            this.editDialog = false
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

        downloadTemplate() {},

        importExcel() {},

        // Metodos para a divisão da despesa
        newItemDivision() {
            this.titleDivisionModal = this.$t('credit-card-invoice-expense.new-item-division')
            this.division = {
                id: null,
                description: null,
                value: null,
                remarks: null,
                share_value: null,
                share_user_id: null,
                expense_id: null,
                tags: [],
            }
            this.editedIndex = -1
            this.editDivisionDialog = true
        },

        editDivisionItem(item) {
            this.titleDivisionModal = this.$t('credit-card-invoice-expense.edit-item-division')
            this.division = {
                id: item.id,
                description: item.description,
                value: item.value,
                remarks: item.remarks,
                share_value: item.share_value,
                share_user_id: item.share_user_id,
                expense_id: item.expense_id,
                tags: item.tags,
            }
            this.editedIndex = this.expense.divisions.indexOf(item)
            this.editDivisionDialog = true
        },

        saveDivison() {
            if (this.editedIndex > -1) {
                Object.assign(this.expense.divisions[this.editedIndex], this.division)
            } else {
                this.expense.divisions.push(this.division)
            }
            this.editDivisionDialog = false
        },

        openDivisionDelete(item) {
            this.editedIndex = this.expense.divisions.indexOf(item)
            this.deleteDivisionDialog = true
        },

        deleteeDivision() {
            this.expense.divisions.splice(this.editedIndex, 1)
            this.deleteDivisionDialog = false
        },
    },
}
</script>
