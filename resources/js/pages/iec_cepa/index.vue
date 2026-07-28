<script setup lang="ts">

import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'


import Button from 'primevue/button'

import IECCEPATable from './table/IECCEPATable.vue'
import modal_iec_cepa_insert from './modal/modal_iec_cepa_insert.vue'
import { useSubmissionMonitoring } from '@/composables/useMonitoring'

const page = usePage()
const props = defineProps({
    protectedAreas: Array,
    paStaff: Array,
});


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hired Staff',
        href: '/hired_staff/index',
    },
]

// ✅ FORM
const form = useForm({
    protected_area_id: '',
    pa_staff_id: '',
    quarter: null,
    months: [] as string[],
    accomplishments: [] as string[],
})

// ✅ PAGE PROPS




// ✅ CONFIRM
const confirm = useConfirm()
const activeView = ref<'accomplishment'>('accomplishment');

const showInsertDialog = () => {

    confirm.require({
        group: 'headless',
        header: 'IEC/CEPA Monitoring',
        message: '',
        reject: () => {
            console.log('Insert cancelled')
        }
    })
}

// ✅ MONITORING
const {
    data,
    totals,
    loading,
    getIEC
} = useSubmissionMonitoring()

// ✅ FETCH
onMounted(() => {
    getIEC({
        year: new Date().getFullYear()
    })
})

</script>
<template>

    <Head title="DENR Online Protected Area Information System" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6 p-4">

            <!-- HEADER -->
            <div class="box">

                <h2 class="title">IEC/CEPA Monitoring</h2>

                <div class="ict-hero-banner">
                    <div class="ict-qp-label">
                        DENR E-PARS
                    </div>

                    <h1 class="ict-hero-title">
                        Information, Education and Communication and Communication, Education and Public Awareness
                        Report per Protected Area
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
                                Monitoring of IEC/CEPA per Protected Area
                            </h5>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Real-time tracking of IEC/CEPA activities per protected area
                            </p>

                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center">

                        <Button @click="showInsertDialog"
                            class="flex items-center gap-2 text-white text-xs px-4 py-2 rounded-lg hover:bg-[#145c3b] transition h-[32px]" style="background-color: #004d40;">

                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.5">

                                <path d="M12 5v14M5 12h14"></path>
                            </svg>

                            <span>Add / Insert</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="box">
                <div class="flex flex-wrap gap-3">
                    <Button label="Consolidated IEC/CEPA Report" icon="pi pi-chart-bar"
                        style="background:#004d40"
                        @click="activeView = 'accomplishment'" />
                </div>
                <div v-if="activeView === 'accomplishment'" class="mt-4">
                    <IECCEPATable :rows="data" :totals="totals" :loading="loading" />

                </div>
                <div v-if="activeView === 'tabular'" class="mt-4">
                </div>

            </div>

        </div>

        <!-- MODAL -->
        <modal_iec_cepa_insert :form="form" :protectedAreas="props.protectedAreas" :paStaff="props.paStaff" />

    </AppLayout>

</template>

<style>
@import url('../../../css/style.css');
</style>