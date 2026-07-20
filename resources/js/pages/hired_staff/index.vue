<script setup lang="ts">

import { ref, computed, onMounted } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import { useConfirm } from 'primevue/useconfirm'

import HiredStaffTable from '@/components/HiredStaffTable.vue'
import modal_form_insert from './modal/modal_form_insert.vue'
import modal_staff_insert from './modal/modal_staff_insert.vue'
import AccomplishmentTable from './table/accomplishment_table.vue'

import { useSubmissionMonitoring } from '@/composables/useMonitoring'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const page = usePage()
const props = defineProps({
    protectedAreas: Array,
    paStaff: Array,
});

const showPAStaffModal = ref(false)
const isLoading = ref(false);
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
    months: [] as string[],
    accomplishments: [] as string[],
    name: '',
    position: ''
})

// ✅ PAGE PROPS




// ✅ CONFIRM
const confirm = useConfirm()
const activeView = ref<'tabular' | 'accomplishment'>('tabular');

const showInsertDialog = () => {
    confirm.require({
        group: 'insert-accomplishment',
        header: 'Insert Hired Staff Accomplishment Report',
        message: 'Do you want to add a new hired staff record?',
    })
}

const showPAStaffDialog = () => {
    showPAStaffModal.value = true
}

const closePAStaffDialog = () => {
    showPAStaffModal.value = false
    form.reset()
    form.clearErrors()
}

const savePAStaff = () => {
    form.post(route('pa_staff.store'), {
        preserveScroll: true,

        onStart: () => {
            isLoading.value = true
        },

        onSuccess: () => {
            const message = page.props.flash?.success
            if (message) {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: message,
                    life: 3000,
                })
            }
            closePAStaffDialog()
        },

        onError: (errors) => {
            console.log(errors)
        },

        onFinish: () => {
            isLoading.value = false
        }
    })
}
// ✅ MONITORING
const {
    data,
    totals,
    loading,
    fetchSubmissionMonitoring
} = useSubmissionMonitoring()

// ✅ FETCH
onMounted(() => {

    fetchSubmissionMonitoring({
        year: new Date().getFullYear()
    })
})

</script>
<template>

    <Head title="DENR Online Protected Area Information System" />
    <Toast />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6 p-4">

            <!-- HEADER -->
            <div class="box">

                <h2 class="title">Hired Staff Monitoring</h2>

                <div class="ict-hero-banner">
                    <div class="ict-qp-label">
                        DENR E-PARS
                    </div>

                    <h1 class="ict-hero-title">
                        Monitoring of Accomplishment Report per Protected Area
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
                                Monitoring of Accomplishment Report per Protected Area
                            </h5>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Real-time tracking of hired staff accomplishment reports
                            </p>

                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center">
                        <Button @click="showPAStaffDialog"
                            class="flex items-center gap-2 mr-4 bg-[#303F9F] text-white text-xs px-4 py-2 rounded-lg hover:bg-[#1A237E] transition h-[32px]">

                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.5">

                                <path d="M12 5v14M5 12h14"></path>
                            </svg>

                            <span>Add Staff</span>
                        </Button>
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

            <!-- TABLE -->
            <div class="box">
                <div class="flex flex-wrap gap-3">

                    <Button label="Tabular Data" icon="pi pi-table"
                        :severity="activeView === 'tabular' ? 'primary' : 'secondary'"
                        @click="activeView = 'tabular'" />
                    <Button label="Consolidated Accomplishment Report" icon="pi pi-chart-bar"
                        :severity="activeView === 'accomplishment' ? 'primary' : 'secondary'"
                        @click="activeView = 'accomplishment'" />
                </div>
                <div v-if="activeView === 'accomplishment'" class="mt-4">
                    <AccomplishmentTable />
                </div>
                <div v-if="activeView === 'tabular'" class="mt-4">
                    <HiredStaffTable :rows="data" :totals="totals" :loading="loading" />
                </div>

            </div>

        </div>

        <!-- MODAL -->
        <modal_form_insert :form="form" :protectedAreas="props.protectedAreas" :paStaff="props.paStaff" />
        <Dialog v-model:visible="showPAStaffModal" modal header="PA Staff Form" :style="{ width: '30vw' }">
            <div class="flex max-w-full flex-col items-center rounded-xl bg-white p-6">



                <div class="w-full space-y-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label>Protected Area</label>
                            <Select class="w-full" filter :options="props.protectedAreas" optionLabel="pa_code"
                                optionValue="id" v-model="form.protected_area_id" />
                        </div>

                        <div>
                            <label>Protected Area Staff</label>
                            <InputText class="w-full" v-model="form.name" />
                        </div>


                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <div>
                            <label>Position</label>
                            <InputText class="w-ful l" v-model="form.position" />
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex gap-3">
                    <Button label="Submit" @click="savePAStaff" />
                    <Button label="Cancel" outlined @click=closePAStaffDialog />
                </div>

            </div>
        </Dialog>
    </AppLayout>

</template>

<style>
@import url('../../../css/style.css');
</style>z