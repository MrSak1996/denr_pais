<script setup lang="ts">
import { useAppForm } from '@/composables/useAppForm';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import Fieldset from 'primevue/fieldset';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';
import Select from 'primevue/select';
import prc_table from './table/reso_tbl.vue';

const { chainsaw_form, chainsaw } = useAppForm();
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List of Approved PAMB Resolution and Clearance',
        href: '/monitoring/index',
    },
];

// Sample data based on your image
const pas = [
    'APLS',
    'AWFR',
    'BPL',
    'BRWFR',
    'CIPLS',
    'CPLS',
    'CWFR',
    'HTPL',
    'IWFR',
    'KRFR',
    'MBSCPL',
    'MIGRBS',
    'MPPMNGPL',
    'MSPL',
    'PaWFR',
    'PMSFR',
    'PoWFR',
    'PP 1636',
    'PPL',
    'QPL',
    'SFPL',
    'TVPL',
    'UMRBPL',
    'URWR',
];

const totalApprovedResolutions = [5, 0, 1, 0, 0, 3, 1, 0, 0, 3, 19, 2, 3, 2, 2, 2, 2, 8, 0, 5, 0, 9, 9, 4];

const totalApprovedClearances = [0, 0, 0, 0, 0, 2, 0, 0, 0, 3, 18, 0, 0, 0, 0, 1, 0, 7, 0, 4, 0, 5, 2, 0];

const totalResolutionsReceived = [7, 0, 0, 0, 0, 3, 1, 0, 0, 14, 19, 2, 3, 3, 2, 2, 2, 11, 0, 7, 0, 9, 12, 4];

// Chart.js data for Resolutions vs Clearances
const chartData = {
    labels: pas,
    datasets: [
        {
            label: 'Approved Resolutions',
            backgroundColor: '#42A5F5',
            data: totalApprovedResolutions,
        },
        {
            label: 'Approved Clearances',
            backgroundColor: '#66BB6A',
            data: totalApprovedClearances,
        },
        {
            label: 'Resolutions Received',
            backgroundColor: '#FFA726',
            data: totalResolutionsReceived,
        },
    ],
};
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false, // ⭐ THIS FIXES HEIGHT CONTROL

    plugins: {
        legend: {
            position: 'top'
        },
        title: {
            display: true,
            text: 'PAMB Resolutions & Clearances per Protected Area'
        }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
};
</script>

<template>

    <Head title="DENR Online Protected Area Information System" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 rounded-xl p-4">
            <!-- Table Box Section -->
            <div class="box">
                <Tabs value="0">
                    <TabList>
                        <Tab value="0">Summary of Approved PAMB Resolution & Clearances</Tab>
                        <Tab value="1">List of PAMB Resolution & Clearances</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="0">
                            <div class="grid gap-6 md:grid-cols-4">
                                <div class="flex flex-col gap-1">
                                    <Label for="rating">Year</Label>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label for="rating">Month</Label>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label for="rating">Quarter</Label>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <Label for="rating">Semestral</Label>
                                    <Select />
                                </div>
                            </div>

                            <!-- SUMMARY CARDS -->
                            <Fieldset legend="Dashboard Summary" class="mb-6">
                                <div class="grid gap-4 md:grid-cols-3">
                                    <Card class="rounded-xl text-center shadow-md">
                                        <template #title>Total Approved Resolutions</template>
                                        <template #content>
                                            <h2 class="text-3xl font-bold text-blue-600">
                                                {{totalApprovedResolutions.reduce((a, b) => a + b, 0)}}
                                            </h2>
                                        </template>
                                    </Card>

                                    <Card class="rounded-xl text-center shadow-md">
                                        <template #title>Total Approved Clearances</template>
                                        <template #content>
                                            <h2 class="text-3xl font-bold text-green-600">
                                                {{totalApprovedClearances.reduce((a, b) => a + b, 0)}}
                                            </h2>
                                        </template>
                                    </Card>

                                    <Card class="rounded-xl text-center shadow-md">
                                        <template #title>Total Resolutions Received</template>
                                        <template #content>
                                            <h2 class="text-3xl font-bold text-orange-500">
                                                {{totalResolutionsReceived.reduce((a, b) => a + b, 0)}}
                                            </h2>
                                        </template>
                                    </Card>
                                </div>
                            </Fieldset>

                            <!-- CHART -->
                            <Fieldset legend="Graphical Comparison">
                           
                                <div class="mx-auto w-full md:w-4/5" style="height: 500px !important;">
                                    <Chart type="bar" :data="chartData" :options="chartOptions" />
                                </div>
                            </Fieldset>
                        </TabPanel>
                        <TabPanel value="1">
                            <prc_table />
                        </TabPanel>
                        <TabPanel value="2"> </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped src="../../../css/style.css"></style>
