<!-- ./components/MonitoringTable.vue -->
<script setup lang="ts">
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

defineProps({
    rows: Array,
    totals: Object
})
</script>

<template>
    <DataTable
        :value="rows"
        stripedRows
        showGridlines
        responsiveLayout="scroll"
        class="p-datatable-sm rounded-xl shadow-md"
    >
        <Column field="pa" header="PA" frozen />
        <Column field="focal" header="Focal / Alternate" />

        <Column field="jan" header="Jan" />
        <Column field="feb" header="Feb" />
        <Column field="mar" header="Mar" />
        <Column field="apr" header="Apr" />
        <Column field="may" header="May" />
        <Column field="jun" header="Jun" />
        <Column field="jul" header="Jul" />
        <Column field="aug" header="Aug" />
        <Column field="sep" header="Sep" />
        <Column field="oct" header="Oct" />
        <Column field="nov" header="Nov" />
        <Column field="dec" header="Dec" />

        <Column field="approved_resolutions" header="Approved Resolutions" class="text-blue-600 bg-gray-100 font-bold" />
        <Column field="approved_clearances" header="Approved Clearances"  class="text-green-600 bg-gray-100 font-bold" />
        <Column field="received" header="Received" class="text-orange-600 bg-gray-100 font-bold" />
        <Column field="pamb_minutes" header="PAMB Minutes" class="text-purple-600 bg-gray-100 font-bold" />

        <template #footer>
            <tr class="font-bold bg-gray-100">
                <td>Totals</td>
                <td>{{ totals.jan }}</td>
                <td>{{ totals.feb }}</td>
                <td>{{ totals.mar }}</td>
                <td>{{ totals.apr }}</td>
                <td>{{ totals.may }}</td>
                <td>{{ totals.jun }}</td>
                <td>{{ totals.jul }}</td>
                <td>{{ totals.aug }}</td>
                <td>{{ totals.sep }}</td>
                <td>{{ totals.oct }}</td>
                <td>{{ totals.nov }}</td>
                <td>{{ totals.dec }}</td>
            
            </tr>
        </template>

    </DataTable>
</template>