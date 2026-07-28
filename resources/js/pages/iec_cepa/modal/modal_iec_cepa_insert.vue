<script lang="ts" setup>
import axios from 'axios';
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { router } from '@inertiajs/vue3';
import { ref, watch, toRef } from 'vue';
import ProgressSpinner from 'primevue/progressspinner';
import MultiSelect from 'primevue/multiselect';

const isLoading = ref(false);

const props = defineProps({
    protectedAreas: { type: Array, default: () => [] },
    paStaff: { type: Array, default: () => [] },
    form: { type: Object, required: true },
});

/* ---------------- TYPES ---------------- */
interface SelectedImage {
    file: File;
    preview: string;
}

/* ---------------- REACTIVE DATA ---------------- */
const pa_staff_opts = ref([]);

const selectedMonths = ref<string[]>([]);

const accomplishments = ref<string[]>(['']);

const selectedFiles = ref<(SelectedImage | null)[]>([
    null,
    null,
    null,
    null,
]);

/* ---------------- MONTH MAP ---------------- */
const monthMap: Record<string, number> = {
    jan: 1,
    feb: 2,
    mar: 3,
    apr: 4,
    may: 5,
    jun: 6,
    jul: 7,
    aug: 8,
    sep: 9,
    oct: 10,
    nov: 11,
    dec: 12,
};
const quarterOptions = [
    { label: '1st Quarter', value: 1 },
    { label: '2nd Quarter', value: 2 },
    { label: '3rd Quarter', value: 3 },
    { label: '4th Quarter', value: 4 },
]
const strategy_opts = [
    {label:'Information Exchange & Dissemination', value: 1},
    {label:'Education Integration', value: 1},
    {label:'Public Awareness & Social Marketing', value: 1},
    {label:'Stakeholder Dialogue & Inclusive Participation', value: 1},
    {label:'Monitoring and Evaluation', value: 1},
]
/* ---------------- WATCH PROTECTED AREA ---------------- */
const protected_area_id = toRef(props.form, 'protected_area_id');

watch(
    protected_area_id,
    async (pa_id) => {
        if (!pa_id) {
            pa_staff_opts.value = [];
            return;
        }

        try {
            const res = await axios.get(`/api/hired_staff/${pa_id}/pa_staff`);

            pa_staff_opts.value = res.data.map((item: any) => ({
                id: Number(item.id),
                name:
                    item.name +
                    (item.position ? ` - ${item.position}` : ''),
            }));
        } catch (error) {
            console.error('Failed to load staff:', error);
        }
    },
    { immediate: true }
);

/* ---------------- TEXTAREA ACTIONS ---------------- */
const addTextarea = () => accomplishments.value.push('');

const removeTextarea = (index: number) => {
    accomplishments.value.splice(index, 1);
};

/* ---------------- FILE UPLOAD ---------------- */
const onFileChange = (event: Event, index: number) => {
    const input = event.target as HTMLInputElement;

    if (!input.files?.length) return;

    const file = input.files[0];

    if (!['image/png', 'image/jpeg'].includes(file.type)) {
        alert('Only JPG, JPEG, and PNG files are allowed.');
        input.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('Maximum file size is 5 MB.');
        input.value = '';
        return;
    }

    selectedFiles.value[index] = {
        file,
        preview: URL.createObjectURL(file),
    };
};

/* ---------------- SUBMIT ---------------- */
const submitForm = async () => {
    isLoading.value = true;

    try {

        const formData = new FormData();

        // Protected Area
        formData.append(
            'protected_area_id',
            props.form.protected_area_id
        );
        formData.append(
            'quarter',
            String(props.form.quarter)
        );


        // Months
        selectedMonths.value.forEach((month) => {
            formData.append('months[]', String(monthMap[month]));
        });

        // Accomplishments
        accomplishments.value.forEach((activity) => {
            formData.append('accomplishments[]', activity);
        });

        // Images
        selectedFiles.value
            .filter((item) => item !== null)
            .forEach((item) => {
                formData.append('iec_images[]', item!.file);
            });

        const response = await axios.post(
            route('iec_cepa.store'),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        console.log(response.data);

        // Reset form
        props.form.reset();

        selectedMonths.value = [];
        accomplishments.value = [''];

        selectedFiles.value = [
            null,
            null,
            null,
            null,
        ];

        router.reload();

    } catch (error: any) {

        console.error(error);

        if (error.response) {
            console.log(error.response.data);
        }

    } finally {

        // Keep the loading state for 10 seconds
        await new Promise(resolve => setTimeout(resolve, 10000));

        isLoading.value = false;
    }
};
</script>

<template>
    <ConfirmDialog group="headless">
        <template #container="{ message, rejectCallback }">
            <div class="relative w-[950px] max-w-[95vw] rounded-xl bg-white shadow-lg">
                <div v-if="isLoading" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50">
                    <div class="flex flex-col items-center rounded-xl bg-white p-8 shadow-2xl">
                        <ProgressSpinner style="width:70px;height:70px" strokeWidth="5" />

                        <p class="mt-4 text-lg font-semibold text-gray-800">
                            Uploading images...
                        </p>

                        <small class="text-gray-500">
                            Please wait. Do not close this window.
                        </small>
                    </div>
                </div>
                <!-- Header -->
                <div class="relative rounded-t-xl bg-[#004d40] px-6 py-8 text-center text-white"
                    style="background-color: #004d40;">

                    <div
                        class="absolute left-1/2 top-full flex h-20 w-20 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-lg">
                        <img src="../../../../images/denr_logo.png" class="h-16 w-16" />
                    </div>

                    <h2 class="text-xl font-bold">
                        {{ message.header }}
                    </h2>

                    <p class="mt-1 text-sm text-green-100">
                        {{ message.message }}
                    </p>
                </div>

                <div class="grid grid-cols-12 gap-6 p-6 pt-14">

                    <!-- LEFT COLUMN -->
                    <div class="col-span-8 space-y-5">

                        <!-- Protected Area -->
                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Protected Area
                            </label>

                            <Select class="w-full" filter :options="props.protectedAreas" optionLabel="pa_code"
                                optionValue="id" v-model="props.form.protected_area_id"
                                placeholder="Select Protected Area" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Quarter
                            </label>

                            <Select class="w-full" :options="quarterOptions" optionLabel="label" optionValue="value"
                                v-model="props.form.quarter" placeholder="Select Quarter" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold">
                                Strategy
                            </label>
                            <MultiSelect class="w-full" filter :options="strategy_opts" optionLabel="name"
                                optionValue="id" display="chip" v-model="props.form.strategy"
                                placeholder="Select Staff" />
                            
                        </div>

                        <!-- Activities -->
                        <div>
                            <div class="flex items-center justify-between">

                                <label class="text-sm font-semibold">
                                    Activities under Regular Monitoring
                                </label>

                                <Button label="Add Activity" icon="pi pi-plus" size="small" text @click="addTextarea" />
                            </div>

                            <div class="mt-3 max-h-[350px] space-y-3 overflow-y-auto pr-2">

                                <div v-for="(item, index) in accomplishments" :key="index" class="flex gap-2">
                                    <Textarea v-model="accomplishments[index]" autoResize rows="3" class="w-full"
                                        placeholder="Enter activity..." />

                                    <Button v-if="accomplishments.length > 1" icon="pi pi-trash" severity="danger"
                                        outlined @click="removeTextarea(index)" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-span-4 max-h-[600px] space-y-3 overflow-y-auto">

                        <div class="rounded-lg border bg-gray-50 p-4">

                            <h3 class="font-semibold text-gray-700">
                                IEC Supporting Images
                            </h3>

                            <p class="mb-4 text-xs text-gray-500">
                                Upload up to 4 JPG, JPEG, or PNG images.
                                Maximum size: 5 MB each.
                            </p>

                            <div class="space-y-4">

                                <div v-for="n in 10" :key="n" class="rounded-lg border border-dashed p-3">
                                    <label class="mb-2 block text-xs font-medium">
                                        Image {{ n }}
                                    </label>

                                    <input type="file" name="iec_images[]" accept=".png,.jpg,.jpeg" class="block w-full text-xs
                                            file:mr-2
                                            file:rounded-md
                                            file:border-0
                                            file:bg-[#004d40]
                                            file:px-3
                                            file:py-2
                                            file:text-white" @change="onFileChange($event, n - 1)" />

                                    <div v-if="selectedFiles[n - 1]" class="mt-2 text-xs">
                                        <img :src="selectedFiles[n - 1]!.preview"
                                            class="h-24 w-full rounded object-cover" />

                                        <div class="mt-1 truncate">
                                            {{ selectedFiles[n - 1]!.file.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4">

                    <Button label="Cancel" outlined @click="rejectCallback" :disabled="isLoading" />

                    <Button label="Submit" icon="pi pi-check" @click="submitForm" :loading="isLoading"
                        :disabled="isLoading" style="background:#004d40" />
                </div>

            </div>

        </template>
    </ConfirmDialog>
</template>
