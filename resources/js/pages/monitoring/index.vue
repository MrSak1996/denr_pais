<script setup lang="ts">
import { useAppForm } from '@/composables/useAppForm';
import { ref } from 'vue';
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
import FloatLabel from 'primevue/floatlabel';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
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
    maintainAspectRatio: true, // ⭐ THIS FIXES HEIGHT CONTROL

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
const data = ref([
    {
        pa: 'APLS',
        focal: 'Julie/Kath',
        jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 5,
        approved_clearances: 0,
        received: 7,
        pamb_minutes: 1
    },
    {
        pa: 'CPLS',
        focal: 'Julie/Mich',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 1,
        approved_clearances: 2,
        received: 3,
        pamb_minutes: 1
    },
    {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
     {
        pa: 'TVPL',
        focal: 'Cj/Den',
         jan: 0,
        feb: 0,
        mar: 5,
        april: 2,
        may: 35,
        june: 6,
        july: 0,
        august: 0,
        september: 0,
        october: 0,
        november: 0,
        december: 0,
        approved_resolutions: 9,
        approved_clearances: 5,
        received: 9,
        pamb_minutes: 1
    },
    
    // 👉 add the rest of your data here
]);

const totals = {
    jan: 0,
    feb: 0,
    mar: 55,
    approved_resolutions: 55,
    approved_clearances: 43,
    received: 104,
    pamb_minutes: 15
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
                                    <FloatLabel for="rating">Year</FloatLabel>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <FloatLabel for="rating">Month</FloatLabel>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <FloatLabel for="rating">Quarter</FloatLabel>
                                    <Select />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <FloatLabel for="rating">Semestral</FloatLabel>
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
                            <Fieldset>
                                <DataTable :value="data" stripedRows showGridlines responsiveLayout="scroll"
                                    class="p-datatable-sm rounded-xl shadow-md">
                                    <!-- PA -->
                                    <Column field="pa" header="PA" frozen class="font-semibold" />

                                    <!-- FOCAL -->
                                    <Column field="focal" header="Focal/Alternate" />

                                    <!-- MONTHS -->
                                    <Column field="jan" header="Jan" class="text-center" />
                                    <Column field="feb" header="Feb" class="text-center" />
                                    <Column field="mar" header="Mar" class="text-center" />
                                    <Column field="apr" header="Apr" class="text-center" />
                                    <Column field="may" header="May" class="text-center" />
                                    <Column field="jun" header="June" class="text-center" />
                                    <Column field="jul" header="Jul" class="text-center" />
                                    <Column field="aug" header="Aug" class="text-center" />
                                    <Column field="sep" header="Sept" class="text-center" />
                                    <Column field="oct" header="Oct" class="text-center" />
                                    <Column field="nov" header="Nov" class="text-center" />
                                    <Column field="dec" header="Dec" class="text-center" />

                                    <!-- SUMMARY -->
                                    <Column field="approved_resolutions" header="Approved Resolutions">
                                        <template #body="slotProps">
                                            <span class="font-bold text-blue-600">
                                                {{ slotProps.data.approved_resolutions }}
                                            </span>
                                        </template>
                                    </Column>

                                    <Column field="approved_clearances" header="Approved Clearances">
                                        <template #body="slotProps">
                                            <span class="font-bold text-green-600">
                                                {{ slotProps.data.approved_clearances }}
                                            </span>
                                        </template>
                                    </Column>

                                    <Column field="received" header="Resolutions Received">
                                        <template #body="slotProps">
                                            <span class="font-bold text-orange-500">
                                                {{ slotProps.data.received }}
                                            </span>
                                        </template>
                                    </Column>

                                    <Column field="pamb_minutes" header="PAMB Minutes">
                                        <template #body="slotProps">
                                            <span class="font-bold text-purple-600">
                                                {{ slotProps.data.pamb_minutes }}
                                            </span>
                                        </template>
                                    </Column>

                                    <!-- FOOTER TOTAL -->
                                    <template #footer>
                                        <tr class="bg-gray-100 font-bold">
                                            <td colspan="2">Total</td>
                                            <td class="text-center">{{ totals.jan }}</td>
                                            <td class="text-center">{{ totals.feb }}</td>
                                            <td class="text-center">{{ totals.mar }}</td>
                                            <td class="text-blue-600 text-center">{{ totals.approved_resolutions }}</td>
                                            <td class="text-green-600 text-center">{{ totals.approved_clearances }}</td>
                                            <td class="text-orange-500 text-center">{{ totals.received }}</td>
                                            <td class="text-purple-600 text-center">{{ totals.pamb_minutes }}</td>
                                        </tr>
                                    </template>
                                </DataTable>
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
<style lang="css" scoped>
.p-datatable th {
    background: #f8fafc;
    font-weight: 600;
}

.p-datatable td {
    padding: 10px;
}
</style>
