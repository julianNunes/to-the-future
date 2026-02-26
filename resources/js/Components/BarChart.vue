<template>
    <div class="ma-2">
        <apexcharts height="350" type="bar" :options="options" :series="series"></apexcharts>
    </div>
</template>

<script setup>
    import apexcharts from 'vue3-apexcharts'

    defineOptions({ name: 'BarChart' })

    defineProps({
        options: {
            type: Object,
        },
        series: {
            type: Array,
        },
    })
</script>
