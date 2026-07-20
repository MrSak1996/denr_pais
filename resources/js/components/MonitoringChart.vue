<!-- ./components/MonitoringChart.vue -->
<script setup lang="ts">
import Chart from 'primevue/chart'

defineProps({
    chartData: Object,
    chartOptions: Object
})
</script>

<template>
    <div class="w-full h-full rounded-xl shadow-md bg-white p-4">
        <Chart
            type="bar"
            :data="chartData"
            :options="chartOptions"
            class="h-full"
        />
    </div>
</template>