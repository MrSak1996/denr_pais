<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { router, usePage } from '@inertiajs/vue3'
import { FilterMatchMode } from '@primevue/core/api'

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

const expandedRows = ref<Record<number, boolean>>({})

/* ---------------------------
   FILTERS
----------------------------*/
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    pa_code: { value: null, matchMode: FilterMatchMode.EQUALS },
    month: { value: null, matchMode: FilterMatchMode.EQUALS },
})

/* ---------------------------
   DATA
----------------------------*/
const data = ref(
    ((page.props.data as any[]) || []).map((r) => ({
        ...r,
        accomplishments: (() => {
            try {
                return typeof r.accomplishments === 'string'
                    ? JSON.parse(r.accomplishments)
                    : r.accomplishments ?? []
            } catch {
                return [r.accomplishments]
            }
        })()
    }))
)

/* ---------------------------
   FILTERED DATA (Select works properly)
----------------------------*/
const filteredData = computed(() => {
    let rows = data.value

    // PA Code filter
    if (filters.value.pa_code.value) {
        rows = rows.filter(row =>
            row.protected_area_staff?.protected_area?.pa_code === filters.value.pa_code.value
        )
    }

    // Global search
    if (filters.value.global.value) {
        const search = filters.value.global.value.toLowerCase()

        rows = rows.filter(row =>
            row.protected_area_staff?.name?.toLowerCase().includes(search) ||
            row.protected_area_staff?.protected_area?.pa_code?.toLowerCase().includes(search) ||
            String(row.month).includes(search)
        )
    }

    return rows
})

/* ---------------------------
   HELPERS
----------------------------*/
const normalizeAccomplishments = (value: any) => {
    if (!value) return []
    if (Array.isArray(value)) return value

    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value)
            return Array.isArray(parsed) ? parsed : [parsed]
        } catch {
            return [value]
        }
    }

    return []
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
   INIT
----------------------------*/
onMounted(() => {
    fetchDashboardData()
})
</script>

<template>
    <div class="flex flex-col gap-4 rounded-xl p-4">
        <Toast />

        <DataTable :value="filteredData" v-model:filters="filters" v-model:expandedRows="expandedRows" dataKey="id"
            stripedRows showGridlines paginator :rows="10" :rowsPerPageOptions="[5, 10, 25]" filterDisplay="menu"
            responsiveLayout="scroll" class="w-full text-sm" :globalFilterFields="[
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
                    <Select v-model="filters.global.value" :options="[
                        'APLS', 'AWFR', 'BPL', 'BRWFR', 'CIPLS', 'CPLS',
                        'CWFR', 'HTPL', 'IWFR', 'KRFR', 'MBSCPL', 'MIGRBS',
                        'MPPMNGPL', 'MSPL', 'PaWFR', 'PMSFR', 'PoWFR',
                        'PP 1636', 'PPL', 'QPL', 'SFPL', 'TVPL', 'UMRBPL', 'URWR'
                    ]" placeholder="Filter PA Code" class="w-full md:w-48" filter showClear />

                </div>
            </template>

            <!-- PA CODE -->
        

            <!-- STAFF -->
            <Column header="Protected Area">
                <template #body="{ data }">
                    {{ data.protected_area_staff?.name ?? 'N/A' }}
                </template>
            </Column>

            <!-- POSITION -->
            <Column header="Activities Under Regular Monitoring">
                <template #body="{ data }">
                    {{ data.protected_area_staff?.position ?? 'N/A' }}
                </template>
            </Column>

            <!-- MONTH -->
            <Column header="Details" sortable>
                <template #body="{ data }">
                    {{ formatMonth(data.month) }}
                </template>
            </Column>

            <!-- ACCOMPLISHMENTS -->
            <Column header="Uploaded Files / Reports">
                <template #body="{ data }">
                    <ul class="list-disc pl-4">
                        <li v-for="(item, i) in normalizeAccomplishments(data.accomplishments)" :key="i">
                            {{ item }}
                        </li>
                    </ul>
                </template>
            </Column>

        </DataTable>
    </div>
</template>

<style scoped>
:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #1a3a6b;
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
}
</style>