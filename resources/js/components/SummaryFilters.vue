<!-- ./components/SummaryFilters.vue -->
<script setup lang="ts">
import { reactive } from 'vue'
import FloatLabel from 'primevue/floatlabel'
import Select from 'primevue/select'

const filters = reactive({
    year: 2026,
    month: null,
    quarter: null,
    semestral: null
})

const years = [
    { label: '2026', value: 2026 },
    { label: '2025', value: 2025 },
    { label: '2024', value: 2024 }
]

const months = [
    { label: 'January', value: 1 },
    { label: 'February', value: 2 },
    { label: 'March', value: 3 },
    { label: 'April', value: 4 },
    { label: 'May', value: 5 },
    { label: 'June', value: 6 },
    { label: 'July', value: 7 },
    { label: 'August', value: 8 },
    { label: 'September', value: 9 },
    { label: 'October', value: 10 },
    { label: 'November', value: 11 },
    { label: 'December', value: 12 }
]

const quarters = [
    { label: '1st Quarter', value: 1 },
    { label: '2nd Quarter', value: 2 },
    { label: '3rd Quarter', value: 3 },
    { label: '4th Quarter', value: 4 }
]

const semestrals = [
    { label: '1st Semestral', value: 1 },
    { label: '2nd Semestral', value: 2 }
]
</script>

<template>
    <div class="grid gap-4 md:grid-cols-4 mt-2">

        <FloatLabel>
            <Select v-model="filters.year" :options="years" optionLabel="label" optionValue="value" class="w-full" />
            <label>Year</label>
        </FloatLabel>

        <FloatLabel>
            <Select v-model="filters.month" :options="months" optionLabel="label" optionValue="value" class="w-full" />
            <label>Month</label>
        </FloatLabel>

        <FloatLabel>
            <Select v-model="filters.quarter" :options="quarters" optionLabel="label" optionValue="value" class="w-full" />
            <label>Quarter</label>
        </FloatLabel>

        <FloatLabel>
            <Select v-model="filters.semestral" :options="semestrals" optionLabel="label" optionValue="value" class="w-full" />
            <label>Semestral</label>
        </FloatLabel>

    </div>
</template>