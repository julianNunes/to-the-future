<template>
    <v-card class="mt-2">
        <v-card-title> MEU TITULo </v-card-title>
        <v-card-text>
            <span> BUDGet Income </span>
        </v-card-text>
    </v-card>
</template>

<script setup></script>

<script>
export default {
    name: 'BudgetIncome',
    props: {
        incomes: {
            type: Array,
        },
        viewOnly: {
            type: Boolean,
        },
    },

    data() {
        return {}
    },

    computed: {},
}
</script>
