<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import Select from 'primevue/select'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import Tag from 'primevue/tag'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'
import Divider from 'primevue/divider'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast';

const page = usePage()
const isLoading = ref(false);
const toast = useToast();


interface props {
    accomplishmentReport: any;
}
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Hired Staff',
        href: '/hired_staff/accomplishment',
    },
]
const props = defineProps<props>();
console.log('accomplishmentReport:', props.accomplishmentReport);

const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
]

const form = useForm({
    month: props.accomplishmentReport.month,
    accomplishments: [...props.accomplishmentReport.accomplishments],
})
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}
const save = () => {
    isLoading.value = true;

    form.put(
        route('hired_staff.accomplishment.update', props.accomplishmentReport.id),
        {
            preserveScroll: true,

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

            },

            onError: (errors) => {
                console.log(errors)
            },

            onFinish: () => {
                isLoading.value = false;
            }
        }
    )
}
</script>
<template>

    <Head title="DENR Online Protected Area Information System" />
    <Toast />
    <AppLayout :breadcrumbs>
        <div class="space-y-6 p-4">

            <!-- ================= HEADER ================= -->

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

                <div class="flex items-center gap-3 mt-5">

                    <div
                        class="w-12 h-12 rounded-xl bg-[#1a6b45] flex items-center justify-center text-white font-bold text-lg">

                        {{ accomplishmentReport.protected_area_staff.name.charAt(0) }}

                    </div>

                    <div>

                        <h1 class="text-xl font-bold">

                            {{ accomplishmentReport.protected_area_staff.name.toUpperCase() }}

                            -

                            {{ accomplishmentReport.protected_area_staff.protected_area.pa_code }}

                        </h1>

                        <p class="text-gray-500">

                            {{ accomplishmentReport.protected_area_staff.position }}

                        </p>

                    </div>

                </div>

            </div>

            <!-- ================= SUMMARY ================= -->

            <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-4">

                <div class="bg-white border rounded-xl p-5 shadow-sm">

                    <p class="text-gray-500 text-lg">
                        Protected Area
                    </p>

                    <h2 class="font-semibold mt-2">

                        {{ accomplishmentReport.protected_area_staff.protected_area.pa_name }}

                    </h2>

                </div>

                <div class="bg-white border rounded-xl p-5 shadow-sm">



                </div>

                <div class="bg-white border rounded-xl p-5 shadow-sm">

                    <p class="text-gray-500 text-lg">
                        Total Accomplishments
                    </p>

                    <h2 class="font-semibold text-green-700 mt-2">

                        {{ form.accomplishments.length }}

                    </h2>

                </div>

                <div class="bg-white border rounded-xl p-5 shadow-sm">

                    <p class="text-gray-500 text-lg">
                        Status
                    </p>

                    <Tag :value="accomplishmentReport.is_submitted ? 'Submitted' : 'Draft'"
                        :severity="accomplishmentReport.is_submitted ? 'success' : 'warn'" class="mt-2" />

                </div>

            </div>

            <!-- ================= REPORT DETAILS ================= -->

            <div class="box">

                <h2 class="font-semibold text-lg mb-4">

                    Report Information

                </h2>

                <Divider />

                <div class="grid md:grid-cols-3 gap-6">

                    <div>

                        <label class="text-gray-500 text-sm">

                            Created

                        </label>

                        <p class="font-medium">

                            {{ formatDate(accomplishmentReport.created_at) }}

                        </p>

                    </div>

                    <div>

                        <label class="text-gray-500 text-sm">

                            Last Updated

                        </label>

                        <p class="font-medium">

                            {{ formatDate(accomplishmentReport.updated_at) }}

                        </p>

                    </div>

                    <div>

                        <label class="text-gray-500 text-lg">

                            Month

                        </label>

                        <Select class="w-full mt-2" :options="months" v-model="form.month" />

                    </div>

                </div>

            </div>

            <!-- ================== ACTUAL ACCOMPLISHMENT REPORT ================= -->
            <!-- ================== ACTUAL ACCOMPLISHMENT REPORT ================= -->
            <!-- ================== ACTUAL ACCOMPLISHMENT REPORT ================= -->
            <div class="box">

                <!-- Header -->
                <div class="flex items-start gap-4">

                    <div
                        class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center shrink-0">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16l4-4a2 2 0 012.828 0L14 16m-2-2l1-1a2 2 0 012.828 0L21 18m-2-14H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />

                        </svg>

                    </div>

                    <div>

                        <h2 class="text-lg font-semibold text-gray-800">
                            Actual Accomplishment Report
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Uploaded photos documenting the accomplished activities.
                        </p>

                    </div>

                </div>

                <Divider class="my-6" />

                <!-- ================= Gallery ================= -->

                <div v-if="accomplishmentReport.images?.length"
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">

                    <div v-for="(image, index) in accomplishmentReport.images" :key="index"
                        class="group rounded-xl overflow-hidden border bg-white shadow hover:shadow-lg transition">

                        <img :src="image.url"
                            class="w-full h-56 object-cover transition duration-300 group-hover:scale-105" />

                    </div>

                </div>

                <!-- ================= Empty State ================= -->

                <div v-else class="border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 py-14">

                    <div class="flex flex-col items-center">

                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16l4-4a2 2 0 012.828 0L14 16m-2-2l1-1a2 2 0 012.828 0L21 18m-2-14H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z" />

                            </svg>

                        </div>

                        <h3 class="mt-5 text-lg font-semibold text-gray-700">
                            No Images Uploaded
                        </h3>

                        <p class="mt-2 text-sm text-gray-500 text-center max-w-md">
                            There are currently no uploaded photos for this accomplishment report.
                            Once images are uploaded, they will appear here.
                        </p>

                    </div>

                </div>

            </div>
            <!-- ================= ACCOMPLISHMENTS ================= -->

            <div class="box">

                <h2 class="font-semibold text-lg mb-5">

                    Accomplishments

                </h2>

                <div v-for="(item, index) in form.accomplishments" :key="index"
                    class="border rounded-xl shadow-sm mb-5 overflow-hidden">

                    <!-- Header -->

                    <div class="bg-gray-50 border-b px-5 py-3 flex items-center gap-3">

                        <div
                            class="w-9 h-9 rounded-full bg-green-700 text-white flex items-center justify-center font-bold">

                            {{ index + 1 }}

                        </div>

                        <div>

                            <h3 class="font-semibold">

                                Accomplishment {{ index + 1 }}

                            </h3>

                            <p class="text-xs text-gray-500">

                                Describe the completed activity.

                            </p>

                        </div>

                    </div>

                    <!-- Body -->

                    <div class="p-5">

                        <Textarea v-model="form.accomplishments[index]" rows="4" autoResize class="w-full" />

                    </div>

                </div>

            </div>

            <!-- ================= BUTTONS ================= -->

            <div class="flex justify-end gap-3">

                <Button label="Cancel" severity="secondary" icon="pi pi-times" />

                <Button label="Save Changes" icon="pi pi-save" severity="success" @click="save()"
                    :disabled="isLoading" />

            </div>

        </div>


    </AppLayout>
</template>
<style>
@import url('../../../../css/style.css');
</style>