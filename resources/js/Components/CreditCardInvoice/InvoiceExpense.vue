<template>
    <!-- Dados do cartão -->
    <v-expansion-panels v-model="panel" :readonly="!titleCard" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <template #default="{ expanded }">
                    <v-row no-gutters>
                        <span class="text-h6">
                            {{ $t('credit-card-invoice.data-invoice') }}
                            {{ !expanded && titleCard ? ' - ' + creditCardname : '' }}
                        </span>
                    </v-row>
                </template>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-4">
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
                <!-- <v-row dense>
                    <v-col md="12">
                        <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                        <span class="text-h6">{{ $t('credit-card-invoice-expense.title') }}</span>
                        <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                    </v-col>
                </v-row> -->
                <v-row dense>
                    <v-col md="12">
                        <v-btn color="primary" :disabled="viewOnly" @click="newItem">{{ $t('default.new') }}</v-btn>
                        <v-btn
                            color="info"
                            class="ml-1"
                            href="/storage/template/template-despesas.xlsx"
                            download
                            :disabled="viewOnly"
                        >
                            {{ $t('credit-card-invoice.download-template') }}
                        </v-btn>
                        <v-btn color="info" class="ml-1" :disabled="viewOnly" @click="clickImportFile">{{
                            $t('credit-card-invoice.import-excel')
                        }}</v-btn>
                        <input ref="fileInput" type="file" class="d-none" accept="xlxs/*" @change="selectFile" />
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
                            <template #[`item.portion`]="{ item }">{{
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
                                            :disabled="viewOnly"
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
                                            :disabled="viewOnly"
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
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels>

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
                                v-model:search="searchTag"
                                :label="$t('default.tags')"
                                :items="itemsTags"
                                :loading="loadingData"
                                item-title="name"
                                item-value="name"
                                :disabled="hasDivisions"
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
                <!-- Divisão das Despesas -->
                <v-row dense>
                    <v-col md="12">
                        <v-checkbox
                            v-model="hasDivisions"
                            :label="$t('credit-card-invoice-expense.has-divisions')"
                        ></v-checkbox>
                    </v-col>
                </v-row>
                <v-row v-show="hasDivisions" dense>
                    <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                    <v-col md="12">
                        <span class="text-h6">{{ $t('credit-card-invoice-expense.title-division') }}</span>
                    </v-col>
                    <v-divider :thickness="3" class="border-opacity-90" color="black"></v-divider>
                </v-row>
                <v-row v-show="hasDivisions">
                    <v-col md="12">
                        <v-btn color="primary" @click="newItemDivision">{{ $t('default.new') }}</v-btn>
                    </v-col>
                </v-row>
                <v-row v-show="hasDivisions" dense>
                    <v-col md="12">
                        <v-data-table
                            :headers="headersDivision"
                            :items="expense.divisions"
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
                            <template #[`item.share_value`]="{ item }">{{ currencyField(item.share_value) }}</template>
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
                                    <th class="title font-weight-bold text-right">Total</th>
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
    <v-row v-if="deleteDialog" justify="center">
        <v-dialog v-model="deleteDialog" persistent width="800">
            <v-card>
                <v-card-text>
                    <v-row>
                        <v-col md="12">
                            <span class="text-h6">{{ $t('default.confirm-delete-item') }}</span>
                        </v-col>
                        <v-col v-show="expense.portion_total > 0" md="12">
                            <v-checkbox
                                v-model="deleteAllPortions"
                                :label="$t('credit-card-invoice-expense.delete-all-portions')"
                            ></v-checkbox>
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
                <v-form ref="formDivision" @submit.prevent>
                    <v-row dense>
                        <v-col cols="12" sm="12" md="12">
                            <v-text-field
                                ref="txtDescriptionDivision"
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
    <v-row v-if="deleteDivisionDialog" justify="center">
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
                    <v-btn color="error" elevated :loading="isLoading" text @click="deleteDivisionDialog = false">
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
import { useToast } from 'vue-toastification'
import { sumField, sumGroup, currencyField } from '../../utils/utils.js'
import readXlsxFile from 'read-excel-file'

export default {
    name: 'InvoiceExpenses',
    props: {
        invoice: {
            type: Object,
        },
        shareUsers: {
            type: Array,
        },
        budgetWeeks: {
            type: Array,
        },
        titleCard: {
            type: Boolean,
            default: false,
        },
        yearMonth: {
            type: String,
        },
        viewOnly: {
            type: Boolean,
            default: false,
        },
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
                { title: this.$t('default.action'), align: 'center', key: 'action', width: '100', sortable: false },
            ],
            headersDivision: [
                { title: this.$t('default.description'), align: 'start', key: 'description', groupable: false },
                { title: this.$t('default.value'), align: 'end', key: 'value' },
                { title: this.$t('default.share-value'), align: 'end', key: 'share_value' },
                { title: this.$t('default.share-user'), key: 'share_user_id' },
                { title: this.$t('default.remarks'), key: 'remarks' },
                { title: this.$t('default.tags'), key: 'tags' },
                { title: this.$t('default.action'), align: 'center', key: 'action', width: '100', sortable: false },
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
                    name: this.$t('default.in-installments'),
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
            toast: null,
            panel: this.titleCard ? 1 : 0,
            search: null,
            timeOut: null,
            searchTag: '',
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
            hasDivisions: false,
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

    watch: {
        hasDivisions(value) {
            if (value) {
                this.expense.tags = []
            } else {
                this.expense.divisions = []
            }
        },
    },

    async created() {},

    async mounted() {
        this.toast = useToast()
    },

    methods: {
        convertGroup(group) {
            console.log('this.budgetWeeks', this.budgetWeeks)
            if (this.budgetWeeks?.length && this.budgetWeeks.find((x) => x.value === group)) {
                return (
                    this.groupList.find((x) => x.value === group).name +
                    ' (' +
                    this.budgetWeeks.find((x) => x.value === group).text +
                    ')'
                )
            }

            return this.groupList.find((x) => x.value === group).name
        },

        async searchTags(val) {
            if (this.loadingData) return

            if (!val || val.length <= 1) {
                this.listTags = []
                clearTimeout(this.timeOut)
                return
            }

            if (this.expense.tags && this.expense.tags.length > 0 && this.expense.tags.find((x) => x.name == val)) {
                return
            }

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
                date: this.yearMonth ? this.yearMonth + '-01' : null,
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
            this.hasDivisions = false
            setTimeout(() => {
                this.$refs.txtDescription.focus()
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
            this.hasDivisions = this.expense.divisions && this.expense.divisions.length ? true : false
            setTimeout(() => {
                this.$refs.txtDescription.focus()
            })
        },

        async save() {
            let validate = await this.$refs.form.validate()
            if (validate.valid) {
                if (this.validateDivisions()) {
                    if (this.expense.id) {
                        await this.update()
                    } else {
                        await this.create()
                    }
                }
            }
        },

        async create() {
            this.isLoading = true
            this.$inertia.post(
                '/credit-card/invoice/expense',
                {
                    credit_card_id: this.invoice.credit_card.id,
                    invoice_id: this.invoice.id,
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    tags: this.expense.tags,
                    divisions: this.expense.divisions,
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
                '/credit-card/invoice/expense/' + this.expense.id,
                {
                    credit_card_id: this.invoice.credit_card.id,
                    invoice_id: this.invoice.id,
                    description: this.expense.description,
                    date: this.expense.date,
                    value: this.expense.value,
                    group: this.expense.group,
                    portion: this.expense.portion,
                    portion_total: this.expense.portion_total,
                    remarks: this.expense.remarks,
                    share_value: this.expense.share_value,
                    share_user_id: this.expense.share_user_id,
                    tags: this.expense.tags,
                    divisions: this.expense.divisions,
                },
                {
                    // only: ['invoice'],
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
                this.$inertia.delete('/credit-card/invoice/expense/' + this.deleteId + '/delete-all-portions', {
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
                })
            } else {
                this.$inertia.delete('/credit-card/invoice/expense/' + this.deleteId, {
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
                })
            }
        },

        downloadTemplate() {
            fetch('/credit-card/invoice/download-template')
                .then((res) => res.blob())
                .then((blob) => {
                    const file = window.URL.createObjectURL(blob)
                    window.location.assign(file)
                })
        },

        clickImportFile() {
            console.log('clickImportFile')
            console.log(this.$refs.fileInput)
            this.$refs.fileInput.click()
        },

        async selectFile(event) {
            const file = event.target.files[0]
            if (file) {
                let data_excel = []

                await readXlsxFile(file).then(async (rows) => {
                    rows.forEach((element, key) => {
                        console.log('element', element)
                        if (key > 0) {
                            data_excel.push({
                                description: element[0],
                                date: element[1],
                                value: element[2],
                                group: this.convertGroupToExcel(element[3]),
                                portion: element[4],
                                portion_total: element[5],
                                share_value: element[6],
                                share_user_name: element[7],
                                remarks: element[8],
                                tags: element[9] ? element[9].split(',') : null,
                            })
                        }
                    })
                })

                if (this.validateImportExcel(data_excel)) {
                    console.log('data_excel', data_excel)
                    this.importExcel(data_excel)
                }
            }
        },

        convertGroupToExcel(group) {
            if (group) {
                if (group === 'PARCELADO') return 'PORTION'
                else if (group === 'SEMANA 1') return 'WEEK_1'
                else if (group === 'SEMANA 2') return 'WEEK_2'
                else if (group === 'SEMANA 3') return 'WEEK_4'
                else if (group === 'SEMANA 4') return 'WEEK_5'
            }
            return ''
        },

        validateImportExcel(data_excel) {
            data_excel.forEach((element, key) => {
                // Description
                if (!element.description) {
                    this.toast.error(this.$tc('credit-card-invoice-expense.excel.description', { key: key + 1 }))
                    return false
                }

                // Date
                if (!element.date) {
                    this.toast.error(this.$tc('credit-card-invoice-expense.excel.date', { key: key + 1 }))
                    return false
                }

                // Value
                if (!element.value) {
                    this.toast.error(this.$tc('credit-card-invoice-expense.excel.date', { key: key + 1 }))
                    return false
                }

                // group
                if (!element.group) {
                    this.toast.error(this.$tc('credit-card-invoice-expense.excel.group', { key: key + 1 }))
                    return false
                }

                // portion and portion_total
                if (element.portion || element.portion_total) {
                    if (!element.portion || element.portion <= 0) {
                        this.toast.error(this.$tc('credit-card-invoice-expense.excel.portion', { key: key + 1 }))
                        return false
                    }

                    if (!element.portion_total || element.portion_total <= 1) {
                        this.toast.error(this.$tc('credit-card-invoice-expense.excel.portion-total', { key: key + 1 }))
                        return false
                    }
                }

                // share_value and share_user_name
                if (element.share_value || element.share_user_name) {
                    if (!element.share_value || element.share_value <= 0) {
                        this.toast.error(this.$tc('credit-card-invoice-expense.excel.share-value', { key: key + 1 }))
                        return false
                    }

                    if (!element.share_user_name) {
                        this.toast.error(this.$tc('credit-card-invoice-expense.excel.share-user', { key: key + 1 }))
                        return false
                    }
                }
            })

            return true
        },

        async importExcel(data_excel) {
            console.log('data_excel', data_excel)
            // this.isLoading = true
            // this.$inertia.post(
            //     '/credit-card/invoice/expense-import-excel',
            //     {
            //         data: data_excel,
            //         invoice_id: this.invoice.id,
            //     },
            //     {
            //         onSuccess: () => {
            //             this.editDialog = false
            //         },
            //         onFinish: () => {
            //             this.isLoading = false
            //         },
            //     }
            // )
        },

        // Metodos para validação da divisão da despesa
        validateDivisions() {
            if (this.expense.divisions && this.expense.divisions.length) {
                let total = 0

                this.expense.divisions.forEach((item) => {
                    total += parseFloat(item.value)
                })

                if (this.expense.value != total) {
                    this.toast.warning(this.$t('credit-card-invoice-expense.error-total-division'))
                    return false
                }

                if (this.expense.share_total) {
                    let share_total = 0

                    this.expense.divisions.forEach((item) => {
                        share_total += parseFloat(item.share_value)
                    })

                    if (this.expense.share_total != share_total) {
                        this.toast.warning(this.$t('credit-card-invoice-expense.error-total-share-division'))
                        return false
                    }
                }
            }

            return true
        },

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
            setTimeout(() => {
                this.$refs.txtDescriptionDivision.focus()
            })
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

        async saveDivison() {
            let validate = await this.$refs.formDivision.validate()
            if (validate.valid) {
                if (this.editedIndex > -1) {
                    Object.assign(this.expense.divisions[this.editedIndex], this.division)
                } else {
                    this.expense.divisions.push(this.division)
                }
                this.editDivisionDialog = false
            }
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
