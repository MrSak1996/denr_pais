<script lang="ts" setup>
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { router } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi';
import { useConfirm } from 'primevue/useconfirm';
import { ref, watch, onMounted, toRef, computed } from 'vue';

const confirm = useConfirm();
const isLoading = ref(false);

const props = defineProps({
    protectedAreas: { type: Array, default: () => [] },
    paStaff: { type: Array, default: () => [] },
    form: { type: Object, required: true },
});


const paOptions = computed(() =>
    (props.protectedAreas || []).map((pa: any) => ({
        label: pa.pa_code,
        value: pa.id,
    })),
);

const pa_staff_opts = ref([]);

/* reactive form field */
const protected_area_id = toRef(props.form, 'protected_area_id');


/* MONTHS */
const monthMap: Record<string, number> = {
    jan: 1, feb: 2, mar: 3, apr: 4,
    may: 5, jun: 6, jul: 7, aug: 8,
    sep: 9, oct: 10, nov: 11, dec: 12
};

const months = ref([
    { label: 'Jan', value: 'jan' },
    { label: 'Feb', value: 'feb' },
    { label: 'Mar', value: 'mar' },
    { label: 'Apr', value: 'apr' },
    { label: 'May', value: 'may' },
    { label: 'Jun', value: 'jun' },
    { label: 'Jul', value: 'jul' },
    { label: 'Aug', value: 'aug' },
    { label: 'Sep', value: 'sep' },
    { label: 'Oct', value: 'oct' },
    { label: 'Nov', value: 'nov' },
    { label: 'Dec', value: 'dec' },
]);



const selectedMonths = ref<string[]>([]);
const accomplishments = ref<string[]>(['']);

/* ---------------- LOAD STAFF ---------------- */
const loadPAStaff = async (pa_id: any) => {
    const id = Number(pa_id);

    if (!id) {
        pa_staff_opts.value = [];
        return;
    }

    try {
        const res = await axios.get(`/api/hired_staff/${id}/pa_staff`);

        pa_staff_opts.value = res.data.map((item: any) => ({
            id: Number(item.id),
            name: item.name + "" + (item.position ? ` - ${item.position}` : ''),
        }));
    } catch (error) {
        console.error('loadPAStaff error:', error);
    }
};

/* ---------------- WATCH PROTECTED AREA ---------------- */
watch(
    protected_area_id,
    async (pa_id, oldVal) => {
        console.log('protected_area_id changed:', pa_id);

        // reset selected staff
        props.form.pa_staff_id = null;

        // load corresponding staff
        await loadPAStaff(pa_id);

        if (oldVal && pa_id !== oldVal) {
            props.form.i_city_mun = '';
            props.form.i_barangay = '';
        }
    },
    { immediate: true }
);

/* ---------------- FORM ACTIONS ---------------- */
const addTextarea = () => accomplishments.value.push('');

const removeTextarea = (index: number) => {
    accomplishments.value.splice(index, 1);
};

const submitForm = (acceptCallback: () => void) => {
    isLoading.value = true;

    props.form.months = selectedMonths.value.map(m => monthMap[m]);
    props.form.accomplishments = accomplishments.value;

    props.form.post(route('hired_staff.store'), {
        preserveScroll: true,
        onSuccess: () => {
            props.form.reset();
            selectedMonths.value = [];
            accomplishments.value = [''];
            router.visit(window.location.pathname)
            acceptCallback()

        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

/* ---------------- INIT ---------------- */
onMounted(() => {

});
</script>

<template>
    <ConfirmDialog group="insert-accomplishment">
        <template #container="{ message, acceptCallback, rejectCallback }">
            <div class="flex w-[800px] max-w-full flex-col items-center rounded-xl bg-white p-6 shadow-lg">

                <!-- LOGO -->
                <div class="-mt-16 flex h-20 w-20 items-center justify-center rounded-full bg-white shadow">
                    <img src="../../../../images/denr_logo.png" class="h-18 w-18" />
                </div>

                <!-- HEADER -->
                <span class="mt-4 text-xl font-bold">
                    {{ message.header }}
                </span>

                <p class="mb-4 text-center text-sm text-gray-500">
                    {{ message.message }}
                </p>

                <!-- FORM -->
                <div class="w-full space-y-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Protected Area -->
                        <div>
                            <label class="text-sm font-medium">Protected Area</label>
                            <Select class="w-full" filter :options="props.protectedAreas" optionLabel="pa_code"
                                optionValue="id" v-model="form.protected_area_id" placeholder="Select Protected Area" />
                        </div>

                        <!-- Staff -->
                        <div>
                            <label class="text-sm font-medium">Protected Area Staff</label>
                            <MultiSelect  class="w-full" filter :options="pa_staff_opts" optionLabel="name" optionValue="id" display="chip" 
                                v-model="form.pa_staff_id" placeholder="Select Staff" />
                        </div>

                    </div>

                    <!-- MONTHS -->
                    <div>
                        <label class="text-sm font-medium">Months Submitted</label>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <div v-for="m in months" :key="m.value" class="flex items-center gap-2">
                                <Checkbox v-model="selectedMonths" :value="m.value" />
                                <label class="text-xs">{{ m.label }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- ACCOMPLISHMENTS -->
                    <div>
                        <label class="text-sm font-medium">Accomplishment Report</label>

                        <div class="mt-2 max-h-[200px] space-y-2 overflow-y-auto pr-2">
                            <div v-for="(item, index) in accomplishments" :key="index" class="flex gap-2">
                                <Textarea v-model="accomplishments[index]" rows="2" autoResize class="w-full"
                                    placeholder="Enter accomplishment..." />

                                <button v-if="accomplishments.length > 1" @click="removeTextarea(index)"
                                    class="text-xs text-red-500">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <Button label="Add More" icon="pi pi-plus" text class="mt-2 text-xs" @click="addTextarea" />
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="mt-6 flex gap-3">
                    <Button label="Submit"    @click="submitForm(acceptCallback)" style="background-color: #004d40"
                        :disabled="isLoading" />
                    <Button label="Cancel" outlined @click="rejectCallback" :disabled="isLoading" />
                </div>

            </div>
        </template>
    </ConfirmDialog>
</template>