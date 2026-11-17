<script lang="ts" setup>
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import DatePicker from 'primevue/datepicker';
import { FilterMatchMode } from '@primevue/core/api';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';

interface DocRow {
    docid: string;
    date_received: string;
    originating_office: string;
    sender: string;
    subject: string;
    action_required: string;
}

const visible = defineModel<boolean>('visible', { default: false });
const emit = defineEmits<{ (e: 'synced', count: number): void }>();

const toast = useToast();
const rows = ref<DocRow[]>([]);
const isFetching = ref(false);
const isLoading = ref(false); // generating
const errorMsg = ref('');
const filters = ref({
    global: {
        value: null as string | null,
        matchMode: FilterMatchMode.CONTAINS
    },
    date_from: {
        value: null as Date | null,
        matchMode: FilterMatchMode.DATE_IS
    },
    date_to: {
        value: null as Date | null,
        matchMode: FilterMatchMode.DATE_IS
    }
});

async function loadPreview() {
    isFetching.value = true;
    errorMsg.value = '';
    try {
        const { data } = await axios.get('/api/googlesheet/preview');
        rows.value = data.data;
    } catch (e: any) {
        errorMsg.value = e.response?.data?.message ?? 'Failed to load documents.';
    } finally {
        isFetching.value = false;
    }
}

async function generate() {
    isLoading.value = true;

    try {
        const selectedDateFrom = filters.value.date_from.value;
        const selectedDateTo = filters.value.date_to.value;

        const { data } = await axios.get('/api/googlesheet/syncdatabase', {
            params: {
                date_from: selectedDateFrom
                    ? selectedDateFrom.toISOString().split('T')[0]
                    : null,
                date_to: selectedDateTo
                    ? selectedDateTo.toISOString().split('T')[0]
                    : null
            }
        });

        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: data.message,
            life: 4000
        });

        emit('synced', data.count);
        visible.value = false;
    } catch (e: any) {
        toast.add({
            severity: 'error',
            summary: 'Failed',
            detail: e.response?.data?.message ?? 'Generation failed.',
            life: 6000
        });
    } finally {
        isLoading.value = false;
    }
}

// load fresh data every time the dialog opens
watch(visible, (v) => {
    if (v) {
        filters.value.global.value = null;
        loadPreview();
    }
});
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="!isLoading" :close-on-escape="!isLoading" :draggable="false"
        :show-header="false" :style="{ width: '1000px', maxWidth: '95vw' }"
        :pt="{ content: { class: 'p-0 rounded-xl overflow-hidden' }, root: { class: 'rounded-xl overflow-visible' } }">
        <!-- Header -->
        <div class="relative px-6 pb-14 pt-8 text-center text-white"
            style="background: linear-gradient(135deg, #004d40, #00796b);">
            <h2 class="text-xl font-bold">Generate Google Sheet</h2>
            <p class="mt-1 text-sm text-green-100">
                Review the documents below before syncing them to the sheet.
            </p>

            <div
                class="absolute left-1/2 top-full flex h-20 w-20 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white shadow-lg">
                <img src="../../../../images/denr_logo.png" class="h-16 w-16" />
            </div>
        </div>

        <!-- Body -->
        <div class="p-6 pt-14">
            <Message v-if="errorMsg" severity="error" :closable="false" class="mb-4">
                {{ errorMsg }}
                <Button label="Retry" text size="small" @click="loadPreview" />
            </Message>

            <DataTable :value="rows" :loading="isFetching" v-model:filters="filters"
                :global-filter-fields="['docid', 'originating_office', 'sender', 'subject', 'action_required']"
                paginator :rows="8" size="small" striped-rows scrollable scroll-height="360px" data-key="docid">
                <template #header>
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <span class="text-sm font-semibold text-gray-600">
                            {{ rows.length }} document(s) found
                        </span>

                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText v-model="filters.global.value" placeholder="Search..." size="small" />
                        </IconField>

                        <div class="flex flex-wrap">
                            <DatePicker v-model="filters.date_from.value" :show-icon="true"
                                placeholder="Date From" size="small" date-format="MM dd, yy"
                                show-button-bar />
                                
                                   <DatePicker v-model="filters.date_to.value" :show-icon="true"
                                placeholder="Date To" size="small" date-format="MM dd, yy"
                                show-button-bar />
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="py-8 text-center text-gray-500">No documents found.</div>
                </template>

                <Column field="docid" header="Doc ID" style="min-width: 8rem" />
                <Column field="date_received" header="Date Received" style="min-width: 8rem" />
                <Column field="originating_office" header="Office" style="min-width: 10rem" />
                <Column field="sender" header="Sender" style="min-width: 10rem" />
                <Column field="subject" header="Subject" style="min-width: 18rem">
                    <template #body="{ data }">
                        <span class="line-clamp-2" :title="data.subject">{{ data.subject }}</span>
                    </template>
                </Column>
                <Column field="action_required" header="Action Required" style="min-width: 10rem" />
            </DataTable>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between border-t bg-gray-50 px-6 py-4">
            <span class="text-xs text-gray-500">
                <i class="pi pi-info-circle mr-1" />
                This will write {{ rows.length }} row(s) to the Google Sheet.
            </span>
            <div class="flex gap-3">
                <Button label="Cancel" outlined severity="secondary" :disabled="isLoading" @click="visible = false" />
                <Button label="Generate" icon="pi pi-check" :loading="isLoading"
                    :disabled="isLoading || isFetching || !rows.length"
                    style="background: #004d40; border-color: #004d40" @click="generate" />
            </div>
        </div>
    </Dialog>
</template>