<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';
import axios from 'axios';
import { Send, SquarePen, EyeIcon, Trash, Undo2, Edit2, Info, PrinterCheck } from 'lucide-vue-next';
import Fieldset from 'primevue/fieldset';
import { useToast } from 'primevue/usetoast';
import { onMounted, reactive, ref } from 'vue';
import FileCard from '../cards/file_card.vue';
import { ProductService } from '../service/ProductService';
import { Link, usePage } from '@inertiajs/vue3';
import ReusableConfirmDialog from '../modal/endorsed_modal.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputIcon from 'primevue/inputicon';
import IconField from 'primevue/iconfield';
import Tag from 'primevue/tag';
import { Button } from '@/components/ui/button';


interface Resolution {
    id: number
    resolution_no: string
    type_of_meeting: string
    focal_person: string
    resolution_title: string
}
const page = usePage();
const resolutions = ref<Resolution[]>(page.props.resolutions as Resolution[])
const userId = page.props.auth.user.id;
const officeId = page.props.auth.user.office_id;
const expandedRows = ref<Record<number, boolean>>({}) // fix null assignment error
const filters = ref({ global: { value: null, matchMode: FilterMatchMode.CONTAINS }, });

const onRowExpand = (event: { originalEvent: Event; data: Customer }) => {
    // Toggle single row without collapsing others
    expandedRows.value = {
        ...expandedRows.value,
        [event.data.id]: true,
    }
}

const onRowCollapse = (event?: { originalEvent: Event; data: Customer }) => {
    if (event) {
        const { [event.data.id]: _, ...rest } = expandedRows.value
        expandedRows.value = rest
    } else {
        expandedRows.value = {}
    }
}
const goToInsert = () => {
    router.visit('/monitoring/form/insert');
};
onMounted(() => {

});




</script>

<template>
    <div class="flex flex-col gap-4 rounded-xl p-4">
        <Toast />

        <Button  @click="goToInsert" class="mt-4 w-[150px]" :tabindex="4">
            Add Data
        </Button>

        <DataTable ref="dt" size="small" v-model:expandedRows="expandedRows" :value="resolutions" dataKey="id"
            stripedRows showGridlines :paginator="true" :rows="10" :filters="filters" filterDisplay="menu"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products" responsiveLayout="scroll"
            class="w-full text-sm" @rowExpand="onRowExpand" @rowCollapse="onRowCollapse" tableStyle="min-width: 60rem">

            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <!-- <h4 class="m-0 font-semibold">Manage Products</h4> -->
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                    </IconField>
                </div>
            </template>
            <Column expander style="width: 5rem" />
            <Column field="resolution_no" header="Resolution No" sortable />
            <Column field="type_of_meeting" header="Type of Meeting" sortable style="min-width: 12rem">

            </Column>
            <Column field="focal_person" header="Focal Person" sortable />

            <Column header="Resolution Title" field="resolution_title" sortable></Column>
            <Column header="Approved PAMB No." field="approved_pamb_no" sortable></Column>
            <Column header="Status" field="status" sortable></Column>


            <template #expansion="slotProps">
                <div class="p-4">
                    <h5 class="font-semibold mb-2 flex items-center gap-2">
                        <Info />
                        Other Information
                    </h5>
                    <DataTable size="small" showGridlines :value="[slotProps.data]">
                        <Column header="Date of Meeting" style="min-width: 12rem"
                            :headerStyle="{ backgroundColor: '', color: '#000', fontWeight: 'bold' }">

                            <template #body="slotProps">
                                {{ slotProps.data.authorized_representative || slotProps.data.applicant_name }}
                            </template>

                        </Column>
                        <Column field="status_title" header="Date Received by CDD" sortable style="min-width: 12rem">
                            <template #body="{ data }">
                                <div class="flex flex-col ">
                                    <Tag :value="data.status_title"
                                        :severity="data.application_status >= 17 ? 'danger' : 'success'" class=" mb-2"
                                        stlye="text-align:left !important;" />

                                    <!-- <button v-if="data.status_title === 'Returned to Technical Staff'"
                            class="px-3 py-1 rounded bg-blue-600 text-white text-xs" @click="openCommentModal(data)">
                            View Comments
                        </button> -->
                                </div>
                            </template>
                        </Column>
                        <Column header="Date Received By Focal" field="classification" sortable></Column>
                        <Column field="date_applied" header="Date Submitted/Released by Focal" sortable
                            style="min-width: 4rem" />
                        <Column header="Date Received by OARDTS" :exportable="false" style="min-width: 10rem">
                            <template #body="{ data }">
                                <Button class="mr-2" @click="openFileModal(data)" style="background-color: #004D40;">
                                    <EyeIcon :size="15" />
                                </Button>

                                <!-- :disabled="data.application_status !== 24 || data.application_status != 1">  -->
                                <Link :href="route('applications.edit', { id: data.id, type: data.application_type })"
                                    class="mr-2 inline-flex items-center justify-center bg-orange-700 text-white rounded-md px-3 py-2 hover:bg-orange-600">
                                    <SquarePen :size="16" />
                                </Link>



                                <Button v-if="data.application_status == STATUS_APPROVED_BY_RED"
                                    @click="generatePdf(data)" style="background-color: #0D47A1;">
                                    <PrinterCheck :size="15" />
                                </Button>


                            </template>
                        </Column>
                        <Column header="Date Approved by the PAMB Chair" field="classification" sortable></Column>
                        <Column header="Date emailed (BMB)" field="classification" sortable></Column>
                        <Column header="Date submitted to BMB (hard copy)" field="classification" sortable></Column>
                    </DataTable>
                </div>

            </template>




        </DataTable>
    </div>
    <ReusableConfirmDialog ref="confirmDialogRef" />


</template>
