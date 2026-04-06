<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';
import DatePicker from 'primevue/datepicker';
import Select from 'primevue/select';
import FloatLabel from 'primevue/floatlabel';
import { type BreadcrumbItem } from '@/types';

const form = useForm({
    resolution_no: '',
    type_of_meeting: '',
    date_of_meeting: '',
    resolution_title: '',
    approved_pamb_no: '',
    documents_submitted: '',

    status: '',
    rating: '',

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
        onSuccess: () => form.reset(),
    });
}
</script>
<template>

    <Head title="Monitoring Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-7xl mx-auto">

            <!-- Header -->
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <Info /> Add New Resolution
            </h2>

            <!-- SECTION 1 -->
            <div class="bg-white shadow rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-lg mb-4">📌 Basic Information</h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <Input v-model="form.resolution_no" placeholder="Resolution No." />
                    <Input v-model="form.type_of_meeting" placeholder="Type of Meeting" />

                    <DatePicker v-model="form.date_of_meeting" showIcon placeholder="Date of Meeting" />
                    <Input v-model="form.resolution_title" placeholder="Resolution Title" />

                    <Input v-model="form.approved_pamb_no" placeholder="Approved PAMB No." />
                    <Input v-model="form.documents_submitted" placeholder="Reason (if deferred)" />
                </div>
            </div>

            <!-- SECTION 2 -->
            <div class="bg-white shadow rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-lg mb-4">📌 Status & Evaluation</h3>

                <div class="grid md:grid-cols-2 gap-6">
                    <Select :options="statusOptions" optionLabel="label" optionValue="value" v-model="form.status"
                        placeholder="Select Status" />

                    <Select :options="ratingOptions" optionLabel="label" optionValue="value" v-model="form.rating"
                        placeholder="Select Rating" />
                </div>
            </div>

            <!-- SECTION 3 -->
            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">📌 Timeline Tracking</h3>

                <div class="grid md:grid-cols-3 gap-6">
                    <DatePicker v-model="form.date_received_cdd" showIcon placeholder="Received (CDD)" />
                    <DatePicker v-model="form.date_received_focal" showIcon placeholder="Received (Focal)" />
                    <DatePicker v-model="form.date_released_focal" showIcon placeholder="Released" />

                    <DatePicker v-model="form.date_approved_pamb" showIcon placeholder="Approved" />
                    <DatePicker v-model="form.date_emailed_bmb" showIcon placeholder="Emailed (BMB)" />
                    <DatePicker v-model="form.date_submitted_bmb" showIcon placeholder="Submitted (Hard Copy)" />
                </div>
            </div>

            <!-- Errors -->
            <div v-if="Object.keys(form.errors).length" class="mt-4 text-red-500">
                <div v-for="(error, key) in form.errors" :key="key">
                    {{ error }}
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex justify-end gap-3">
                <Button variant="outline" @click="form.reset()">
                    Cancel
                </Button>

                <Button @click="submitForm" :disabled="form.processing">
                    Submit
                </Button>
            </div>
        </div>
    </AppLayout>
</template>