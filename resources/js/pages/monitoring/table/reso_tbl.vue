<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import { FileCogIcon, Info, PrinterCheck } from 'lucide-vue-next';

import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Fieldset from 'primevue/fieldset';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

import total_icon from '../../../../images/icons/application.png';
import approved_icon from '../../../../images/icons/approved.png';
import reject_icon from '../../../../images/icons/reject.png';
import review_icon from '../../../../images/icons/review.png';
import ReusableConfirmDialog from '../modal/endorsed_modal.vue';
interface Resolution {
    id: number;
    resolution_no: string;
    type_of_meeting: string;
    focal_person: string;
    resolution_title: string;
}
const page = usePage();
const userId = page.props.auth.user.id;
const officeId = page.props.auth.user.office_id;
const expandedRows = ref<Record<number, boolean>>({}); // fix null assignment error
const filters = ref(
    {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
        type_of_meeting: { value: null, matchMode: FilterMatchMode.EQUALS },
        pa_code: { value: null, matchMode: FilterMatchMode.EQUALS }
    });


const onRowExpand = (event: { originalEvent: Event; data: Customer }) => {
    // Toggle single row without collapsing others
    expandedRows.value = {
        ...expandedRows.value,
        [event.data.id]: true,
    };
};

const onRowCollapse = (event?: { originalEvent: Event; data: Customer }) => {
    if (event) {
        const { [event.data.id]: _, ...rest } = expandedRows.value;
        expandedRows.value = rest;
    } else {
        expandedRows.value = {};
    }
};
const goToInsert = () => {
    router.visit('/monitoring/form/insert');
};

// GET DATA FROM CONTROLLER
//CONTROLLER NAME: RESOLUTIONCLEARANCECONTROLLER
//METHOD NAME: INDEX
const resolutions = ref(
    (page.props.resolutions as any[]).map((r) => ({
        ...r,
        pa_code: r.protected_area?.pa_code ?? null,
    })),
);
onMounted(() => { });
</script>

<template>
    <div class="flex flex-col gap-4 rounded-xl p-4">
        <Toast />
        <Fieldset legend="Dashboard Summary" class="mb-6">
            <div class="grid gap-4 md:grid-cols-4">
                <!-- TOTAL -->

                <!-- TOTAL -->
                <Card class="overflow-hidden rounded-2xl text-white shadow-lg" style="background-color: #1A237E;">
                    <template #content>
                        <div class="relative flex items-center justify-between">
                            <!-- TEXT -->
                            <div class="z-10 p-4">
                                <p class="text-sm opacity-90 text-white">Total Applications</p>
                                <h2 class="text-3xl font-bold text-white">12</h2>
                            </div>

                            <!-- IMAGE -->
                            <img :src="total_icon" alt="Total Applications"
                                class="absolute top-0 right-0 h-full w-auto object-cover opacity-90 transition-transform duration-500 ease-in-out hover:scale-150" />
                        </div>
                    </template>
                </Card>

                <!-- FOR REVIEW -->
                <Card
                    class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg"
                    style="background-color: #004D40 !important;color:#fff;">
                    <template #content>
                        <div class="relative flex items-center justify-between">
                            <!-- TEXT -->
                            <div class="z-10 p-4">
                                <p class="text-sm opacity-90">For Review</p>
                                <h2 class="text-3xl font-bold">34</h2>
                            </div>

                            <!-- IMAGE -->
                            <img :src="review_icon" alt="For Review"
                                class="absolute top-0 right-0 h-full w-auto object-cover opacity-90 transition-transform duration-500 ease-in-out hover:scale-150" />
                        </div>
                    </template>
                </Card>

                <!-- APPROVED -->
                <Card
                    class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg"
                    style="background-color: #BF360C !important;color:#fff;">
                    <template #content>
                        <div class="relative flex items-center justify-between">
                            <!-- TEXT -->
                            <div class="z-10 p-4">
                                <p class="text-sm opacity-90">Approved</p>
                                <h2 class="text-3xl font-bold">34</h2>
                            </div>

                            <!-- IMAGE -->
                            <img :src="approved_icon" alt="Approved"
                                class="absolute top-0 right-0 h-full w-auto object-cover opacity-90 transition-transform duration-500 ease-in-out hover:scale-150" />
                        </div>
                    </template>
                </Card>

                <Card class="overflow-hidden rounded-2xl bg-blue-700 text-white shadow-lg"
                    style="background-color: #FFEB3B !important;color:#F57F17;">
                    <template #content>
                        <div class="relative flex items-center justify-between">
                            <!-- TEXT -->
                            <div class="z-10 p-4">
                                <p class="text-sm opacity-90">Deferred</p>
                                <h2 class="text-3xl font-bold">432</h2>
                            </div>

                            <!-- IMAGE -->
                            <img :src="reject_icon" alt="Deferred"
                                class="absolute top-0 right-0 h-full w-auto object-cover opacity-90 transition-transform duration-500 ease-in-out hover:scale-150" />
                        </div>
                    </template>
                </Card>
            </div>
        </Fieldset>
        <DataTable ref="dt" size="small" v-model:expandedRows="expandedRows" :value="resolutions" dataKey="id"
            stripedRows showGridlines :paginator="true" :rows="10" :filters="filters" filterDisplay="menu"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products" responsiveLayout="scroll"
            class="w-full text-sm" @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem">
            <template #header>
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <!-- LEFT: Search + Filters -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Search -->
                        <IconField class="w-full md:w-64">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." class="w-full" />
                        </IconField>

                        <Select v-model="filters.pa_code.value" :options="[
                            'APLS', 'AWFR', 'BPL', 'BRWFR', 'CIPLS', 'CPLS', 'CWFR', 'HTPL', 'IWFR', 'KRFR',
                            'MBSCPL', 'MIGRBS', 'MPPMNGPL', 'MSPL', 'PaWFR', 'PMSFR', 'PoWFR', 'PP 1636',
                            'PPL', 'QPL', 'SFPL', 'TVPL', 'UMRBPL', 'URWR'
                        ]" placeholder="Protected Area" class="w-full md:w-48" />

                        <!-- Meeting Type -->
                        <Select v-model="filters.type_of_meeting.value" :options="[
                            { label: 'Regular', value: 'Regular' },
                            { label: 'Special', value: 'Special' },
                        ]" optionLabel="label" optionValue="value" placeholder="Meeting Type" class="w-full md:w-48" />

                        <!-- Status -->
                        <Select v-model="filters.status.value" :options="[
                            { label: 'Approved', value: 'Approved' },
                            { label: 'For Review', value: 'For Review' },
                            { label: 'Deferred', value: 'Deferred' },
                        ]" optionLabel="label" optionValue="value" placeholder="Status" class="w-full md:w-48" />
                    </div>

                    <!-- RIGHT: Action Button -->
                    <div class="flex justify-end">
                        <Button @click="goToInsert" icon="pi pi-plus" label="Add Data" class="w-full md:w-auto" />
                    </div>
                </div>
            </template>
            <Column expander style="width: 3rem" />
            <Column header="Action" style="min-width: 10rem">
                <template #body="slotProps">
                    <div data-v-46ac47fe=""
                        style="display: flex; gap: 4px; align-items: center; justify-content: center; flex-wrap: wrap">
                        <Button class="bg-[#1B5E20]">
                            <Info />
                        </Button>
                        <Button class="bg-[#BF360C]">
                            <FileCogIcon />
                        </Button>
                        <Button class="bg-[#0D47A1]">
                            <PrinterCheck />
                        </Button>
                    </div>
                </template>
            </Column>
            <Column header="Status" field="status" sortable>
                <template #body="slotProps">
                    <Tag :value="slotProps.data.status" severity="success">{{ slotProps.data.status }}</Tag>
                </template>
            </Column>
            <Column field="pa_code" header="Protected Area Code" sortable />
            <Column field="resolution_no" header="Resolution No" sortable />
            <Column field="type_of_meeting" header="Type of Meeting" sortable style="min-width: 12rem"> </Column>
            <Column header="Focal Person" sortable>
                <template #body="slotProps">
                    {{ slotProps.data.focal_person + ' & ' + slotProps.data.alternate_focal || 'N/A' }}
                </template>
            </Column>
            <Column header="Resolution Title" field="resolution_title" sortable></Column>
            <Column header="Approved PAMB No." field="approved_pamb_no" sortable></Column>
            <template #expansion="slotProps">
                <div class="p-4">
                    <h5 class="mb-2 flex items-center gap-2 font-semibold">
                        <Info />
                        Other Information
                    </h5>
                    <DataTable size="small" showGridlines :value="[slotProps.data]">
                        <Column header="Date of Meeting" style="min-width: 12rem" field="date_of_meeting" sortable>
                            <template #body="slotProps">
                                {{ slotProps.data.date_of_meeting || 'N/A' }}
                            </template>
                        </Column>
                        <Column field="date_received_by_cdd" header="Date Received by CDD" sortable
                            style="min-width: 12rem">
                            <template #body="slotProps">
                                {{ slotProps.data.date_received_by_cdd || 'N/A' }}
                            </template>
                        </Column>
                        <Column field="date_received_by_focal" header="Date Received By Focal" sortable></Column>
                        <Column field="date_submitted_released_by_focal" header="Date Submitted/Released by Focal"
                            sortable style="min-width: 4rem" />
                        <Column field="date_received_by_oardts" header="Date Received by OARDTS" :exportable="false"
                            style="min-width: 10rem">
                            <template #body="slotProps">
                                {{ slotProps.data.date_received_by_oardts || 'N/A' }}
                            </template>
                        </Column>
                        <Column header="Date Approved by the PAMB Chair" field="date_approved_by_pamb_chair" sortable>
                        </Column>
                        <Column header="Date emailed (BMB)" field="date_emailed_bmb" sortable></Column>
                        <Column header="Date submitted to BMB (hard copy)" field="date_submitted_to_bmb_hardcopy"
                            sortable></Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>
    </div>
    <ReusableConfirmDialog ref="confirmDialogRef" />
</template>

<style scoped>
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #1a3a6b;
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
}
</style>
