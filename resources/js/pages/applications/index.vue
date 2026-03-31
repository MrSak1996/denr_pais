<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Folder, Info } from 'lucide-vue-next';
import ToggleButton from 'primevue/togglebutton';
import { ref, watch, onMounted } from 'vue';
import application_form from './forms/application_form.vue';
import company_application_form from './forms/company_application_form.vue';
import individual_img from '../../../images/man.png';
import company_img from '../../../images/office-building.png';
// ---------------------
// STATE
// ---------------------
const checked = ref<boolean | null>(null);
const hasSelected = ref(false);

// ---------------------
// BREADCRUMBS
// ---------------------
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applications', href: '/applicants/index' },
];

// ---------------------
// UTILITY: Get URL Parameter Safely
// ---------------------
const getApplicationTypeFromUrl = (): string | null => {
    const params = new URLSearchParams(window.location.search);
    return params.get('type');
};

// ---------------------
// UI REACTION: When user toggles button
// ---------------------
watch(checked, (value) => {
    if (value !== null) {
        hasSelected.value = true;
    }
});

const showIndividualApplicant = async () => {
    checked.value = false;
}

// ---------------------
// AUTO-SELECT BASED ON URL
// ---------------------
onMounted(() => {
    const type = getApplicationTypeFromUrl();

    if (type === 'individual') {
        checked.value = false;
        hasSelected.value = true;
    }
    else if (type === 'company') {
        checked.value = true;
        hasSelected.value = true;
    }
});
</script>


<style scoped>
.box {
    background-color: #fff;
    border-top: 4px solid #00943a;
    margin-bottom: 20px;
    padding: 20px;
    -moz-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    -o-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    -webkit-box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.box .title {
    border-bottom: 1px solid #e0e0e0;
    color: #432c0b !important;
    font-weight: bold;
    margin-bottom: 20px;
    margin-top: 0;
    padding-bottom: 10px;
    padding-top: 0;
    text-transform: uppercase;
    font-size: 10pt;
}

/* Base style for ToggleButton - Green (unchecked/default state) */
/* Default state - Green */
/* Base style for ToggleButton - Green (unchecked/default state) */
.p-togglebutton {
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    border: none;
    background-color: #22c55e;
    /* green-500 */
    color: white;
    transition: background-color 0.3s ease, filter 0.3s ease;
}

/* Hover effect */
.p-togglebutton:hover {
    filter: brightness(1.1);
}

/* Checked state - Darker green */
.p-togglebutton.p-togglebutton-checked {
    background-color: #15803d !important;
    /* green-700 */
    border-color: #166534;
    color: rgb(0, 0, 0);
}

/* Fix inner white background */
.p-togglebutton.p-togglebutton-checked .p-togglebutton-content {
    background-color: #15803d !important;
    box-shadow: none;
    color: white !important;
}

/* Ensure label and icon are white in all states */
.p-togglebutton .p-togglebutton-icon,
.p-togglebutton .p-togglebutton-label {
    color: white !important;
}
</style>


<template>

    <Head title="Chainsaw Permit Application" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex flex-col gap-6 rounded-xl p-4 sm:grid-cols-3">
            <div class="flex items-center gap-2 text-sm">
                <Folder class="h-5 w-5" />
                <h1 class="text-xl font-semibold">Chainsaw Purchase Application Form</h1>
            </div>


            <!-- SHOW BUTTONS ONLY BEFORE SELECTION -->
            <div v-if="!hasSelected" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <button @click="() => { checked = true; hasSelected = true }"
                        class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl shadow-md border bg-blue-200 text-blue-700 hover:bg-blue-300 hover:text-blue-900 transition-all duration-200">
                        <img :src="company_img" alt="Company Applicant" class="h-36 w-36" />
                        <span class="text-lg font-semibold">Government</span>
                    </button>
                    <!--  SAME AS CITIZEN-->





                    <!-- Individual Applicant -->
                       <button @click="() => { checked = true; hasSelected = true }"
                        class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl shadow-md border bg-blue-200 text-blue-700 hover:bg-blue-300 hover:text-blue-900 transition-all duration-200">
                        <img :src="company_img" alt="Company Applicant" class="h-36 w-36" />
                        <span class="text-lg font-semibold">Business</span>
                    </button>

                    <button @click="() => { checked = false; hasSelected = true }"
                        class="flex flex-col items-center justify-center gap-3 p-6 rounded-2xl shadow-md border bg-green-600 text-white hover:bg-green-700 hover:text-yellow-100 transition-all duration-200">
                        <img :src="individual_img" alt="Individual Applicant" class="h-36 w-36" />
                        <span class="text-lg font-semibold">Citizen</span>
                    </button>

                    <!-- Business / Private Corporation -->
                  
               



            </div>

            <!-- SHOW FORM AFTER SELECTION -->
            <div v-if="hasSelected">
                <application_form v-if="checked === false" />
                <company_application_form v-else />
            </div>



        </div>
    </AppLayout>
</template>
