<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { router, usePage } from '@inertiajs/vue3'
import { FilterMatchMode } from '@primevue/core/api'
import { Info } from 'lucide-vue-next';

import Dialog from 'primevue/dialog'
import Toast from 'primevue/toast'
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'

import { useDashboardStats } from '../../../composables/useDashboardStat'

const { fetchDashboardData } = useDashboardStats()

const page = usePage()
const props = defineProps({
    rows: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
})
const expandedRows = ref<Record<number, boolean>>({})

/* ---------------------------
   FILTERS
----------------------------*/
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    pa_code: { value: null, matchMode: FilterMatchMode.EQUALS },
    month: { value: null, matchMode: FilterMatchMode.EQUALS },
})

const imageDialog = ref(false);
const selectedFiles = ref([]);

const viewFiles = (files) => {
    selectedFiles.value = files;
    imageDialog.value = true;
};

const formatMonth = (month: number) => {
    const months = [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December'
    ]
    return months[month - 1] ?? 'N/A'
}

const getEmbedUrl = (url: string) => {
    return url ? url.replace('/view', '/preview') : '';
};
/* ---------------------------
   INIT
----------------------------*/
onMounted(() => {
    console.log(props.rows);
    fetchDashboardData()
})
</script>

<template>
    <div class="flex flex-col gap-4 rounded-xl p-4">
        <Toast />

        <DataTable :value="rows" v-model:filters="filters" dataKey="id" stripedRows showGridlines paginator :rows="10"
            :globalFilterFields="[
                'protected_area_staff.name',
                'protected_area_staff.protected_area.pa_code',
                'month'
            ]">
            <!-- Protected Area -->
            <Column header="Protected Area">
                <template #body="{ data }">
                    {{ data.protected_area?.pa_code ?? 'N/A' }}
                </template>
            </Column>

            <!-- Quarter -->
            <Column field="quarter" header="Quarter" sortable />

            <!-- Activities -->
            <Column header="Activities Under Regular Monitoring">
                <template #body="{ data }">
                    {{ data.activities }}
                </template>
            </Column>

            <!-- Details -->
            <Column header="Details">
                <template #body="{ data }">
                    {{ data.details ?? 'N/A' }}
                </template>
            </Column>

            <!-- Uploaded Files -->
            <Column header="Uploaded Files / Reports" style="width: 160px">
                <template #body="{ data }">
                    <Button label="Submit" icon="pi pi-check" @click="viewFiles(data.uploaded_files)" style="background:#004d40"
                        :disabled="!data.uploaded_files?.length"> <Info/> </Button>
                    
                </template>

            </Column>
        </DataTable>
    </div>
    <Dialog v-model:visible="imageDialog" header="Uploaded Files" :style="{ width: '70vw' }" modal :draggable="false">
        <div v-if="selectedFiles.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="file in selectedFiles" :key="file.id" class="border rounded-lg p-2">
                <iframe :src="getEmbedUrl(file.file_url)" width="100%" height="500"
                    allow="autoplay"></iframe>
                <div class="mt-2 text-center text-sm font-medium">
                    {{ file.file_name }}
                </div>
            </div>
        </div>

        <div v-else class="text-center py-6">
            No uploaded images.
        </div>
    </Dialog>
</template>

<style scoped>
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #1a3a6b;
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
}
</style>