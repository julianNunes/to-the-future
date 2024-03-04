<template>
    <div class="example">
        <apexcharts width="500" height="350" type="bar" :options="chartOptions" :series="series"></apexcharts>
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
        chartData: {
            type: Object,
        },
    },

    data() {
        console.log(this.chartData)
        return {
            chartOptions: {
                chart: {
                    id: 'basic-bar',
                },
                xaxis: {
                    categories: this.chartData.labels,
                },
            },
            series: [
                {
                    // name: 'series-1',
                    data: this.chartData.serie,
                },
            ],
        }
    },
}
</script>
