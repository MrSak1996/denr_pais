<!-- ./components/SummaryCards.vue -->
<script setup lang="ts">
import Card from 'primevue/card'

defineProps({
    summary: Object
})
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">

        <Card class="text-center shadow-md rounded-xl mb-2" style="background-color: #004D40;">
            <template #title><label class="text-white">Total Approved Resolutions</label></template>
            <template #content>
                <h2 class="text-3xl font-bold text-white">
                    {{ summary.approvedResolutions }}
                </h2>
            </template>
        </Card>

        <Card class="text-center shadow-md rounded-xl mb-2" style="background-color: #004D40;">
            <template #title><label class="text-white">Total Approved Clearances</label></template>
            <template #content>
                <h2 class="text-3xl font-bold text-white">
                    {{ summary.approvedClearances }}
                </h2>
            </template>
        </Card>

        <Card class="text-center shadow-md rounded-xl mb-2" style="background-color: #004D40;">
            <template #title><label class="text-white">Total Resolutions Received</label></template>
            <template #content>
                <h2 class="text-3xl font-bold text-white">
                    {{ summary.received }}
                </h2>
            </template>
        </Card>

    </div>
</template>