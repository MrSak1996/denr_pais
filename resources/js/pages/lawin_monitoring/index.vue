<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';

import Toast from 'primevue/toast'
import Column from 'primevue/column'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import { type BreadcrumbItem } from '@/types';
import { useConfirm } from 'primevue/useconfirm'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useLawinMonitoring } from '@/composables/useMonitoring';
import { FilterMatchMode } from '@primevue/core/api'
import modal_patrol_plan from './modal/modal_patrol_plan.vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List of Protected Area Patrol Plan',
        href: '/lawin_monitoring/index',
    },
];

const { lawinData, fetchLawinData } = useLawinMonitoring();

const props = defineProps({
    protectedAreas: Array,
    paStaff: Array,
});
const confirm = useConfirm()

const form = useForm({
    protected_area_id: null,
    area: '',
    // target_patrol_sector: '',
    conservation_targets: '',
    target_patrol_route_distance: '',
    target_patrol_sector_name: '',
    target_patrol_sector_area: '',
    target_patrol_sector_location: '',
    target_patrol_period: '',
    patrol_team_members: '',
    data_manager: '',
    number_of_patrollers: null,
});

const showInsertDialog = () => {

    confirm.require({
        group: 'headless',
        header: 'Insert Protected Area Patrol Plan',
        message: 'Do you want to add a new patrol plan?',
        reject: () => {
        }
    })
}
const formatMonth = (month: number) => {
    const months = [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December'
    ]
    return months[month - 1] ?? 'N/A'
}
/* ---------------------------
   FILTERS
----------------------------*/
const filters = ref({
    global: {
        value: null,
        matchMode: FilterMatchMode.CONTAINS
    },

    'protected_area.pa_code': {
        value: null,
        matchMode: FilterMatchMode.EQUALS
    }
})
onMounted(() => {
    fetchLawinData();
});
</script>

<template>

    <Head title="DENR Online Protected Area Information System" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div class="box">
                <h2 class="title">List of Protected Area Patrol Plan</h2>


                <div class="ict-hero-banner">
                    <div class="ict-qp-label">
                        DENR E-PARS
                    </div>

                    <h1 class="ict-hero-title">
                        This section provides an overview of the protected area patrol plans, including key metrics
                        and insights related to patrol activities.

                    </h1>
                </div>

                <div class="flex items-center justify-between flex-wrap gap-4">

                    <!-- LEFT -->
                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 rounded-lg bg-[#1a6b45] flex items-center justify-center shrink-0">

                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.2">

                                <path
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>

                        <div>

                            <h5 class="text-[15px] font-bold text-gray-900">
                                Monitoring of Protected Area Patrol Plans
                            </h5>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Real-time tracking of patrol plans
                            </p>

                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center">

                        <Button @click="showInsertDialog"
                            class="flex items-center gap-2 bg-[#1a6b45] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#145c3b] transition h-[32px]">

                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.5">

                                <path d="M12 5v14M5 12h14"></path>
                            </svg>

                            <span>Add / Insert</span>
                        </Button>
                    </div>
                </div>
            </div>
            <div class="box">
                <DataTable :value="lawinData" v-model:filters="filters" dataKey="id" stripedRows showGridlines paginator
                    :rows="10" :rowsPerPageOptions="[5, 10, 25]" filterDisplay="menu" responsiveLayout="scroll"
                    class="w-full text-sm" :globalFilterFields="[
                        'protected_area_staff.name',
                        'protected_area_staff.protected_area.pa_code',
                        'month'
                    ]">

                    <!-- HEADER -->
                    <template #header>
                        <div class="flex flex-wrap items-center gap-3">

                            <!-- SEARCH -->
                            <IconField class="w-full md:w-64">
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>

                                <InputText v-model="filters.global.value" placeholder="Search..." class="w-full" />
                            </IconField>

                            <!-- PA CODE FILTER -->
                            <Select v-model="filters['protected_area.pa_code'].value" :options="[
                                'APLS', 'AWFR', 'BPL', 'BRWFR', 'CIPLS', 'CPLS',
                                'CWFR', 'HTPL', 'IWFR', 'KRFR', 'MBSCPL', 'MIGRBS',
                                'MPPMNGPL', 'MSPL', 'PaWFR', 'PMSFR', 'PoWFR',
                                'PP 1636', 'PP 1636 - Rizal Side', 'PPL', 'QPL', 'SFPL', 'TVPL', 'UMRBPL', 'URWR'
                            ]" placeholder="Filter PA Code" class="w-full md:w-48" filter showClear />

                        </div>
                    </template>

                    <!-- PA CODE -->
                    <Column field="protected_area.pa_code" header="PA Code" sortable filter
                        filterField="protected_area.pa_code" />

                    <!-- STAFF -->
                    <Column header="Area">
                        <template #body="{ data }">
                            {{ data.area ?? 'N/A' }} hectares
                        </template>
                    </Column>

                    <!-- POSITION -->
                    <Column header="Conservation Targets">
                        <template #body="{ data }">
                            {{ data.conservation_targets ?? 'N/A' }}
                        </template>
                    </Column>

                    <!-- MONTH -->
                    <Column header="Target Patrol Sector" sortable>
                        <template #body="{ data }">
                            {{ data.target_patrol_sector_name ?? 'N/A' }}
                        </template>
                    </Column>

                    <!-- ACCOMPLISHMENTS -->
                    <Column header="Target Patrol Sector Location">
                        <template #body="{ data }">
                            {{ data.target_patrol_sector_location ?? 'N/A' }}
                        </template>

                    </Column>
                    <Column header="Target Patrol Route Distance">
                        <template #body="{ data }">
                            {{ data.target_patrol_route_distance ?? 'N/A' }}
                        </template>

                    </Column>
                    <Column header="Target Patrol Period">
                        <template #body="{ data }">
                            {{ data.target_patrol_period ?? 'N/A' }}
                        </template>

                    </Column>
                    <Column header="Target Patrol Team Members">
                        <template #body="{ data }">
                            {{ data.patrol_team_members ?? 'N/A' }}
                        </template>

                    </Column>
                    <Column header="Data Manager">
                        <template #body="{ data }">
                            {{ data.data_manager ?? 'N/A' }}
                        </template>

                    </Column>
                    <Column header="Number of Patrollers">
                        <template #body="{ data }">
                            {{ data.number_of_patrollers ?? 'N/A' }}
                        </template>

                    </Column>

                </DataTable>
            </div>
        </div>

        <modal_patrol_plan :form="form" :protectedAreas="props.protectedAreas" :paStaff="props.paStaff" />
    </AppLayout>
</template>

<style scoped>
@import url('../../../css/style.css');

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #1a3a6b;
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
}
</style>