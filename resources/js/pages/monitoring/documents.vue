<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import { FilterService } from '@primevue/core/api';
import { FileCogIcon, Info, PrinterCheck } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Fieldset from 'primevue/fieldset';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Tag from 'primevue/tag';

import approved_icon from '../../../images/icons/approved.png';
import reject_icon from '../../../images/icons/reject.png';
import review_icon from '../../../images/icons/review.png';
    const page = usePage();

    import total_icon from '../../../images/icons/application.png';

    FilterService.register('MONTH', (value, filter) => {
        if (!filter) return true;
        if (!value) return false;

        // Example value: "6/16/2026"
        const month = value.split('/')[0];

        return month === filter;
    });
    defineProps({
        dats_documents: Array,
        total: Number,
        counts: Object
    })

    const expandedRows = ref<Record<number, boolean>>({});
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
    const filters = ref(
        {
            global: { value: null, matchMode: FilterMatchMode.CONTAINS },
            status: { value: null, matchMode: FilterMatchMode.EQUALS },
            type_of_meeting: { value: null, matchMode: FilterMatchMode.EQUALS },
            "ROUTED TO": { value: null, matchMode: FilterMatchMode.EQUALS },
            "CATEGORY": { value: null, matchMode: FilterMatchMode.EQUALS },
            "DATE ENCODED": { value: null, matchMode: 'MONTH' }

        });


    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'List of Pending Documents',
            href: '/monitoring/index',
        },
    ];
    const getFirstInitial = (value) => value?.trim().charAt(0).toUpperCase() || "";

    const avatarColor = (value) => {
        const colors = [
            "bg-blue-500",
            "bg-green-500",
            "bg-red-500",
            "bg-purple-500",
            "bg-yellow-500",
            "bg-pink-500",
            "bg-indigo-500",
        ];

        const index = value
            ? value.charCodeAt(0) % colors.length
            : 0;

        return colors[index];
    };
</script>

<template>

    <Head title="DENR Online Protected Area Information System" />
    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6 p-4">
            <div class="box">
                <h2 class="title">List of All Documents</h2>


                <div class="ict-hero-banner">
                    <div class="ict-qp-label">
                        DENR E-PARS
                    </div>

                    <h1 class="ict-hero-title">
                        This section provides an overview of the hired staff monitoring system, including key metrics and insights related to staff performance and activities.

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
                                Monitoring of Pending Documents
                            </h5>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Real-time tracking of pending documents
                            </p>

                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center">

                        <!-- <Button @click="showInsertDialog"
                            class="flex items-center gap-2 bg-[#1a6b45] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#145c3b] transition h-[32px]">

                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.5">

                                <path d="M12 5v14M5 12h14"></path>
                            </svg>

                            <span>Add / Insert</span>
                        </Button> -->
                    </div>
                </div>
            </div>

            <div class="box">
                
             
                <div class="p-6">
                    <h1 class="text-2xl font-bold mb-4">
                        DATS Documents
                    </h1>

                    <p class="mb-4">
                        Total Pending: {{ total }}
                    </p>
                    <DataTable ref="dt" v-model:expandedRows="expandedRows" :value="dats_documents" dataKey="DATS_NUMBER"
                        stripedRows showGridlines paginator :rows="10" :rowsPerPageOptions="[10, 20, 50]"
                        responsiveLayout="scroll" class="w-full text-sm" :filters="filters" filterDisplay="menu"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} documents"
                        @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 80rem">
                        <template #header>
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <!-- LEFT: Search + Filters -->
                                <div class="flex flex-wrap items-center gap-3">
                                    <!-- Search -->
                                    <IconField class="w-full md:w-64">
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters['global'].value" placeholder="Search..."
                                            class="w-full" />
                                    </IconField>
                                    <Select v-model="filters['DATE ENCODED'].value" :options="[
                                        { label: 'January', value: '1' },
                                        { label: 'February', value: '2' },
                                        { label: 'March', value: '3' },
                                        { label: 'April', value: '4' },
                                        { label: 'May', value: '5' },
                                        { label: 'June', value: '6' },
                                        { label: 'July', value: '7' },
                                        { label: 'August', value: '8' },
                                        { label: 'September', value: '9' },
                                        { label: 'October', value: '10' },
                                        { label: 'November', value: '11' },
                                        { label: 'December', value: '12' }
                                    ]" optionLabel="label" optionValue="value" placeholder="Filter by Month" showClear
                                        class="w-full md:w-48" />

                                    <Select v-model="filters['ROUTED TO'].value"
                                        :options="['MKS', 'JAAE', 'CRCH', 'AHP', 'DJAF', 'KVA', 'MCY', 'PFMS', 'JAAE/MBP', 'JAAE/KVA', 'CRCH/KVA']"
                                        placeholder="Focal Person" class="w-full md:w-48" showClear />
                                    <!-- Meeting Type -->
                                    <Select v-model="filters['CATEGORY'].value"
                                        :options="['MINUTES', 'IPAF', 'HIRED STAFF', 'BMS', 'PA PATROLLING', 'OTHERS', 'NIPAS', 'SAPA', 'PAWCZMS', 'NOM/Invitation', 'MEETING/NOM', 'RESOLUTION ONLY', 'AWS', 'OTHERS/DOCUMENTATION']"
                                        placeholder="Category" class="w-full md:w-48" showClear />

                                    <!-- Status -->
                                    <Select v-model="filters.status.value" :options="[
                                        { label: 'Approved', value: 'Approved' },
                                        { label: 'For Review', value: 'For Review' },
                                        { label: 'Deferred', value: 'Deferred' },
                                    ]" optionLabel="label" optionValue="value" placeholder="Status"
                                        class="w-full md:w-48" />
                                </div>

                                <!-- RIGHT: Action Button -->
                                <div class="flex justify-end">
                                    <Button
                                        class="flex items-center gap-2 bg-[#1a6b45] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#145c3b] transition h-[32px]">

                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white"
                                            stroke-width="2.5">

                                            <path d="M12 5v14M5 12h14"></path>
                                        </svg>

                                        <span>Add / Insert</span>
                                    </Button>
                                </div>
                            </div>
                        </template>
                        <Column expander style="width: 3rem" />
                     
                        <Column field="ROUTED TO" header="Routed To">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <div :class="[
                                        'w-8 h-8 rounded-full text-white flex items-center justify-center font-semibold uppercase',
                                        avatarColor(data['ROUTED TO'])
                                    ]">
                                        {{ getFirstInitial(data['ROUTED TO']) }}
                                    </div>

                                    <span>{{ data['ROUTED TO'] }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="SUBJECT" header="Subject" style="width: 280px">
                            <template #body="{ data }">
                                <div class="whitespace-normal break-words">
                                    {{ data.SUBJECT }}
                                </div>
                            </template>
                        </Column>

                        <Column field="DATS_NUMBER" header="DATS Number">
                            <template #body="{ data }">
                                <Tag severity="info" rounded>
                                    {{ data.DATS_NUMBER }}
                                </Tag>

                            </template>
                        </Column>
                        <Column field="CATEGORY" header="Category" />
                        <Column field="DATE ENCODED" header="Date Encoded"></Column>

                        <Column field="DATE RECEIVED BY PAMBCS" header="Date Received by PAMBCS"></Column>

                        <Column field="DATE RECEIVED BY PAMBCS FOCAL PERSON" header="Date Received by Focal"></Column>


                        <template #expansion="slotProps">
                            <div class="p-4">
                                <h5 class="mb-3 flex items-center gap-2 font-semibold">
                                    <Info class="w-4 h-4" />
                                    Document Details
                                </h5>

                                <DataTable :value="[slotProps.data]" size="small" showGridlines stripedRows
                                    responsiveLayout="scroll">
                                    <Column field="ORIGINATING OFFICE" header="Originating Office"
                                        style="min-width: 10rem" />


                                    <Column field="DOCUMENT TYPE" header="Document Type" style="min-width: 14rem" />

                                    <Column field="MOV'S" header="Means of Verification" style="min-width: 14rem" />
                                    <Column field="DATE RECEIVED BY CDD" header="Date Received by CDD"
                                        style="min-width: 14rem" />
                                    <Column field="PAMBCS CHIEF INSTRUCTION" header="PAMBCS Chief Instruction"
                                        style="min-width: 14rem" />



                                    <Column field="REMARKS" header="Remarks" style="min-width: 12rem">
                                        <template #body="{ data }">
                                            {{ data['REMARKS'] || 'N/A' }}
                                        </template>
                                    </Column>

                                    <Column field="OUTGOING DATE" header="Outgoing Date"
                                        :headerStyle="{ backgroundColor: 'red', color: 'white' }"
                                        style="min-width: 10rem;">
                                        <template #body="{ data }">
                                            {{ data['OUTGOING DATE'] || 'N/A' }}
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                    <!-- <table class="w-full border">
                        <thead>
                            <tr>
                                <th class="border p-2">DATS Number</th>
                                <th class="border p-2">Date Encoded</th>
                                <th class="border p-2">Date Received by PAMBCS</th>
                                <th class="border p-2">Date Received by Focal</th>
                                <th class="border p-2">Subject</th>
                                <th class="border p-2">Routed To</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in documents" :key="row.DATS_NUMBER">

                                <td class="border p-2">
                                    {{ row.DATS_NUMBER }}
                                </td>

                                <td class="border p-2">
                                    {{ row['DATE ENCODED'] }}
                                </td>

                                <td class="border p-2">
                                    {{ row['DATE RECEIVED BY PAMBCS'] }}
                                </td>

                                <td class="border p-2">
                                    {{ row['DATE RECEIVED BY FOCAL PERSON'] }}
                                </td>

                                <td class="border p-2">
                                    {{ row.SUBJECT }}
                                </td>

                                <td class="border p-2">
                                    {{ row['ROUTED TO'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table> -->
                </div>
            </div>
        </div>
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