<template>
    <v-expansion-panels v-model="panel" class="mt-3">
        <v-expansion-panel>
            <v-expansion-panel-header>
                {{ $t('group-resumes.title') }}
            </v-expansion-panel-header>
        </v-expansion-panel>
    </v-expansion-panels>
</template>

<script setup>
    import { ref } from 'vue'

    defineOptions({ name: 'GroupResumes' })

    defineProps({
        itens: {
            type: Object,
        },
    })

    const panel = ref(1)
</script>
