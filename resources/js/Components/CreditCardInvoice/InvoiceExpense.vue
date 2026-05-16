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
                            v-model="isClosedName"
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
                <v-row v-show="!viewOnly" dense>
                    <v-col md="12">
                        <v-btn color="primary" :disabled="isClosed" @click="newItem">{{ $t('default.new') }}</v-btn>
                        <v-btn
                            color="info"
                            class="ml-1"
                            href="/storage/template/template-despesas.xlsx"
                            download
                            :disabled="viewOnly || isClosed"
                        >
                            {{ $t('credit-card-invoice.download-template') }}
                        </v-btn>
                        <v-btn color="info" class="ml-1" :disabled="isClosed" @click="clickImportFile">{{
                            $t('credit-card-invoice.import-excel')
                        }}</v-btn>
                        <input ref="fileInput" type="file" class="d-none" accept="xlxs/*" @change="selectFile" />
                        <v-btn v-if="isClosed" color="warning" class="ml-1" @click="updateInvoice(false)">
                            {{ $t('credit-card-invoice.open-invoice') }}
                        </v-btn>
                        <v-btn v-else color="warning" class="ml-1" @click="updateInvoice(true)">{{
                            $t('credit-card-invoice.close-invoice')
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
                            :items-per-page="50"
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
                                            @click="confirmRemove(item)"
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
                                        >
                                        </VBtn>
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
                                <tr class="text-green">
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
                            <v-autocomplete
                                ref="txtDescription"
                                v-model="expense.description"
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
                        <v-col cols="12" sm="6" md="3">
                            <v-date-input
                                v-model="expense.date"
                                :label="$t('default.date')"
                                prepend-icon=""
                                prepend-inner-icon="$calendar"
                                required
                                :rules="rules.textFieldRules"
                                density="comfortable"
                                :show-adjacent-months="true"
                                :show-week="true"
                                :year="yearToDateInput"
                                :month="monthToDateInput"
                                :display-format="(date) => formatDate(date, 'DD/MM/YYYY')"
                                placeholder="DD/MM/YYYY"
                                :update-on="['enter']"
                            ></v-date-input>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="expense.value"
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
                                v-model="percentage"
                                type="number"
                                :label="$t('default.percentage-share')"
                                density="comfortable"
                                @blur="calculeShareValue"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="expense.share_value"
                                :label="$t('default.share-value')"
                                density="comfortable"
                                :rules="[
                                    (value) => {
                                        if (expense.share_user_id) {
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
                                @update:model-value="searchTag = ''"
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
                                            @click="confirmDivisionRemove(item)"
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
                                        >
                                        </VBtn>
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
                                <tr class="text-green">
                                    <th :colspan="2" class="title font-weight-bold text-right">Total</th>
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

    <ConfirmDialog ref="confirm" />

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
                            <vuetify-money
                                v-model="division.value"
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
                            <v-text-field
                                v-model="percentage_division"
                                type="number"
                                :label="$t('default.percentage-share')"
                                density="comfortable"
                                @blur="calculeShareValueDivision"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <vuetify-money
                                v-model="division.share_value"
                                :label="$t('default.share-value')"
                                density="comfortable"
                                :rules="[
                                    (value) => {
                                        if (division.share_user_id) {
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
                                @update:model-value="searchTag = ''"
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
    import { ref, computed, watch, nextTick } from 'vue'
    import { router } from '@inertiajs/vue3'
    import { useI18n } from 'vue-i18n'
    import moment from 'moment'
    import readXlsxFile from 'read-excel-file'
    import { useToast } from 'vue-toastification'
    import { currencyField, formatDate, reverseFormatNumber, sumField, sumGroup } from '@/utils/utils.js'

    import { useValidationRules } from '@/composables/useFormConstants.js'
    import { useCrudOperations } from '@/composables/useCrudOperations.js'
    import { useTagSearch } from '@/composables/useTagSearch.js'
    import { useDescriptionSearch } from '@/composables/useDescriptionSearch.js'
    import { useShareCalculation } from '@/composables/useShareCalculation.js'

    const componentProps = defineProps({
        invoice: { type: Object },
        shareUsers: { type: Array },
        budgetWeeks: { type: Array },
        titleCard: { type: Boolean, default: false },
        yearMonth: { type: String },
        viewOnly: { type: Boolean, default: false },
    })

    const { t } = useI18n()
    const toast = useToast()
    const rules = useValidationRules()

    const { isLoading, editDialog, titleModal } = useCrudOperations('/credit-card/invoice/expense')

    const { tags: listTags, isSearching: isTagSearching, searchTags: doSearchTags } = useTagSearch()
    const { descriptions: listDescriptions, isSearching: isDescriptionSearching, searchDescriptions: doSearchDescriptions } =
        useDescriptionSearch('credit-card/invoice/expense')
    const { calculateShareValue } = useShareCalculation()

    const headers = [
        { title: t('default.description'), align: 'start', key: 'description', groupable: false },
        { title: t('default.date'), key: 'date' },
        { title: t('default.value'), align: 'end', key: 'value' },
        { title: t('default.share-value'), align: 'end', key: 'share_value' },
        { title: t('default.portion'), key: 'portion' },
        { title: t('default.share-user'), key: 'share_user_id' },
        { title: t('default.remarks'), key: 'remarks' },
        { title: t('default.tags'), key: 'tags' },
        { title: t('default.action'), align: 'center', key: 'action', width: '100', sortable: false },
    ]

    const headersDivision = [
        { title: t('default.description'), align: 'start', key: 'description', groupable: false },
        { title: t('default.value'), align: 'end', key: 'value' },
        { title: t('default.share-value'), align: 'end', key: 'share_value' },
        { title: t('default.share-user'), key: 'share_user_id' },
        { title: t('default.remarks'), key: 'remarks' },
        { title: t('default.tags'), key: 'tags' },
        { title: t('default.action'), align: 'center', key: 'action', width: '100', sortable: false },
    ]

    const groupList = [
        { name: t('default.in-installments'), value: 'PORTION' },
        { name: t('default.week-1'), value: 'WEEK_1' },
        { name: t('default.week-2'), value: 'WEEK_2' },
        { name: t('default.week-3'), value: 'WEEK_3' },
        { name: t('default.week-4'), value: 'WEEK_4' },
    ]

    const panel = ref(componentProps.titleCard ? 1 : 0)
    const search = ref(null)
    const searchTag = ref('')
    const searchDescription = ref('')
    const editDivisionDialog = ref(false)
    const titleDivisionModal = ref('')
    const deleteDialog = ref(false)
    const deleteDivisionDialog = ref(false)
    const deleteId = ref(null)
    const editedIndex = ref(-1)
    const deleteAllPortions = ref(false)
    const hasDivisions = ref(false)
    const percentage = ref(null)
    const percentage_division = ref(null)

    const expense = ref({
        id: null,
        description: null,
        date: null,
        value: 0,
        group: null,
        portion: null,
        portion_total: null,
        remarks: null,
        share_value: null,
        share_user_id: null,
        invoice_id: null,
        tags: [],
        divisions: [],
    })

    const division = ref({
        id: null,
        description: null,
        value: 0,
        remarks: null,
        share_value: null,
        share_user_id: null,
        expense_id: null,
        tags: [],
    })

    // Refs for template
    const txtName = ref(null)
    const txtDescription = ref(null)
    const fileInput = ref(null)
    const form = ref(null)
    const formDivision = ref(null)
    const confirm = ref(null)
    const txtDescriptionDivision = ref(null)

    const isClosed = computed(() => (componentProps.invoice.closed ? true : false))
    const isClosedName = computed(() => (componentProps.invoice.closed ? t('default.yes') : t('default.no')))
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

    const creditCardname = computed(() => componentProps.invoice.credit_card.name)
    const invoiceDueDate = computed(() => moment(componentProps.invoice.due_date).format('DD/MM/YYYY'))
    const invoiceClosindDate = computed(() => moment(componentProps.invoice.closing_date).format('DD/MM/YYYY'))
    const invoiceTotal = computed(() => currencyField(componentProps.invoice.total))
    const invoiceTotalPaid = computed(() => currencyField(componentProps.invoice.total_paid))
    const monthToDateInput = computed(() => moment(componentProps.invoice.closing_date).subtract('40', 'days').month())
    const yearToDateInput = computed(() => moment(componentProps.invoice.closing_date).subtract('40', 'days').year())

    watch(hasDivisions, (value) => {
        if (value) {
            expense.value.tags = []
        } else {
            expense.value.divisions = []
        }
    })

    function calculeShareValue(evt) {
        if (expense.value.value) {
            expense.value.share_value = calculateShareValue(expense.value.value, evt.target.value)
        }
    }

    function calculeShareValueDivision(evt) {
        if (division.value.value) {
            division.value.share_value = calculateShareValue(division.value.value, evt.target.value)
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
        const existing = expense.value.tags ? expense.value.tags : []
        doSearchTags(val, existing)
    }

    async function searchDescriptions(val) {
        doSearchDescriptions(val)
    }

    async function selectedDescription(item) {
        if (item?.data) {
            expense.value.value = item.data.value
            expense.value.share_value = item.data.share_value
            expense.value.share_user_id = item.data.share_user_id
            expense.value.remarks = item.data.remarks
            expense.value.tags = item.data.tags
        }
    }

    async function updateInvoice(closed) {
        isLoading.value = true
        router.put(
            '/credit-card/invoice/' + componentProps.invoice.id,
            { closed: closed },
            {
                onSuccess: () => {},
                onFinish: () => {
                    isLoading.value = false
                },
            }
        )
    }

    function newItem() {
        titleModal.value = t('credit-card-invoice-expense.new-item')
        editDialog.value = true
        expense.value = {
            id: null,
            description: null,
            date: null,
            value: 0,
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
        hasDivisions.value = false
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    function editItem(item) {
        titleModal.value = t('credit-card-invoice-expense.edit-item')
        editDialog.value = true
        expense.value = {
            id: item.id,
            description: item.description,
            date: moment(item.date, 'YYYY-MM-DD'),
            value: item.value,
            group: item.group,
            portion: item.portion,
            portion_total: item.portion_total,
            remarks: item.remarks,
            share_value: item.share_value,
            invoice_id: item.invoice_id,
            share_user_id: item.share_user_id,
            tags: item.tags || [],
            divisions: item.divisions || [],
        }
        hasDivisions.value = expense.value.divisions && expense.value.divisions.length ? true : false
        nextTick(() => {
            if (txtDescription.value) txtDescription.value.focus()
        })
    }

    async function save() {
        const validate = await form.value.validate()
        if (validate.valid) {
            if (validateDivisions()) {
                if (expense.value.id) {
                    await updateData()
                } else {
                    await createData()
                }
            }
        }
    }

    async function createData() {
        isLoading.value = true
        router.post(
            '/credit-card/invoice/expense',
            {
                credit_card_id: componentProps.invoice.credit_card.id,
                invoice_id: componentProps.invoice.id,
                description: expense.value.description?.description || expense.value.description,
                date: expense.value.date.format
                    ? expense.value.date.format('YYYY-MM-DD')
                    : moment(expense.value.date).format('YYYY-MM-DD'),
                value: expense.value.value,
                group: expense.value.group,
                portion: expense.value.portion,
                portion_total: expense.value.portion_total,
                remarks: expense.value.remarks,
                share_value: expense.value.share_value,
                share_user_id: expense.value.share_user_id,
                tags: expense.value.tags,
                divisions: expense.value.divisions,
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

    async function updateData() {
        isLoading.value = true
        router.put(
            '/credit-card/invoice/expense/' + expense.value.id,
            {
                credit_card_id: componentProps.invoice.credit_card.id,
                invoice_id: componentProps.invoice.id,
                description: expense.value.description?.description || expense.value.description,
                date: expense.value.date.format
                    ? expense.value.date.format('YYYY-MM-DD')
                    : moment(expense.value.date).format('YYYY-MM-DD'),
                value: expense.value.value,
                group: expense.value.group,
                portion: expense.value.portion,
                portion_total: expense.value.portion_total,
                remarks: expense.value.remarks,
                share_value: expense.value.share_value,
                share_user_id: expense.value.share_user_id,
                tags: expense.value.tags,
                divisions: expense.value.divisions,
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

    async function confirmRemove(item) {
        deleteId.value = item.id
        deleteAllPortions.value = false
        expense.value = item
        deleteDialog.value = true
        if (await confirm.value.open(t('credit-card-invoice-expense.item'), t('default.confirm-delete-item'))) {
            remove()
        }
    }

    function remove() {
        isLoading.value = true

        if (deleteAllPortions.value) {
            router.delete('/credit-card/invoice/expense/' + deleteId.value + '/delete-all-portions', {
                onSuccess: () => {
                    deleteDialog.value = false
                },
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
        } else {
            router.delete('/credit-card/invoice/expense/' + deleteId.value, {
                onSuccess: () => {
                    deleteDialog.value = false
                },
                onError: () => {
                    isLoading.value = false
                },
                onFinish: () => {
                    isLoading.value = false
                },
                preserveScroll: true,
            })
        }
    }

    function validateDivisions() {
        if (expense.value.divisions && expense.value.divisions.length) {
            let total = 0
            expense.value.divisions.forEach((item) => {
                total += parseFloat(item.value)
            })

            if (expense.value.value != total) {
                toast.warning(t('credit-card-invoice-expense.error-total-division'))
                return false
            }

            if (expense.value.share_total) {
                let share_total = 0
                expense.value.divisions.forEach((item) => {
                    share_total += parseFloat(item.share_value)
                })

                if (expense.value.share_total != share_total) {
                    toast.warning(t('credit-card-invoice-expense.error-total-share-division'))
                    return false
                }
            }
        }
        return true
    }

    function newItemDivision() {
        titleModal.value = t('credit-card-invoice-expense.new-item-division')
        division.value = {
            id: null,
            description: null,
            value: 0,
            remarks: null,
            share_value: null,
            share_user_id: null,
            expense_id: null,
            tags: [],
        }
        editedIndex.value = -1
        editDivisionDialog.value = true
        nextTick(() => {
            if (txtDescriptionDivision.value) txtDescriptionDivision.value.focus()
        })
    }

    function editDivisionItem(item) {
        titleModal.value = t('credit-card-invoice-expense.edit-item-division')
        division.value = {
            id: item.id,
            description: item.description,
            value: item.value,
            remarks: item.remarks,
            share_value: item.share_value,
            share_user_id: item.share_user_id,
            expense_id: item.expense_id,
            tags: item.tags,
        }
        editedIndex.value = expense.value.divisions.indexOf(item)
        editDivisionDialog.value = true
    }

    async function saveDivison() {
        let validate = await formDivision.value.validate()
        if (validate.valid) {
            if (editedIndex.value > -1) {
                Object.assign(expense.value.divisions[editedIndex.value], division.value)
            } else {
                expense.value.divisions.push({ ...division.value })
            }
            editDivisionDialog.value = false
        }
    }

    async function confirmDivisionRemove(item) {
        editedIndex.value = expense.value.divisions.indexOf(item)
        if (await confirm.value.open(t('credit-card-invoice-expense.item'), t('default.confirm-delete-item'))) {
            deleteeDivision()
        }
    }

    function deleteeDivision() {
        expense.value.divisions.splice(editedIndex.value, 1)
        deleteDivisionDialog.value = false
    }

    function clickImportFile() {
        fileInput.value.click()
    }

    async function selectFile(event) {
        const file = event.target.files[0]
        if (file) {
            let data_excel = []
            await readXlsxFile(file).then(async (rows) => {
                rows.forEach((element, key) => {
                    if (key > 0) {
                        data_excel.push({
                            date: moment(element[0]).format('YYYY-MM-DD'),
                            description: element[1],
                            value: element[2],
                            share_value: element[3],
                            remarks: element[4],
                            group: convertGroupToExcel(element[5]),
                            portion: element[6],
                            portion_total: element[7],
                            share_user_id: element[8],
                            tags: element[9]
                                ? element[9].split(',').map((x) => {
                                      return { name: x.toUpperCase() }
                                  })
                                : null,
                        })
                    }
                })
            })

            if (validateImportExcel(data_excel)) {
                await importExcel(data_excel)
            }
        }
    }

    function convertGroupToExcel(group) {
        if (group) {
            if (group === 'PARCELADO') return 'PORTION'
            else if (group === 'SEMANA 1') return 'WEEK_1'
            else if (group === 'SEMANA 2') return 'WEEK_2'
            else if (group === 'SEMANA 3') return 'WEEK_3'
            else if (group === 'SEMANA 4') return 'WEEK_4'
        }
        return ''
    }

    function validateImportExcel(data_excel) {
        for (const [key, element] of Object.entries(data_excel)) {
            if (!element.description) {
                toast.error(t('credit-card-invoice-expense.excel.description', { key: Number(key) + 1 }))
                return false
            }
            if (!element.date) {
                toast.error(t('credit-card-invoice-expense.excel.date', { key: Number(key) + 1 }))
                return false
            }
            if (!element.value) {
                toast.error(t('credit-card-invoice-expense.excel.value', { key: Number(key) + 1 }))
                return false
            }
            if (!element.group) {
                toast.error(t('credit-card-invoice-expense.excel.group', { key: Number(key) + 1 }))
                return false
            }
            if (element.portion || element.portion_total) {
                if (!element.portion || element.portion <= 0) {
                    toast.error(t('credit-card-invoice-expense.excel.portion', { key: Number(key) + 1 }))
                    return false
                }
                if (!element.portion_total || element.portion_total <= 1) {
                    toast.error(t('credit-card-invoice-expense.excel.portion-total', { key: Number(key) + 1 }))
                    return false
                }
            }
            if (element.share_value || element.share_user_id) {
                if (!element.share_value) {
                    toast.error(t('credit-card-invoice-expense.excel.share-value', { key: Number(key) + 1 }))
                    return false
                }
                if (!element.share_user_id) {
                    toast.error(t('credit-card-invoice-expense.excel.share-user', { key: Number(key) + 1 }))
                    return false
                }
                if (!componentProps.shareUsers.find((x) => x.share_user_id == element.share_user_id)) {
                    toast.error(t('credit-card-invoice-expense.excel.share-user', { key: Number(key) + 1 }))
                    return false
                }
            }
        }
        return true
    }

    async function importExcel(data_excel) {
        isLoading.value = true
        router.post(
            '/credit-card/invoice/expense/import-excel',
            {
                data: data_excel,
                invoice_id: componentProps.invoice.id,
            },
            {
                onSuccess: () => {},
                onFinish: () => {
                    isLoading.value = false
                },
            }
        )
    }
</script>
