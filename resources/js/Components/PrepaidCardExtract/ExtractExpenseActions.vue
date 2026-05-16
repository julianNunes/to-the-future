<template>
    <v-row dense>
        <v-col md="12">
            <v-btn color="primary" :disabled="viewOnly" @click="$emit('new')">
                {{ $t('default.new') }}
            </v-btn>
            <v-btn
                color="info"
                class="ml-1"
                href="/storage/template/template-prepaid-card.xlsx"
                download
                :disabled="viewOnly"
            >
                {{ $t('prepaid-card-extract-expense.download-template') }}
            </v-btn>
            <v-btn color="info" class="ml-1" :disabled="viewOnly" @click="clickImportFile">
                {{ $t('prepaid-card-extract-expense.import-excel') }}
            </v-btn>
            <input
                ref="fileInput"
                type="file"
                class="d-none"
                accept=".xlsx,.xls"
                @change="$emit('select-file', $event)"
            />
            <v-btn v-if="viewOnly" color="warning" class="ml-1" @click="$emit('open')">
                {{ $t('prepaid-card-extract-expense.open-extract') }}
            </v-btn>
        </v-col>
    </v-row>
</template>

<script setup>
    import { ref } from 'vue'

    defineOptions({ name: 'ExtractExpenseActions' })

    defineProps({
        viewOnly: { type: Boolean, default: false },
    })

    defineEmits(['new', 'select-file', 'open'])

    const fileInput = ref(null)

    function clickImportFile() {
        fileInput.value?.click()
    }

    defineExpose({ clickImportFile })
</script>
