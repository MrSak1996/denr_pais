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

import quezon from '../../../images/quezon.png'
import batangas from '../../../images/batangas.png'
import laguna from '../../../images/laguna.png'
import cavite from '../../../images/cavite.png'
import rizal from '../../../images/rizal.png'

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
import ProvinceMonitoring from '@/components/ProvinceMonitoring.vue';


/* Composable */
import { useMonitoring, useDATSMonitoring } from '@/composables/useMonitoring';


// -------------- Field Offices Rating ------------------

const protectedAreasByProvince = {
    rizal: [
        'UMRIP',
        'HPL',
        'IPL',
        'KRFR',
    ],

    batangas: [
        'TVPL',
    ],

    cavite: [
        'MPMNGGPL',
    ],

    quezon: [
        'QPL',
        'BPL',
        'MSPL',
        'MBSCPL',
        'PP 1636',
        'APLS',
        'POWFR',
        'BWRFR',
        'CWRFR',
        'AWFR',
        'MIGRBS',
        'PMSFR',
        'CPLS',
        'SPL',
        'UWR',
        'CPLS',
    ],
};

const municipalities = {
    cavite: [
        'Alfonso',
        'Amadeo',
        'Bacoor',
        // ...
    ],

    laguna: [
        'Alaminos',
        'Bay',
        'Biñan',
        // ...
    ],

    batangas: [
        'Agoncillo',
        'Alitagtag',
        'Balayan',
        // ...
    ],

    rizal: [
        'Angono',
        'Antipolo',
        'Baras',
        // ...
    ],

    quezon: [
        'Agdangan',
        'Alabat',
        'Atimonan',
        // ...
    ],
};
const selectedProvince = ref(null);

const selectProvince = (province) => {
    selectedProvince.value = province;
};

const backToOverview = () => {
    selectedProvince.value = null;
};

// -------------- Field Offices Rating ------------------
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
const activeView = ref<'table' | 'dashboard' | 'chart' | 'resolution' | 'calendar' | 'field'>('dashboard');

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

const provinces = [
    {
        name: 'CAVITE',
        key: 'cavite',
        color: 'bg-[#065f46]',
        text: 'text-white',
        pa_count: 1
    },
    {
        name: 'LAGUNA',
        key: 'laguna',
        color: 'bg-[#065f46]',
        text: 'text-white',
        pa_count: 3

    },
    {
        name: 'BATANGAS',
        key: 'batangas',
        color: 'bg-[#2196f3]',
        text: 'text-white',
        pa_count: 1
    },
    {
        name: 'RIZAL',
        key: 'rizal',
        color: 'bg-[#f4c430]',
        text: 'text-black',
        pa_count: 4
    },
    {
        name: 'QUEZON',
        key: 'quezon',
        color: 'bg-[#28a745]',
        text: 'text-white',
        pa_count: 17
    }
]
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

    // ==========================================================
    // ROUTED TO LIST
    // ==========================================================

    const routedNames = [
        "CRCH",
        "KVA",
        "DJAF",
        "JAAE",
        "MBP",
        "MCY",
        "AHP",
        "RBR",
        "MPB",
        "MKS",
        "CRFMS",
        "RWRC",
        "JGC",
        "PFMS",
        "ALL PA FOCAL",
        "CJ",
        "CRCJ"
    ];


    // ==========================================================
    // PIE CHART COUNTS
    // ==========================================================

    const map: Record<string, number> = {};

    routedNames.forEach((name) => {
        map[name] = 0;
    });


    // ==========================================================
    // SUMMARY
    // ==========================================================

    const summary: Record<string, {
        SIMPLE: number;
        COMPLEX: number;
        TOTAL: number;
        MATCHED_RECORDS: number;
    }> = {};


    routedNames.forEach((name) => {

        summary[name] = {
            SIMPLE: 0,
            COMPLEX: 0,
            TOTAL: 0,
            MATCHED_RECORDS: 0
        };

    });


    // ==========================================================
    // PROCESS DATA
    // ==========================================================

    datsData.value.forEach((item: any) => {

        const date = new Date(
            item["DATE ENCODED"]
        );

        if (isNaN(date.getTime())) {
            return;
        }


        const year = date.getFullYear();
        const month = date.getMonth();


        // ======================================================
        // JANUARY - JUNE 2026 ONLY
        // ======================================================

        if (
            year !== 2026 ||
            month < 0 ||
            month > 5
        ) {
            return;
        }


        // ======================================================
        // ROUTED TO
        // ======================================================

        const routedRaw =
            (item["ROUTED TO"] || "")
                .toString()
                .trim()
                .toUpperCase();


        // ======================================================
        // LEVEL OF COMPLEXITY
        // ======================================================

        const complexity =
            (item["LEVEL OF COMPLEXITY"] || "")
                .toString()
                .trim()
                .toUpperCase();


        // ======================================================
        // SPLIT ROUTED TO
        //
        // Examples:
        //
        // MKS/AHP
        // CRCH/MKS
        // JAAE/MBP
        // ALL PA FOCAL
        // ======================================================

        const routedList = [
            ...new Set(
                routedRaw
                    .split("/")
                    .map((r: string) =>
                        r.trim().toUpperCase()
                    )
                    .filter(Boolean)
            )
        ];


        // ======================================================
        // CHECK EACH ROUTED PERSON/GROUP
        // ======================================================

        routedNames.forEach((name) => {

            if (!routedList.includes(name)) {
                return;
            }


            // ==================================================
            // PIE COUNT
            // ==================================================

            map[name] =
                (map[name] || 0) + 1;


            // ==================================================
            // MATCHED RECORD
            // ==================================================

            summary[name].MATCHED_RECORDS++;

            summary[name].TOTAL++;


            // ==================================================
            // SIMPLE
            // ==================================================

            if (
                complexity === "SIMPLE"
            ) {

                summary[name].SIMPLE++;

            }


            // ==================================================
            // COMPLEX
            // ==================================================

            else if (
                complexity === "COMPLEX"
            ) {

                summary[name].COMPLEX++;

            }

        });

    });


    // ==========================================================
    // CONSOLE SUMMARY
    // ==========================================================

    console.log(
        "=== JANUARY TO JUNE 2026 ROUTED TO SUMMARY ==="
    );


    console.table(
        routedNames.map((name) => ({

            ROUTED_TO: name,

            MATCHED_RECORDS:
                summary[name].MATCHED_RECORDS,

            SIMPLE:
                summary[name].SIMPLE,

            COMPLEX:
                summary[name].COMPLEX,

            TOTAL:
                summary[name].TOTAL

        }))
    );


    // ==========================================================
    // PIE CHART
    // ==========================================================

    return {

        labels: routedNames,

        datasets: [

            {
                label:
                    "Total No. of Documents Routed To",

                data:
                    routedNames.map(
                        (name) => map[name]
                    ),

                backgroundColor:
                    "#22C55E",

                borderRadius:
                    8
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
                                <Button label="Field Offices" icon="pi pi-folder"
                                    :severity="activeView === 'field' ? 'primary' : 'secondary'"
                                    @click="activeView = 'field'" />
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
                                    :chartData="chartDataMonthlist" :chartOptions="chartMonthOptions"
                                    :chartDataCategoryList="chartDataCategoryList"
                                    :chartDataRoutedToList="chartDataRoutedToPie" />
                            </div>



                            <div v-if="!selectedProvince && activeView === 'field'"
                                class="box flex flex-col border border-sidebar-border/70 p-4 dark:border-sidebar-border">

                                <div class="w-full flex flex-col lg:flex-row">

                                    <!-- COL-LG-8 -->
                                    <div class="w-full lg:w-2/3 flex justify-center">

                                        <div class="relative w-full max-w-[445px] aspect-square">

                                            <img :src="quezon" class="absolute top-0 left-[68px] w-[445px] max-w-none"
                                                alt="Quezon" />

                                            <img :src="batangas" class="absolute top-0 left-0 w-[445px] max-w-none"
                                                alt="Batangas" />

                                            <img :src="laguna" class="absolute top-0 left-0 w-[445px] max-w-none"
                                                alt="Laguna" />

                                            <img :src="cavite" class="absolute top-0 left-0 w-[445px] max-w-none"
                                                alt="Cavite" />

                                            <img :src="rizal" class="absolute top-0 left-0 w-[445px] max-w-none"
                                                alt="Rizal" />

                                        </div>

                                    </div>


                                    <!-- COL-LG-4 -->
                                    <div class="w-full lg:w-1/3">

                                        <div class="bg-[#e8e8e8] p-[15px]">

                                            <a v-for="province in provinces" :key="province.key" href="#"
                                                @click.prevent="selectProvince(province.key)" :class="[
                                                    'mb-1 flex min-h-[80px] items-center no-underline transition-transform duration-200 hover:scale-[1.02]',
                                                    province.color,
                                                    province.text
                                                ]">

                                                <span class="flex w-[60px] items-center justify-center text-2xl">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>

                                                <div class="flex flex-col">

                                                    <span class="text-sm">
                                                        <b>{{ province.name }}</b>
                                                        - Total Protected Areas
                                                    </span>

                                                    <span class="text-[20pt] font-bold leading-none">
                                                        <!-- {{ reports[province.key] ?? 0 }} -->
                                                        <b>{{ province.pa_count }}</b>
                                                    </span>

                                                </div>

                                            </a>

                                        </div>

                                    </div>

                                </div>

                            </div>




                            <div v-if="selectedProvince && activeView === 'field'"
                                class="box border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <!-- INDICATOR HEADER -->
                                <div class="mb-4 overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm">

                                    <!-- Indicator -->
                                    <div class="flex border-b border-gray-300">
                                        <div class="w-[120px] shrink-0 bg-gray-100 px-3 py-3 text-sm font-bold">
                                            Indicator:
                                        </div>

                                        <div class="flex-1 px-3 py-3 text-sm">
                                            <span class="font-semibold">
                                                53 PAMB Resolutions signed with minutes of meeting approved by the PAMB
                                                Chair (RED)
                                                submitted to BMB within 30 working days after the PAMB meeting
                                            </span>
                                        </div>
                                    </div>

                                    <!-- CY 2026 -->
                                    <div class="flex border-b border-gray-300">
                                        <div class="w-[120px] shrink-0 bg-gray-100 px-3 py-3 text-sm font-bold">
                                            CY 2026
                                        </div>

                                        <div class="flex-1 px-3 py-3 text-sm">
                                            <span class="font-semibold">Legend:</span>
                                            Q1 (Quantity - use SPMS Calcu to calculate the %)
                                        </div>
                                    </div>

                                    <!-- Computation -->
                                    <div class="flex">
                                        <div class="w-[120px] shrink-0 bg-gray-100 px-3 py-3 text-sm font-bold">
                                            &nbsp;
                                        </div>

                                        <div class="flex-1 px-3 py-3 text-sm">
                                            <div class="flex flex-wrap gap-x-6 gap-y-1">
                                                <span><b>1</b> - after conduct</span>
                                                <span><b>3</b> - Fridays</span>
                                                <span><b>1</b> - Holidays</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="w-full flex flex-col lg:flex-row">
                                    <div class="w-full lg:w-2/3">
                                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                                            <!-- Province Monitoring -->
                                            <div class="w-full">
                                                <ProvinceMonitoring :province="selectedProvince"
                                                    :municipalities="municipalities[selectedProvince] ?? []" />
                                            </div>

                                            <!-- Protected Areas -->
                                            <div class="w-full">

                                                <div class="rounded-lg border border-gray-300 bg-white shadow-sm">

                                                    <div class="rounded-t-lg px-4 py-3 text-white"
                                                        style="background-color: #009688;">
                                                        <h5 class="m-0 text-sm font-semibold">
                                                            PROTECTED AREAS -
                                                            {{ selectedProvince.toUpperCase() }}
                                                        </h5>
                                                    </div>

                                                    <div class="overflow-x-auto">

                                                        <table class="w-full border-collapse text-xs">

                                                            <thead>

                                                                <tr class="bg-gray-100">

                                                                    <th rowspan="2"
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        Office
                                                                    </th>

                                                                    <th colspan="2"
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        1ST SEMESTER
                                                                    </th>

                                                                    <th colspan="4"
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        RATING
                                                                    </th>

                                                                    <th rowspan="2"
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        Remarks
                                                                    </th>

                                                                </tr>

                                                                <tr class="bg-gray-100">

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        Target
                                                                    </th>

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        Accomplishment
                                                                    </th>

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        Q1
                                                                    </th>

                                                                    <th
                                                                        class="border border-gray-400 bg-gray-200 px-2 py-2 text-center">
                                                                        Q2
                                                                    </th>

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        T3
                                                                    </th>

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        A4
                                                                    </th>

                                                                </tr>

                                                                <tr class="bg-gray-50 text-[10px] italic">

                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        a</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        b</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        c</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        d</th>
                                                                    <th
                                                                        class="border border-gray-400 bg-gray-200 px-2 py-1 text-center">
                                                                        e</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        f</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        g</th>
                                                                    <th
                                                                        class="border border-gray-400 px-2 py-1 text-center">
                                                                        h</th>

                                                                </tr>

                                                            </thead>

                                                            <tbody>

                                                                <tr v-for="area in protectedAreasByProvince[selectedProvince] ?? []"
                                                                    :key="area" class="hover:bg-gray-50">

                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 font-semibold">
                                                                        {{ area }}
                                                                    </td>

                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>
                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>
                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>

                                                                    <td
                                                                        class="border border-gray-400 bg-gray-50 px-2 py-2 text-center">
                                                                        -
                                                                    </td>

                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>
                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>
                                                                    <td
                                                                        class="border border-gray-400 px-2 py-2 text-center">
                                                                        -</td>

                                                                </tr>

                                                                <tr
                                                                    v-if="!protectedAreasByProvince[selectedProvince]?.length">
                                                                    <td colspan="8"
                                                                        class="border border-gray-400 px-3 py-6 text-center text-gray-500">
                                                                        No protected areas found for
                                                                        {{ selectedProvince.toUpperCase() }}.
                                                                    </td>
                                                                </tr>

                                                            </tbody>

                                                        </table>

                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                    <div class="w-full lg:w-1/3">

                                        <div class="bg-[#e8e8e8] p-[15px]">

                                            <a v-for="province in provinces" :key="province.key" href="#"
                                                @click.prevent="selectProvince(province.key)" :class="[
                                                    'mb-1 flex min-h-[80px] items-center no-underline transition-transform duration-200 hover:scale-[1.02]',
                                                    province.color,
                                                    province.text
                                                ]">

                                                <span class="flex w-[60px] items-center justify-center text-2xl">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </span>

                                                <div class="flex flex-col">

                                                    <span class="text-sm">
                                                        <b>{{ province.name }}</b>
                                                        - Total Protected Areas
                                                    </span>

                                                    <span class="text-[20pt] font-bold leading-none">
                                                        <!-- {{ reports[province.key] ?? 0 }} -->
                                                        <b>{{ province.pa_count }}</b>

                                                    </span>

                                                </div>

                                            </a>

                                        </div>

                                    </div>
                                </div>


                                <!-- BACK BUTTON -->

                                <div class="mt-4">

                                    <button type="button"
                                        class="rounded bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                                        @click="backToOverview">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Back to CALABARZON
                                    </button>

                                </div>

                            </div>


                            <div v-if="activeView === 'calendar'"
                                class="box flex flex-col  border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <FullCalendar :options="calendarOptions" />

                            </div>

                            <div v-if="activeView === 'table'">
                                <SummaryCards :summary="summary" />
                                <MonitoringTable :rows="data" :totals="totals" />
                            </div>

                            <div v-if="activeView === 'chart'" class="h-[550px]">
                                <MonitoringChart :chartData="chartData" :chartOptions="chartOptions" />
                            </div>

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
