<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useToast } from 'primevue/usetoast';
import { Input } from '@/components/ui/input';
import { Head, useForm } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';

import DatePicker from 'primevue/datepicker';

import Select from 'primevue/select';
import FloatLabel from 'primevue/floatlabel';
import Textarea from 'primevue/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

const toast = useToast();

const props = defineProps({
    protectedAreas: Array
});
const paOptions = computed(() =>
    (props.protectedAreas || []).map((pa: any) => ({
        label: pa.pa_code,
        value: pa.id
    }))
);
console.log(props.protectedAreas);
const form = useForm({
    resolution_no: '',
    focal_person: '',
    type_of_meeting: '',
    date_of_meeting: '',
    resolution_title: '',
    approved_pamb_no: '',
    documents_submitted: '',

    status: '',
    rating: '',

    protected_area_id: '',

    date_received_cdd: '',
    date_received_focal: '',
    date_released_focal: '',
    date_approved_pamb: '',
    date_emailed_bmb: '',
    date_submitted_bmb: '',
});

const statusOptions = [
    { label: 'Pending', value: 'Pending' },
    { label: 'Approved', value: 'Approved' },
    { label: 'Rejected', value: 'Rejected' },
];

const ratingOptions = [
    { label: 'Excellent', value: 'Excellent' },
    { label: 'Good', value: 'Good' },
    { label: 'Fair', value: 'Fair' },
    { label: 'Poor', value: 'Poor' },
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'List of Approved PAMB Resolution and Clearance',
        href: '/monitoring/index',
    },
    {
        title: 'Add New Record',
        href: '/monitoring/form/insert',
    },
];

function submitForm() {
    form.post('/monitoring/store', {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Resolution created successfully',
                life: 3000,
            });

            form.reset();
        }
    });
}
</script>
<template>
    <Toast />

    <Head title="Monitoring Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full">

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

                <!-- LEFT: FORM -->
                <div class="xl:col-span-3">
                    <div class="w-full">

                        <!-- Header -->
                        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                            <Info /> Add New Resolution
                        </h2>

                        <!-- SECTION 1 -->
                        <div class="bg-white shadow rounded-xl p-6 mb-6">
                            <h3 class="font-semibold text-lg mb-4">📌 Basic Information</h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <Select :options="paOptions" optionLabel="label" optionValue="value"
                                    v-model="form.protected_area_id" placeholder="Select Protected Area" />
                                <Input v-model="form.resolution_no" placeholder="Resolution No." />
                                <Input v-model="form.focal_person" placeholder="Focal Person" />
                                <Input v-model="form.type_of_meeting" placeholder="Type of Meeting" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_of_meeting" showIcon placeholder="Date of Meeting"
                                    style="height: 40px !important;" />

                            </div>
                            <div class="grid md:grid-cols-1 gap-6 mt-6">

                                <Textarea v-model="form.resolution_title" placeholder="Resolution Title" />
                                <Input v-model="form.approved_pamb_no" placeholder="Approved PAMB No." />
                                <Input v-model="form.documents_submitted" placeholder="Reason (if deferred)" />
                            </div>
                        </div>

                        <!-- SECTION 2 -->
                        <div class="bg-white shadow rounded-xl p-6 mb-6">
                            <h3 class="font-semibold text-lg mb-4">📌 Status & Evaluation</h3>

                            <div class="grid md:grid-cols-2 gap-6">
                                <Select :options="statusOptions" optionLabel="label" optionValue="value"
                                    v-model="form.status" placeholder="Select Status" />

                                <Select :options="ratingOptions" optionLabel="label" optionValue="value"
                                    v-model="form.rating" placeholder="Select Rating" />
                            </div>
                        </div>

                        <!-- SECTION 3 -->
                        <div class="bg-white shadow rounded-xl p-6">
                            <h3 class="font-semibold text-lg mb-4">📌 Timeline Tracking</h3>

                            <div class="grid md:grid-cols-3 gap-6">
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_received_cdd" showIcon placeholder="Received (CDD)" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_received_focal" showIcon
                                    placeholder="Received (Focal)" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_released_focal" showIcon placeholder="Released" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_approved_pamb" showIcon placeholder="Approved" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_emailed_bmb" showIcon placeholder="Emailed (BMB)" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_submitted_bmb" showIcon
                                    placeholder="Submitted (Hard Copy)" />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-end gap-3">
                            <Button variant="outline" @click="form.reset()">Cancel</Button>
                            <Button @click="submitForm" :disabled="form.processing">Submit</Button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: INSTRUCTIONS -->
                <div class="xl:col-span-1">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 sticky top-6">
                        <h3 class="font-semibold text-lg mb-3">📘 Instructions</h3>

                        <ul class="text-sm text-gray-700 space-y-3">
                            <li>
                                <strong>Resolution No.</strong><br />
                                Use official format (e.g., 2026-001).
                            </li>

                            <li>
                                <strong>Focal Person</strong><br />
                                Enter the assigned staff handling the resolution.
                            </li>

                            <li>
                                <strong>Type of Meeting</strong><br />
                                Example: Regular, Special, Emergency.
                            </li>

                            <li>
                                <strong>Status</strong><br />
                                Select current progress (Pending, Approved, Rejected).
                            </li>

                            <li>
                                <strong>Rating</strong><br />
                                Provided by CDD Planning Officer.
                            </li>

                            <li>
                                <strong>Timeline Fields</strong><br />
                                Fill only if applicable. Leave blank if not yet available.
                            </li>

                            <li class="text-red-500">
                                ⚠ Ensure all required fields are filled before submitting.
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>