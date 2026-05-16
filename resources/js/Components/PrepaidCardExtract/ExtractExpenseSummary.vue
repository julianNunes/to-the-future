<template>
    <v-row dense>
        <v-col cols="12" sm="12" md="2">
            <v-text-field
                :model-value="prepaidCardName"
                :label="$t('prepaid-card.item')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" md="2">
            <v-text-field
                :model-value="yearMonth"
                :label="$t('prepaid-card-extract.year-month')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="creditDate"
                :label="$t('prepaid-card-extract.credit-date')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
        <v-col cols="12" sm="6" md="2">
            <v-text-field
                :model-value="credit"
                :label="$t('prepaid-card-extract.credit')"
                :readonly="true"
                density="comfortable"
            />
        </v-col>
    </v-row>
</template>

<script setup>
    defineOptions({ name: 'ExtractExpenseSummary' })

    defineProps({
        prepaidCardName: { type: String, required: true },
        yearMonth: { type: String, required: true },
        creditDate: { type: String, required: true },
        credit: { type: String, required: true },
    })
</script>
