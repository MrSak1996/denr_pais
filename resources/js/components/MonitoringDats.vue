<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    rows: Array,
    dats: Object,
    chartData: Object,
    chartOptions: Object,
    chartDataCategoryList: Object,
    chartDataRoutedToList: Object,
})
</script>

<template>
    <div class="flex-1 overflow-y-auto p-6 bg-gray-50">

        <!-- Dashboard Title -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                PAMBCS Dashboard
            </h1>
            <p class="text-sm text-gray-500">
                Document Monitoring Summary
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <!-- Total Documents -->
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">
                    Total Documents
                </p>

                <h2 class="mt-2 text-3xl font-bold text-blue-600">
                    {{ dats.total_documents }}
                </h2>

                
                <p class="mt-2 text-xs text-gray-400">
                    <Link href="/monitoring/records" class="text-blue-500 hover:text-blue-700 hover:underline">
                    View More Details
                    </Link>
                </p>
            </div>

            <!-- Received This Month -->
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">
                    Received This Month
                </p>

                <h2 class="mt-2 text-3xl font-bold text-green-600">
                    {{ dats.received_this_month }}
                </h2>

               
                <p class="mt-2 text-xs text-gray-400">
                    <Link href="/monitoring/received" class="text-blue-500 hover:text-blue-700 hover:underline">
                    View More Details
                    </Link>
                </p>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">
                    Pending Minutes Documents
                </p>

                <h2 class="mt-2 text-3xl font-bold text-orange-500">
                    {{ dats.pending_minutes }}
                </h2>

                <p class="mt-2 text-xs text-gray-400">
                    <Link href="/monitoring/pending" class="text-blue-500 hover:text-blue-700 hover:underline">
                    View More Details
                    </Link>
                </p>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500">
                    Completed
                </p>

                <h2 class="mt-2 text-3xl font-bold text-emerald-600">
                    {{ dats.completed }}
                </h2>

                <p class="mt-2 text-xs text-gray-400">
                    Successfully processed
                </p>
            </div>

        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">

            <!-- Monthly Trend Placeholder -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    Monthly Documents Received
                </h3>

                <!-- FIX: give a real height -->

                <div class="relative h-80">
                    <Chart type="bar" class="absolute inset-0 w-full h-full" :data="chartData"
                        :options="chartOptions" />
                </div>
            </div>

            <!-- Document Type Placeholder -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    Document Type Distribution
                </h3>

                <!-- FIX: give a real height -->

                <div class="relative h-80">
                    <Chart type="bar" class="absolute inset-0 w-full h-full" :data="chartDataCategoryList"
                        :options="chartOptions" />
                </div>
            </div>

        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-1 lg:grid-cols-1 mt-4">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">
                    Total No. of Documents Routed To Each Focal
                </h3>

                <!-- FIX: give a real height -->

                <div class="relative h-80">
                    <Chart type="bar" class="absolute inset-0 w-full h-full" :data="chartDataRoutedToList"
                        :options="chartOptions" />
                </div>
            </div>
        </div>

    </div>
</template>