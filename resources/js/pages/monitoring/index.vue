<script setup lang="ts">
/**
 * UPDATED PARENT COMPONENT
 * --------------------------------
 * Added:
 * ✅ Calendar Navigation Button
 * ✅ 3 Views:
 *    - table
 *    - chart
 *    - calendar
 * ✅ My Activities / Office Activities Tabs
 */

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

/* PrimeVue */
import Button from 'primevue/button';
import Tab from 'primevue/tab';
import TabList from 'primevue/tablist';
import TabPanel from 'primevue/tabpanel';
import TabPanels from 'primevue/tabpanels';
import Tabs from 'primevue/tabs';

/* Full Calendar */
import dayGridPlugin from '@fullcalendar/daygrid';
import FullCalendar from '@fullcalendar/vue3';

/* Child Components */
import MonitoringChart from '@/components/MonitoringChart.vue';
import MonitoringTable from '@/components/MonitoringTable.vue';
import SummaryCards from '@/components/SummaryCards.vue';
import SummaryFilters from '@/components/SummaryFilters.vue';
import ResolutionTable from './table/reso_tbl.vue';
import MonitoringDats from '@/components/MonitoringDats.vue';

/* Composable */
import { useMonitoring, useDATSMonitoring } from '@/composables/useMonitoring';

/* ------------------------------------------ */
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List of Approved PAMB Resolution and Clearance',
        href: '/monitoring/index',
    },
];

/* ------------------------------------------
   View Navigation
------------------------------------------ */
const activeView = ref<'table' | 'dashboard' | 'chart' | 'resolution' | 'calendar'>('dashboard');

/* ------------------------------------------
   Calendar Tabs
------------------------------------------ */
const activeTab = ref<'my' | 'office'>('my');

const calendarOptions = {
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',
    height: 450,
    events: [
        { title: 'Office Meeting', date: '2026-04-25' },
        { title: 'Site Visit', date: '2026-04-27' },
    ],
};

/* ------------------------------------------ */
const protectedAreas = [
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

const { data, totals, fetchMonitoring } = useMonitoring();
const { datsData, dats, fetchDATSData } = useDATSMonitoring();

onMounted(() => {
    fetchMonitoring({ year: 2026 });
    fetchDATSData();
});

const summary = computed(() => ({
    approvedResolutions: totals.value?.approved_resolutions ?? 0,
    approvedClearances: totals.value?.approved_clearances ?? 0,
    received: totals.value?.received ?? 0,
}));

const chartData = computed(() => ({
    labels: protectedAreas,
    datasets: [
        {
            label: 'Approved Resolutions',
            backgroundColor: '#3B82F6',
            data: data.value.map((row) => row.approved_resolutions),
        },
        {
            label: 'Approved Clearances',
            backgroundColor: '#22C55E',
            data: data.value.map((row) => row.approved_clearances),
        },
        {
            label: 'Resolutions Received',
            backgroundColor: '#F97316',
            data: data.value.map((row) => row.received),
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', display: true },
        title: {
            display: true,
            text: 'Monthly Documents Received',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};

const monthLabels = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec"
];

const chartDataMonthlist = computed(() => {

    const totalDocuments = Array(12).fill(0);
    const minutesReceived = Array(12).fill(0);

    datsData.value.forEach((item: any) => {

        const value = item["DATE ENCODED"];
        if (!value) return;

        const [month, day, year] = value.split("/").map(Number);

        if (year !== 2026) return;

        const monthIndex = month - 1;

        // always count total
        totalDocuments[monthIndex]++;

        // ----------------------------
        // MINUTES CONDITION
        // ----------------------------
        const isMinutes =
            (item["CATEGORY"] || "")
                .toUpperCase()
                .includes("MINUTES");

        const hasMov =
            (item["MOV'S"] || "")
                .toUpperCase()
                .trim() === "YES";

        const hasOutgoingCDD =
            (item["OUTGOING DATE\n(CDD)"] || "")
                .toString()
                .trim() !== "";

        if (isMinutes && hasMov && hasOutgoingCDD) {
            minutesReceived[monthIndex]++;
        }

    });

    return {
        labels: monthLabels,
        datasets: [
            {
                label: "Total Documents",
                data: totalDocuments,
                backgroundColor: "#3B82F6",
                borderRadius: 8
            },
            {
                label: "Minutes (Completed)",
                data: minutesReceived,
                backgroundColor: "#22C55E",
                borderRadius: 8
            }
        ]
    };

});

const chartDataCategoryList = computed(() => {

    const categories = {
        MINUTES: 0,
        IPAF: 0,
        "HIRED STAFF": 0,
        BMS: 0,
        "PA PATROLLING": 0,
        "OTHERS NIPAS": 0,
        SAPA: 0,
        PAWCZMS: 0,
        AWS: 0,
        "MEETING/NOM": 0,
        RESOLUTION: 0
    };

    datsData.value.forEach((item: any) => {

        const raw = (item["CATEGORY"] || "").toUpperCase().trim();

        if (!raw) return;

        // normalize matching
        if (raw.includes("MINUTES")) categories.MINUTES++;
        else if (raw.includes("IPAF")) categories.IPAF++;
        else if (raw.includes("HIRED")) categories["HIRED STAFF"]++;
        else if (raw.includes("BMS")) categories.BMS++;
        else if (raw.includes("PA PATROLLING")) categories["PA PATROLLING"]++;
        else if (raw.includes("NIPAS") && raw.includes("OTHER")) categories["OTHERS NIPAS"]++;
        else if (raw.includes("SAPA")) categories.SAPA++;
        else if (raw.includes("PAWCZMS")) categories.PAWCZMS++;
        else if (raw.includes("AWS")) categories.AWS++;
        else if (raw.includes("MEETING") || raw.includes("NOM")) categories["MEETING/NOM"]++;
        else if (raw.includes("RESOLUTION")) categories.RESOLUTION++;

    });

    return {
        labels: Object.keys(categories),
        datasets: [
            {
                label: "Documents by Category",
                data: Object.values(categories),
                backgroundColor: [
                    "#3B82F6",
                    "#22C55E",
                    "#F59E0B",
                    "#EF4444",
                    "#8B5CF6",
                    "#06B6D4",
                    "#F97316",
                    "#10B981",
                    "#6366F1",
                    "#EC4899",
                    "#84CC16"
                ],
                borderRadius: 8
            }
        ]
    };

});

const chartDataRoutedToPie = computed(() => {

    const map: Record<string, number> = {};

    const summary = {
        MKS: { SIMPLE: 0, COMPLEX: 0, TOTAL: 0, MATCHED_RECORDS: 0 },
        CRCH: { SIMPLE: 0, COMPLEX: 0, TOTAL: 0, MATCHED_RECORDS: 0 }
    };

    datsData.value.forEach((item: any) => {

        const date = new Date(item["DATE ENCODED"]);
        if (isNaN(date.getTime())) return;

        const year = date.getFullYear();
        const month = date.getMonth();

        // January to June 2026 only
        if (year !== 2026 || month < 0 || month > 5) return;

        const routedRaw = (item["ROUTED TO"] || "").toString();
        const complexity = (item["LEVEL OF COMPLEXITY"] || "")
            .toString()
            .trim()
            .toUpperCase();

        // Split and remove duplicates
        const routedList = [...new Set(
            routedRaw
                .split("/")
                .map((r: string) => r.trim().toUpperCase())
                .filter(Boolean)
        )];

        // Pie chart counts
        routedList.forEach((key) => {
            map[key] = (map[key] || 0) + 1;
        });

        // ===== MKS =====
        if (routedList.includes("MKS")) {

            // Counts records like:
            // MKS
            // MKS/AHP
            // CRCH/MKS
            // MKS/CRCH/AHP
            summary.MKS.MATCHED_RECORDS++;

            summary.MKS.TOTAL++;

            if (complexity === "SIMPLE") {
                summary.MKS.SIMPLE++;
            } else if (complexity === "COMPLEX") {
                summary.MKS.COMPLEX++;
            }
        }

        // ===== CRCH =====
        if (routedList.includes("CRCH")) {

            summary.CRCH.MATCHED_RECORDS++;

            summary.CRCH.TOTAL++;

            if (complexity === "SIMPLE") {
                summary.CRCH.SIMPLE++;
            } else if (complexity === "COMPLEX") {
                summary.CRCH.COMPLEX++;
            }
        }
    });

    console.log("=== JANUARY TO JUNE 2026 SUMMARY ===");

    console.table([
        {
            ROUTED_TO: "MKS",
            MATCHED_RECORDS: summary.MKS.MATCHED_RECORDS,
            SIMPLE: summary.MKS.SIMPLE,
            COMPLEX: summary.MKS.COMPLEX,
            TOTAL: summary.MKS.TOTAL
        },
        {
            ROUTED_TO: "CRCH",
            MATCHED_RECORDS: summary.CRCH.MATCHED_RECORDS,
            SIMPLE: summary.CRCH.SIMPLE,
            COMPLEX: summary.CRCH.COMPLEX,
            TOTAL: summary.CRCH.TOTAL
        }
    ]);

    return {
        labels: Object.keys(map),
        datasets: [
            {
                label: "Total No. of Documents Routed To",
                data: Object.values(map),
                backgroundColor: "#22C55E",
                borderRadius: 8
            }
        ]
    };
});

const chartMonthOptions = {
    responsive: true,
    maintainAspectRatio: false,

    plugins: {
        legend: {
            display: true,          // Show the dataset labels
            position: "top"
        },
        tooltip: {
            enabled: true
        },
        title: {
            display: true,
            text: "Documents Received Per Month (2026)"
        }
    },

    scales: {
        x: {
            stacked: false,         // Set to true if you want stacked bars
            grid: {
                display: false
            },
            ticks: {
                color: "#6B7280"
            }
        },

        y: {
            beginAtZero: true,
            stacked: false,         // Set to true if using stacked bars
            ticks: {
                precision: 0,
                stepSize: 1,         // Optional: integer increments
                color: "#6B7280"
            },
            grid: {
                color: "#E5E7EB"
            }
        }
    }
};
</script>

<template>

    <Head title="DENR Online Protected Area Information System" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div class="box">
                <h2 class="title">List of Approved PAMB Resolution and Clearance</h2>
                <p class="text-gray-700 mb-4">
                    This section provides an overview of the hired staff monitoring system, including key metrics and
                    insights related to staff performance and activities.
                </p>


                <TabPanels>
                    <!-- DASHBOARD -->
                    <TabPanel value="0">
                        <div class="space-y-6">
                            <SummaryFilters />


                            <!-- NAVIGATION BUTTONS -->
                            <div class="flex flex-wrap gap-3">
                                <Button label="DATS Dashboard" icon="pi pi-dashboard"
                                    :severity="activeView === 'dashboard' ? '#004d40' : 'secondary'"
                                    @click="activeView = 'dashboard'" />
                                 <Button label="Calendar of Activities" icon="pi pi-calendar"
                                    :severity="activeView === 'calendar' ? 'primary' : 'secondary'"
                                    @click="activeView = 'calendar'" />

                                <Button label="Tabular Data" icon="pi pi-table"
                                    :severity="activeView === 'table' ? 'primary' : 'secondary'"
                                    @click="activeView = 'table'" />

                                <Button label="Graphical Data" icon="pi pi-chart-bar"
                                    :severity="activeView === 'chart' ? 'primary' : 'secondary'"
                                    @click="activeView = 'chart'" />

                                <Button label="Resolution  & Clearance Data" icon="pi pi-folder"
                                    :severity="activeView === 'resolution' ? 'primary' : 'secondary'"
                                    @click="activeView = 'resolution'" />
                            </div>
                            <!-- <div v-for="row in dats.completed_minutes_documents" :key="row.dats_number">
                                <strong>DATS:</strong> {{ row.dats_number }}
                            </div> -->

                            <!-- DATS -->
                            <div v-if="activeView === 'dashboard'"
                                class="box flex flex-col  border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <!-- Content -->
                                <MonitoringDats :dats="dats" :rows="data" :totals="totals" 
                                    :chartData="chartDataMonthlist"
                                    :chartOptions="chartMonthOptions"
                                    :chartDataCategoryList="chartDataCategoryList"
                                    :chartDataRoutedToList="chartDataRoutedToPie"
                                    />
                            </div>

                            <div v-if="activeView === 'calendar'"
                            class="box flex flex-col  border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <FullCalendar :options="calendarOptions" />

                            </div>


                            <!-- TABLE -->
                            <div v-if="activeView === 'table'">
                                <SummaryCards :summary="summary" />
                                <MonitoringTable :rows="data" :totals="totals" />
                            </div>

                            <!-- CHART -->
                            <div v-if="activeView === 'chart'" class="h-[550px]">
                                <MonitoringChart :chartData="chartData" :chartOptions="chartOptions" />
                            </div>

                            <!-- CALENDAR -->
                           

                            <div v-if="activeView === 'resolution'" class="h-[550px]">
                                <ResolutionTable />
                            </div>
                        </div>
                    </TabPanel>
                </TabPanels>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.box {
    background-color: #fff;
    border-top: 4px solid #00943a;
    margin-bottom: 20px;
    padding: 20px;
    -moz-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    -o-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    -webkit-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.box .title {
    border-bottom: 1px solid #e0e0e0;
    color: #432c0b !important;
    font-weight: bold;
    margin-bottom: 20px;
    margin-top: 0;
    padding-bottom: 10px;
    padding-top: 0;
    text-transform: uppercase;
    font-size: 10pt;
}

/* Base style for ToggleButton - Green (unchecked/default state) */
/* Default state - Green */
/* Base style for ToggleButton - Green (unchecked/default state) */
.p-togglebutton {
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    border: none;
    background-color: #22c55e;
    /* green-500 */
    color: white;
    transition:
        background-color 0.3s ease,
        filter 0.3s ease;
}

/* Hover effect */
.p-togglebutton:hover {
    filter: brightness(1.1);
}

/* Checked state - Darker green */
.p-togglebutton.p-togglebutton-checked {
    background-color: #15803d !important;
    /* green-700 */
    border-color: #166534;
    color: rgb(0, 0, 0);
}

/* Fix inner white background */
.p-togglebutton.p-togglebutton-checked .p-togglebutton-content {
    background-color: #15803d !important;
    box-shadow: none;
    color: white !important;
}

/* Ensure label and icon are white in all states */
.p-togglebutton .p-togglebutton-icon,
.p-togglebutton .p-togglebutton-label {
    color: white !important;
}
</style>
