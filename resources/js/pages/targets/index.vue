<script setup lang="ts">
/**
 * TARGETS INDEX PAGE
 * --------------------------------
 * - Focal Person info card (avatar + details) for the Protected Area
 * - Performance Indicators (Targets + Measures) report table,
 *   grouped by Program/Project/Activity category, matching the
 *   DENR PAMB M&E report format.
 * - "Add Target / Measure" form tab for creating new entries.
 */

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useToast } from 'primevue/usetoast';

const page = usePage()
const toast = useToast();
const props = defineProps<Props>();
/* ------------------------------------------ */
/* Breadcrumbs                                 */
/* ------------------------------------------ */
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Focal Person & Alternate Focal Person Targets and Activities',
        href: '/targets/index',
    },
];

/* ------------------------------------------ */
/* Tabs                                        */
/* ------------------------------------------ */
type TabKey = 'report' | 'cos_report' | 'add';

const activeTab = ref<TabKey>('report');

const tabs: { key: TabKey; label: string }[] = [
    { key: 'report', label: 'Performance Indicators Report' },
    { key: 'cos_report', label: 'Performance Evaluation (COS)' },
    { key: 'add', label: 'Add Target / Measure' },
];

/* ------------------------------------------ */
/* Focal Person Info (replace with real prop  */
/* / composable data from the backend)         */
/* ------------------------------------------ */
interface FocalPerson {
    name: string;
    position: string;
    protectedArea: string;
    email: string;
    contact: string;
    avatar?: string | null;
}

const focalPerson = computed<FocalPerson>(() => ({
    name: 'Juan Dela Cruz',
    position: 'Focal Person',
    protectedArea: 'Southern Foreshore Protected Landscape (SFPL)',
    email: 'juan.delacruz@denr.gov.ph',
    contact: '0917-000-0000',
    avatar: null,
}));

function initials(name: string) {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((n) => n[0]?.toUpperCase())
        .join('');
}

/* ------------------------------------------ */
/* Report table data                           */
/* ------------------------------------------ */
// interface Measure {
//     text: string;
//     firstSem?: string | number | null;
//     secondSem?: string | number | null;
//     total?: string | number | null;
// }

interface Props {
    data: any;
    categories: Category[];
}

interface Category {
    label: string;
    rows: ProgramRow[];
}

interface ProtectedArea {
    id: number;
    label: string;
}


interface Measure {
    text: string;
    firstSem?: string | number | null;
    secondSem?: string | number | null;
    total?: string | number | null;
    remarks?: string | null;
}

interface ProgramRow {
    program: string;
    measures: Measure[];
}


/**
 * Flattened row shape for rendering: one physical <tr> per measure.
 * `isFirstOfProgram` tells us whether to render the Program cell
 * (with rowspan = number of measures) or skip it because the
 * previous row already rendered it with a rowspan.
 */
interface FlatRow {
    category: string;
    program: string;
    programSpan: number;
    isFirstOfProgram: boolean;
    isFirstOfCategory: boolean;
    categorySpan: number;
    measure: Measure;
}

const categories = computed<Category[]>(() => {
    return props.categories ?? [];
});

const pa: ProtectedArea[] = [
    { id: 1, label: 'Southern Foreshore Protected Landscape (SFPL)' },
    { id: 2, label: 'Alquerez Watershed Reserve (ALQUEREZ)' },
    { id: 3, label: 'Upper River Watershed Reserve (URWR)' },
];



/* Flatten categories -> rows for straightforward, bug-free rowspan rendering */
const flatRows = computed<FlatRow[]>(() => {
    const rows: FlatRow[] = [];

    for (const category of categories.value) {

        const categoryRowCount = category.rows.reduce(
            (sum, r) => sum + r.measures.length,
            0
        );

        let categoryRowIndex = 0;

        for (const programRow of category.rows) {

            programRow.measures.forEach((measure, measureIndex) => {

                rows.push({
                    category: category.label,
                    program: programRow.program,
                    programSpan: programRow.measures.length,
                    isFirstOfProgram: measureIndex === 0,
                    isFirstOfCategory: categoryRowIndex === 0,
                    categorySpan: categoryRowCount,
                    measure,
                });

                categoryRowIndex++;
            });
        }
    }

    return rows;
});

/* ------------------------------------------ */
/* Add Target / Measure form                   */
/* ------------------------------------------ */

// Category options reuse the labels already used in the report above,
// plus an "Other" escape hatch that reveals a free-text input.
const categoryOptions = [
    'OPERATIONS',
    'SUPPORT TO OPERATIONS',
    'GENERAL AND ADMINISTRATIVE SUPPORT (GAS)',
];
const form = useForm({
    category: categoryOptions[0] ?? '',
    protected_area: pa[0]?.id ?? '',
    category_other: '',
    program: '',
    measure_text: '',
    first_sem: '' as string | number,
    second_sem: '' as string | number,
    total: '' as string | number,
    remarks: '',
});

const showOtherCategory = computed(() => form.category === '__other__');

// Auto-suggest a total when both semester fields are plain numbers.
// The field stays editable in case the real target isn't a simple sum
// (e.g. "100%" style measures).
function suggestTotal() {
    const first = form.first_sem;
    const second = form.second_sem;

    // Both are empty
    if (first === '' && second === '') {
        form.total = '';
        return;
    }

    // Only 1st semester has a value
    if (first !== '' && second === '') {
        const a = Number(first);

        if (!Number.isNaN(a)) {
            form.total = a;
        }

        return;
    }

    // Only 2nd semester has a value
    if (first === '' && second !== '') {
        const b = Number(second);

        if (!Number.isNaN(b)) {
            form.total = b;
        }

        return;
    }

    // Both semesters have values
    const a = Number(first);
    const b = Number(second);

    if (!Number.isNaN(a) && !Number.isNaN(b)) {
        form.total = a + b;
    }
}

function submitTarget() {
    form.post(route('targets.store'), {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            const message = page.props.flash?.success
            if (message) {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: message,
                    life: 3000,
                })
            }

            activeTab.value = 'report';
        },

        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
}

function resetForm() {
    form.reset();
}
</script>

<template>

    <Head title="DENR Online Protected Area Information System" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div class="box">
                <h2 class="title">Focal Person & Alternate Focal Person Targets and Activities</h2>
                <p class="mb-4 text-gray-700">
                    This section provides an overview of the hired staff monitoring system, including key metrics and
                    insights
                    related to staff performance and activities.
                </p>

                <!-- Focal Person Info Card -->
                <div class="focal-card">
                    <div class="focal-avatar">
                        <img v-if="focalPerson.avatar" :src="focalPerson.avatar" :alt="focalPerson.name" />
                        <span v-else>{{ initials(focalPerson.name) }}</span>
                    </div>
                    <div class="focal-details">
                        <div class="focal-name">{{ focalPerson.name }}</div>
                        <div class="focal-role">{{ focalPerson.position }}</div>
                        <div class="focal-meta">
                            <span class="focal-meta-item">
                                <strong>Protected Area:</strong> {{ focalPerson.protectedArea }}
                            </span>
                            <span class="focal-meta-item">
                                <strong>Email:</strong> {{ focalPerson.email }}
                            </span>
                            <span class="focal-meta-item">
                                <strong>Contact:</strong> {{ focalPerson.contact }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab bar -->
            <div class="tab-bar">
                <button v-for="tab in tabs" :key="tab.key" type="button" class="tab-btn"
                    :class="{ 'tab-btn-active': activeTab === tab.key }" @click="activeTab = tab.key">
                    {{ tab.label }}
                </button>
            </div>

            <!-- ============================== -->
            <!-- TAB: Performance Indicators     -->
            <!-- ============================== -->
            <div v-if="activeTab === 'report'" class="box overflow-x-auto">
                <h2 class="title">Performance Indicators (Targets + Measures)</h2>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="col-program">Program/Project/Activity (P/AP/)</th>
                            <th colspan="4">Performance Indicators (Targets + Measures)</th>
                            <th colspan="3">Rating</th>
                            <th rowspan="2" class="col-remarks">Remarks</th>
                        </tr>
                        <tr>
                            <th class="col-measures">Measures</th>
                            <th class="col-sem">1st Sem</th>
                            <th class="col-sem">2nd Sem</th>
                            <th class="col-sem">Total Targets</th>
                            <th class="col-rating">Q1</th>
                            <th class="col-rating">Q2</th>
                            <th class="col-rating">T3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(row, idx) in flatRows" :key="row.category + row.program + idx">
                            <tr v-if="row.isFirstOfCategory" class="category-row">
                                <td colspan="9">{{ row.category }}</td>
                            </tr>
                            <tr>
                                <td v-if="row.isFirstOfProgram" :rowspan="row.programSpan" class="col-program">
                                    {{ row.program }}
                                </td>

                                <td class="measure-text">
                                    {{ row.measure.text }}
                                </td>

                                <td class="text-center">
                                    {{ row.measure.firstSem ?? '' }}
                                </td>

                                <td class="text-center">
                                    {{ row.measure.secondSem ?? '' }}
                                </td>

                                <td class="text-center font-semibold">
                                    {{ row.measure.total ?? '' }}
                                </td>

                                <!-- Rating -->
                                <td v-if="row.isFirstOfProgram" :rowspan="row.programSpan"></td>
                                <td v-if="row.isFirstOfProgram" :rowspan="row.programSpan"></td>
                                <td v-if="row.isFirstOfProgram" :rowspan="row.programSpan"></td>

                                <!-- Remarks -->
                                <td>
                                    {{ row.measure.remarks ?? '' }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- ============================== -->
            <!-- TAB: Performance Evaluation (COS) -->
            <!-- ============================== -->
            <div v-else-if="activeTab === 'cos_report'" class="box overflow-x-auto">

                <h2 class="title">Performance Evaluation (Targets + Measures)</h2>

                <table class="report-table">

                    <thead>
                        <tr>
                            <th rowspan="2" class="col-program">
                                Specific Output under the Terms of Reference in the Contract
                            </th>

                            <th rowspan="2" class="col-performance">
                                Performance Indicator
                            </th>

                            <th rowspan="2" class="col-accomplishment">
                                Actual Accomplishments
                            </th>

                            <th colspan="4" class="col-rating-group">
                                Rating
                            </th>

                            <th rowspan="2" class="col-remarks">
                                Remarks
                            </th>
                        </tr>

                        <tr>
                            <th class="col-rating">Q1</th>
                            <th class="col-rating">Q2</th>
                            <th class="col-rating">T3</th>
                            <th class="col-rating">A1</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- Your rows here -->
                    </tbody>

                </table>
            </div>
            <!-- ============================== -->
            <!-- TAB: Add Target / Measure       -->
            <!-- ============================== -->
            <div v-else class="box">
                <h2 class="title">Add Target / Measure</h2>

                <form class="target-form" @submit.prevent="submitTarget">
                    <div class="form-grid">
                        <!-- Category -->
                        <div class="form-field">
                            <label for="category">Category</label>
                            <select id="category" v-model="form.category">
                                <option v-for="opt in categoryOptions" :key="opt" :value="opt">{{ opt }}</option>
                                <option value="__other__">Other (specify)&hellip;</option>
                            </select>
                            <p v-if="form.errors.category" class="field-error">{{ form.errors.category }}</p>
                        </div>
                        <div class="form-field">
                            <label for="protected_area">Protected Area</label>
                            <select id="protected_area" v-model="form.protected_area">
                                <option v-for="opt in pa" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                                <option value="__other__">Other (specify)&hellip;</option>
                            </select>
                            <p v-if="form.errors.protected_area" class="field-error">{{ form.errors.protected_area }}
                            </p>
                        </div>

                        <div v-if="showOtherCategory" class="form-field">
                            <label for="category_other">Specify Category</label>
                            <input id="category_other" v-model="form.category_other" type="text"
                                placeholder="e.g. CAPACITY BUILDING" />
                        </div>

                        <!-- Program / Project / Activity -->
                        <div class="form-field form-field-wide">
                            <label for="program">Program / Project / Activity (P/A/P)</label>
                            <input id="program" v-model="form.program" type="text"
                                placeholder="e.g. PAMB Resolutions" />
                            <p v-if="form.errors.program" class="field-error">{{ form.errors.program }}</p>
                        </div>

                        <!-- Measure text -->
                        <div class="form-field form-field-wide">
                            <label for="measure_text">Measure</label>
                            <textarea id="measure_text" v-model="form.measure_text" rows="3"
                                placeholder="Describe the performance indicator / target and its basis for measurement..."></textarea>
                            <p v-if="form.errors.measure_text" class="field-error">{{ form.errors.measure_text }}</p>
                        </div>

                        <!-- Targets row -->
                        <div class="form-field">
                            <label for="first_sem">1st Sem Target</label>
                            <input id="first_sem" v-model="form.first_sem" type="text" placeholder="e.g. 2 or 100%"
                                @input="suggestTotal" />
                        </div>

                        <div class="form-field">
                            <label for="second_sem">2nd Sem Target</label>
                            <input id="second_sem" v-model="form.second_sem" type="text" placeholder="e.g. 2 or 100%"
                                @input="suggestTotal" />
                        </div>

                        <div class="form-field">
                            <label for="total">Total Targets</label>
                            <input id="total" v-model="form.total" type="text" placeholder="Auto-suggested, editable" />
                        </div>

                        <!-- Remarks -->
                        <div class="form-field form-field-wide">
                            <label for="remarks">Remarks</label>
                            <textarea id="remarks" v-model="form.remarks" rows="2"
                                placeholder="Optional notes..."></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" @click="resetForm">Clear</button>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Target / Measure' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.box {
    background-color: #fff;
    border-top: 4px solid #00943a;
    margin-bottom: 20px;
    padding: 20px;
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

/* ---------------- Focal Person Card ---------------- */
.focal-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    background: #f9fdfa;
}

.focal-avatar {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #00943a;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    overflow: hidden;
}

.focal-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.focal-name {
    font-weight: 700;
    font-size: 1rem;
    color: #1f2937;
}

.focal-role {
    font-size: 0.85rem;
    color: #00943a;
    font-weight: 600;
    margin-bottom: 6px;
}

.focal-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 24px;
    font-size: 0.8rem;
    color: #4b5563;
}

/* ---------------- Tabs ---------------- */
.tab-bar {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #e0e0e0;
}

.tab-btn {
    padding: 10px 18px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    transition: color 0.15s ease, border-color 0.15s ease;
}

.tab-btn:hover {
    color: #00943a;
}

.tab-btn-active {
    color: #00943a;
    border-bottom-color: #00943a;
}

/* ---------------- Report Table ---------------- */
.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}

.report-table th,
.report-table td {
    border: 1px solid #cfd8dc;
    padding: 6px 8px;
    vertical-align: top;
}

.report-table thead th {
    background: #d7ecdc;
    color: #1f2937;
    text-align: center;
    font-weight: 700;
    vertical-align: middle;
}

.category-row td {
    background: #6b7280;
    color: #fff;
    font-weight: 700;
    text-align: left;
    letter-spacing: 0.03em;
}

.col-program {
    min-width: 180px;
    font-weight: 600;
    vertical-align: top;
}

.col-measures,
.measure-text {
    min-width: 320px;
    font-style: italic;
    font-size: 0.78rem;
}

.col-sem,
.col-rating {
    width: 70px;
    text-align: center;
}

.col-remarks {
    min-width: 140px;
}

.text-center {
    text-align: center;
}

/* ---------------- Add Target / Measure Form ---------------- */
.target-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px 20px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-field-wide {
    grid-column: 1 / -1;
}

.form-field label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #432c0b;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.form-field input,
.form-field select,
.form-field textarea {
    border: 1px solid #cfd8dc;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 0.85rem;
    color: #1f2937;
    background: #fff;
    font-family: inherit;
    resize: vertical;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #00943a;
    box-shadow: 0 0 0 2px rgba(0, 148, 58, 0.15);
}

.field-error {
    font-size: 0.75rem;
    color: #dc2626;
    margin: 0;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #e0e0e0;
    padding-top: 16px;
}

.btn-primary,
.btn-secondary {
    padding: 9px 20px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease;
}

.btn-primary {
    background: #00943a;
    color: #fff;
}

.btn-primary:hover {
    opacity: 0.9;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #fff;
    color: #4b5563;
    border-color: #cfd8dc;
}

.btn-secondary:hover {
    background: #f3f4f6;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>