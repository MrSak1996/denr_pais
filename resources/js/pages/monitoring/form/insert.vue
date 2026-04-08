<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import DatePicker from 'primevue/datepicker';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { useToast } from 'primevue/usetoast';
import { computed, ref, watch } from 'vue';
import { CirclePlus } from 'lucide-vue-next';

const toast = useToast();

const props = defineProps({
    protectedAreas: Array,
});

const paOptions = computed(() =>
    (props.protectedAreas || []).map((pa: any) => ({
        label: pa.pa_code,
        value: pa.id,
    })),
);

const checked = ref(false);

// ✅ FORM WITH ARRAY
const form = useForm({
    resolutions: [
        {
            resolution_no: '',
            type_of_meeting: '',
            date_of_meeting: '',
            resolution_title: '',
        },
    ],
    focal_person: '',
    alternate_focal: '',
    approved_pamb_clearance_no: '',
    documents_submitted: '',
    status: '',
    rating: '',
    protected_area_id: '',
});

// ✅ ADD ROW
function addRow() {
    form.resolutions.push({
        resolution_no: '',
        type_of_meeting: '',
        date_of_meeting: '',
        resolution_title: '',
    });
}

// ✅ REMOVE ROW
function removeRow(index: number) {
    if (form.resolutions.length > 1) {
        form.resolutions.splice(index, 1);
    }
}

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
    { title: 'List of Approved PAMB Resolution and Clearance', href: '/monitoring/index' },
    { title: 'Add New Record', href: '/monitoring/form/insert' },
];

// ✅ FORMAT DATE
function formatDateOnly(date: any) {
    if (!date) return null;
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

// ✅ SUBMIT
function submitForm() {
    form.resolutions = form.resolutions.map((row) => ({
        ...row,
        date_of_meeting: formatDateOnly(row.date_of_meeting),
    }));

    form.post('/monitoring/store', {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Resolution created successfully',
                life: 3000,
            });

            form.reset();
        },
    });
}

// ✅ CLEAR WHEN TOGGLE OFF
watch(checked, (val) => {
    if (!val) {
        form.approved_pamb_clearance_no = '';
        form.documents_submitted = '';
    }
});
</script>
<template>
    <Toast />

    <Head title="Monitoring Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full p-6">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
                <!-- LEFT: FORM -->
                <div class="xl:col-span-3">
                    <div class="w-full">
                        <!-- Header -->
                        <h2 class="mb-6 flex items-center gap-2 text-2xl font-bold">
                            <Info /> Add New Resolution
                        </h2>

                        <!-- SECTION 1 -->
                        <div class="mb-6 rounded-xl border-gray-300 p-6 shadow">
                            <h3 class="mb-4 text-lg font-semibold">📌 Basic Information</h3>

                            <div class="grid gap-6 md:grid-cols-2">
                                <Select :options="paOptions" optionLabel="label" optionValue="value"
                                    v-model="form.protected_area_id" placeholder="Select Protected Area" />
                                <Input v-model="form.focal_person" placeholder="Focal Person" />
                                <Input v-model="form.alternate_focal" placeholder="Alternate Focal Person" />
                            </div>
                            <div class="mt-3">
                                <Button type="button" @click="addRow">+ Add Row</Button>
                            </div>
                            <div class="mt-6 grid gap-6 md:grid-cols-1">
                                <table class="w-full border text-sm">
                                    <thead class="bg-blue-900 text-white">
                                        <tr>
                                            <th class="px-3 py-2">Resolution No</th>
                                            <th class="px-3 py-2">Type</th>
                                            <th class="px-3 py-2">Date</th>
                                            <th class="px-3 py-2">Title</th>
                                            <th class="px-3 py-2 text-center">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="(row, index) in form.resolutions" :key="index">
                                            <td class="px-3 py-2">
                                                <Input v-model="row.resolution_no" />
                                            </td>

                                            <td class="px-3 py-2">
                                                <Input v-model="row.type_of_meeting" />
                                            </td>

                                            <td class="px-3 py-2">
                                                <DatePicker v-model="row.date_of_meeting" dateFormat="yy-mm-dd"
                                                    showIcon />
                                            </td>

                                            <td class="px-3 py-2">
                                                <Textarea v-model="row.resolution_title" cols="70" />
                                            </td>

                                            <td class="px-3 py-2 text-center">
                                                <Button variant="destructive" @click="removeRow(index)">
                                                    <CirclePlus />
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-6 grid gap-6 md:grid-cols-1">
                                <div class="flex items-center gap-3">
                                    <label>Has PAMB Clearance?</label>
                                    <ToggleSwitch v-model="checked" />
                                </div>
                                <Input v-model="form.approved_pamb_clearance_no"
                                    placeholder="Approved PAMB Clearance No." :disabled="!checked" />
                                <Input v-model="form.documents_submitted" placeholder="Reason (if deferred)"
                                    :disabled="!checked" />
                            </div>
                        </div>

                        <!-- SECTION 2 -->
                        <div class="mb-6 rounded-xl bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-semibold">📌 Status & Evaluation</h3>

                            <div class="grid gap-6 md:grid-cols-2">
                                <Select :options="statusOptions" optionLabel="label" optionValue="value"
                                    v-model="form.status" placeholder="Select Status" />

                                <Select :options="ratingOptions" optionLabel="label" optionValue="value"
                                    v-model="form.rating" placeholder="Select Rating" />
                            </div>
                        </div>

                        <!-- SECTION 3 -->
                        <div class="rounded-xl bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-semibold">📌 Timeline Tracking</h3>

                            <div class="grid gap-6 md:grid-cols-3">
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_received_cdd" showIcon
                                    placeholder="Received (CDD)" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_received_focal" showIcon
                                    placeholder="Received (Focal)" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_released_focal" showIcon
                                    placeholder="Released" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_approved_pamb" showIcon
                                    placeholder="Approved" />
                                <DatePicker dateFormat="yy-mm-dd" v-model="form.date_emailed_bmb" showIcon
                                    placeholder="Emailed (BMB)" />
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
                    <div class="sticky top-6 rounded-xl border border-blue-200 bg-[#81C784] p-5">
                        <h3 class="mb-3 text-lg font-semibold">📘 Instructions</h3>

                        <ul class="space-y-3 text-sm text-gray-700">
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

                            <li class="text-red-500">⚠ Ensure all required fields are filled before submitting.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
