<template>
    <div class="ma-2">
        <apexcharts height="350" type="bar" :options="options" :series="series"></apexcharts>
    </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts'

export default {
    name: 'BarChart',

    components: {
        apexcharts: VueApexCharts,
    },

    props: {
        options: {
            type: Object,
        },
        series: {
            type: Array,
        },
    },
}
</script>
