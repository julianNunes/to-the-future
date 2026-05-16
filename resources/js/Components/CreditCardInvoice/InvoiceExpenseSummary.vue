<template>
    <v-row dense>
        <v-col cols="12" sm="12" md="2">
            <v-text-field
                :model-value="creditCardName"
                :label="$t('default.credit-card')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="dueDate"
                :label="$t('credit-card.due-date')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="closingDate"
                :label="$t('credit-card.closing-date')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field :model-value="total" :label="$t('default.total')" :readonly="true" density="comfortable" />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="totalPaid"
                :label="$t('default.total-paid')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="closedName"
                :label="$t('default.closed')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
    </v-row>
</template>

<script setup>
    defineOptions({ name: 'InvoiceExpenseSummary' })

    defineProps({
        creditCardName: { type: String, required: true },
        dueDate: { type: String, required: true },
        closingDate: { type: String, required: true },
        total: { type: String, required: true },
        totalPaid: { type: String, required: true },
        closedName: { type: String, required: true },
    })
</script>
