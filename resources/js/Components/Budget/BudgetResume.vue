<template>
    <!-- Tabela com dados -->
    <v-expansion-panels v-model="panel" class="mt-2">
        <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary">
                <span class="text-h6">{{ $t('budget-resume.title') }}</span>
            </v-expansion-panel-title>
            <v-expansion-panel-text class="pa-2">
                <v-row dense>
                    <v-col md="3">
                        <v-text-field
                            v-model="resumeTotalExpense"
                            :label="$t('budget-resume.total-expense')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                        <v-text-field
                            v-model="resumeTotalIncome"
                            :label="$t('budget-resume.total-incomes')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                        <v-text-field
                            v-model="resumeBalance"
                            :label="$t('budget-resume.balance')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-col md="3">
                        <v-text-field
                            v-model="resumePayShare"
                            :label="$t('budget-resume.pay-share')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                        <v-text-field
                            v-model="resumeReceiveShare"
                            :label="$t('budget-resume.receive-share')"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                        <v-text-field
                            v-model="resumeBalanceShare"
                            :label="resumeBalanceShareLabel"
                            :readonly="true"
                            density="comfortable"
                        ></v-text-field>
                    </v-col>
                    <v-divider class="border-opacity-100" vertical></v-divider>
                    <v-col md="6">
                        <v-table density="compact">
                            <thead>
                                <tr>
                                    <th class="text-left"></th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">{{ $t('default.share-value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in itemsResumeCreditCard" :key="item.text">
                                    <td>{{ $t(item.name) }}</td>
                                    <td class="text-right">{{ currencyField(item.total_value) }}</td>
                                    <td class="text-right">{{ currencyField(item.total_share_value) }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-row>
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels>
</template>

<script setup>
import { currencyField } from '../../utils/utils.js'
</script>

<script>
export default {
    name: 'BudgetResume',
    props: {
        resume: {
            type: Object,
        },
    },

    data() {
        return {
            panel: 1,
        }
    },

    computed: {
        resumeTotalExpense() {
            return currencyField(this.resume.total_expense)
        },
        resumeTotalIncome() {
            return currencyField(this.resume.total_income)
        },
        resumeBalance() {
            return currencyField(this.resume.balance)
        },
        resumePayShare() {
            return currencyField(this.resume.pay_share)
        },
        resumeReceiveShare() {
            return currencyField(this.resume.receive_share)
        },
        resumeBalanceShare() {
            return currencyField(this.resume.balance_share)
        },
        resumeBalanceShareLabel() {
            return this.resume.balance_share < 0
                ? this.$t('budget-resume.value-to-pay')
                : this.$t('budget-resume.value-to-receive')
        },
        itemsResumeCreditCard() {
            return this.resume.resume_credit_card
        },
    },

    async created() {},

    async mounted() {},
}
</script>
