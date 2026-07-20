<script setup lang="ts">
import Checkbox from 'primevue/checkbox';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import DataTable from 'primevue/datatable';
import Row from 'primevue/row';

defineProps({
    rows: {
        type: Array,
        default: () => [],
    },

    totals: {
        type: Object,
        default: () => ({}),
    },

    loading: Boolean,
});
</script>

<template>
    <DataTable :value="rows" :loading="loading" stripedRows showGridlines responsiveLayout="scroll" class="p-datatable-sm rounded-xl shadow-md">
        <!-- HEADER -->
        <ColumnGroup type="header">
            <Row>
                <Column header="Protected Area" :rowspan="2" frozen style="background-color: #004D40; color:#fff;" />

                <Column header="1st Quarter" :colspan=" 3" style="background-color: #1A237E; color:#fff;" />
                <Column header="2nd Quarter" :colspan="3" style="background-color: #004D40; color:#fff;" />
                <Column header="3rd Quarter" :colspan="3" style="background-color: #1A237E; color:#fff;" />
                <Column header="4th Quarter" :colspan="3" style="background-color: #004D40; color:#fff;" />
            </Row>

            <Row>
                <Column header="Jan" />
                <Column header="Feb" />
                <Column header="Mar" />

                <Column header="Apr" />
                <Column header="May" />
                <Column header="Jun" />

                <Column header="Jul" />
                <Column header="Aug" />
                <Column header="Sep" />

                <Column header="Oct" />
                <Column header="Nov" />
                <Column header="Dec" />
            </Row>
        </ColumnGroup>

        <!-- PA -->
        <Column field="pa_code" frozen/>

        <!-- STAFF -->

        <!-- MONTHS -->
        <!-- JAN -->
        <Column field="jan">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.jan) >= 1" binary readonly />
            </template>
        </Column>

        <!-- FEB -->
        <Column field="feb">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.feb) >= 1" binary readonly />
            </template>
        </Column>

        <!-- MAR -->
        <Column field="mar">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.mar) >= 1" binary readonly />
            </template>
        </Column>

        <!-- APR -->
        <Column field="apr">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.apr) >= 1" binary readonly />
            </template>
        </Column>

        <!-- MAY -->
        <Column field="may">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.may) >= 1" binary readonly />
            </template>
        </Column>

        <!-- JUN -->
        <Column field="jun">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.jun) >= 1" binary readonly />
            </template>
        </Column>

        <!-- JUL -->
        <Column field="jul">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.jul) >= 1" binary readonly />
            </template>
        </Column>

        <!-- AUG -->
        <Column field="aug">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.aug) >= 1" binary readonly />
            </template>
        </Column>

        <!-- SEP -->
        <Column field="sep">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.sep) >= 1" binary readonly />
            </template>
        </Column>

        <!-- OCT -->
        <Column field="oct">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.oct) >= 1" binary readonly />
            </template>
        </Column>

        <!-- NOV -->
        <Column field="nov">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.nov) >= 1" binary readonly />
            </template>
        </Column>

        <!-- DEC -->
        <Column field="dec">
            <template #body="{ data }">
                <Checkbox :modelValue="Number(data.dec) >= 1" binary readonly />
            </template>
        </Column>

    </DataTable>
</template>
