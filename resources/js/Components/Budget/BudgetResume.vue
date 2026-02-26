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
    import { ref, computed } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { currencyField } from '@/utils/utils.js'

    defineOptions({ name: 'BudgetResume' })

    const props = defineProps({
        resume: {
            type: Object,
            default: () => ({}),
        },
    })

    const { t } = useI18n()

    const panel = ref(1)

    const resumeTotalExpense = computed(() => currencyField(props.resume.total_expense))
    const resumeTotalIncome = computed(() => currencyField(props.resume.total_income))
    const resumeBalance = computed(() => currencyField(props.resume.balance))
    const resumePayShare = computed(() => currencyField(props.resume.pay_share))
    const resumeReceiveShare = computed(() => currencyField(props.resume.receive_share))
    const resumeBalanceShare = computed(() => currencyField(props.resume.balance_share))

    const resumeBalanceShareLabel = computed(() => {
        return props.resume.balance_share < 0 ? t('budget-resume.value-to-pay') : t('budget-resume.value-to-receive')
    })

    const itemsResumeCreditCard = computed(() => props.resume.resume_credit_card)
</script>
