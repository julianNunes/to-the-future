<template>
    <v-row v-show="!viewOnly" dense>
        <v-col md="12">
            <v-btn color="primary" :disabled="isClosed" @click="$emit('new')">{{ $t('default.new') }}</v-btn>
            <v-btn
                color="info"
                class="ml-1"
                href="/storage/template/template-despesas.xlsx"
                download
                :disabled="viewOnly || isClosed"
            >
                {{ $t('credit-card-invoice.download-template') }}
            </v-btn>
            <v-btn color="info" class="ml-1" :disabled="isClosed" @click="clickImportFile">
                {{ $t('credit-card-invoice.import-excel') }}
            </v-btn>
            <input ref="fileInput" type="file" class="d-none" accept="xlxs/*" @change="$emit('select-file', $event)" />
            <v-btn v-if="isClosed" color="warning" class="ml-1" @click="$emit('update-invoice', false)">
                {{ $t('credit-card-invoice.open-invoice') }}
            </v-btn>
            <v-btn v-else color="warning" class="ml-1" @click="$emit('update-invoice', true)">
                {{ $t('credit-card-invoice.close-invoice') }}
            </v-btn>
        </v-col>
    </v-row>
</template>

<script setup>
    import { ref } from 'vue'

    defineOptions({ name: 'InvoiceExpenseActions' })

    defineProps({
        viewOnly: { type: Boolean, default: false },
        isClosed: { type: Boolean, default: false },
    })

    defineEmits(['new', 'select-file', 'update-invoice'])

    const fileInput = ref(null)

    function clickImportFile() {
        fileInput.value?.click()
    }

    defineExpose({ clickImportFile })
</script>
