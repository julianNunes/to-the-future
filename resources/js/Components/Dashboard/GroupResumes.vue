<template>
    <v-expansion-panels v-model="panel" class="mt-3">
        <v-expansion-panel>
            <v-expansion-panel-header>
                {{ $t('group-resumes.title') }}
            </v-expansion-panel-header>
        </v-expansion-panel>
    </v-expansion-panels>
</template>

<script setup></script>

<script>
    export default {
        name: 'GroupResumes',
        props: {
            itens: {
                type: Object,
            },
        },

        data() {
            return {
                panel: 1,
            }
        },

        computed: {
            itemsResumeTags() {
                return this.itens.resume_tags || []
            },
        },


    }
</script>
