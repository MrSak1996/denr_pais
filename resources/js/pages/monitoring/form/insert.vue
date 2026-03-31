<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import { Input } from '@/components/ui/input';
import { useAppForm } from '@/composables/useAppForm';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Info } from 'lucide-vue-next';

const { chainsaw_form, chainsaw } = useAppForm();

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

// Status options for dropdown
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

function submitForm() {
    console.log('Form submitted:', chainsaw_form);
    // Add submission logic here
}

function resetForm() {
    Object.keys(chainsaw_form).forEach((key) => (chainsaw_form[key] = ''));
}
</script>

<template>
  <Head title="DENR Online Protected Area Information System" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 rounded-xl p-4">

      <div class="box">
        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">
          <Info /> Resolution Details
        </h3>

        <div class="grid gap-6 md:grid-cols-4">

          <div class="flex flex-col gap-1">
            <Label for="resolution_no">Resolution No.</Label>
            <Input id="resolution_no" v-model="chainsaw_form.resolution_no" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="type_of_meeting">Types of Meeting</Label>
            <Input id="type_of_meeting" v-model="chainsaw_form.type_of_meeting" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="date_of_meeting">Date of Meeting</Label>
            <DatePicker id="date_of_meeting" v-model="chainsaw_form.date_of_meeting" style="height:39px;" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="resolution_title">Resolution Title</Label>
            <Input id="resolution_title" v-model="chainsaw_form.resolution_title" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="approved_pamb_no">Approved PAMB Clearance No.</Label>
            <Input id="approved_pamb_no" v-model="chainsaw_form.approved_pamb_no" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="documents_submitted">(If Deferred, State Reason)</Label>
            <Input id="documents_submitted" v-model="chainsaw_form.documents_submitted" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="status">Status / Remarks</Label>
            <Select :options="statusOptions" v-model="chainsaw_form.status" style="height: 39px;border: 1px solid black;" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="rating">Rating (c/o CDD Planning Officer)</Label>
            <Select :options="ratingOptions" v-model="chainsaw_form.rating" style="height: 39px;border: 1px solid black;" />
          </div>
          <div class="flex flex-col gap-1">
            <Label for="date_submitted_hardcopy">Date Received by CDD</Label>
            <DatePicker id="date_submitted_hardcopy" v-model="chainsaw_form.date_submitted_hardcopy" style="height:39px;" />
          </div>
          <div class="flex flex-col gap-1">
            <Label for="date_submitted_hardcopy">Date Received by Focal</Label>
            <DatePicker id="date_submitted_hardcopy" v-model="chainsaw_form.date_submitted_hardcopy" style="height:39px;" />
          </div>
          <div class="flex flex-col gap-1">
            <Label for="date_submitted_hardcopy">Date Submitted/Released by Focal</Label>
            <DatePicker id="date_submitted_hardcopy" v-model="chainsaw_form.date_submitted_hardcopy" style="height:39px;" />
          </div>
          <div class="flex flex-col gap-1">
            <Label for="date_submitted_hardcopy">Date Approved by the PAMB Chair</Label>
            <DatePicker id="date_submitted_hardcopy" v-model="chainsaw_form.date_submitted_hardcopy" style="height:39px;" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="date_emailed_bmb">Date emailed (BMB)</Label>
            <DatePicker id="date_emailed_bmb" v-model="chainsaw_form.date_emailed_bmb" style="height:39px;" />
          </div>

          <div class="flex flex-col gap-1">
            <Label for="date_submitted_hardcopy">Date submitted to BMB (Hard copy)</Label>
            <DatePicker id="date_submitted_hardcopy" v-model="chainsaw_form.date_submitted_hardcopy" style="height:39px;" />
          </div>

        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end gap-2">
        <Button class="p-button-secondary" @click="resetForm">Cancel</Button>
        <Button class="p-button-primary" @click="submitForm">Submit</Button>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped src="../../../../css/style.css"></style>