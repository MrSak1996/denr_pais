<script setup lang="ts">
// Imports
import { onMounted, reactive, ref, computed } from 'vue';
import axios from 'axios';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import Fieldset from 'primevue/fieldset';
import { ShieldAlert, LoaderCircle, Info, CircleCheckBig, Undo2, Send, Undo } from 'lucide-vue-next';
import AssessmentTable from './assessment_tbl.vue';
import AssessmentModal from '../modal/assessment_modal.vue';
import ReusableConfirmDialog from '../modal/endorsed_modal.vue';

// Custom Components
import { useApi } from '@/composables/useApi';
import { useAppForm } from '@/composables/useAppForm';
import { useFormHandler } from '@/composables/useFormHandler';
import { updateChainsawForm } from '@/lib/chainsaw';
import { ChainsawData } from '@/types/chainsaw';
import LoadingSpinner from '../../LoadingSpinner.vue';
import Chainsaw_applicationField from '../forms/chainsaw_applicationField.vue';
import Chainsaw_companyField from '../forms/chainsaw_companyField.vue';
import Chainsaw_operationField from '../forms/chainsaw_operationField.vue';
import FileCard from '../forms/file_card.vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
    application: Object,
    mode: String,
});

// Form Data
const isCollapsed = ref(true)
const isRoutingCollapsed = ref(true)
const { company_form, chainsaw_form, payment_form } = useAppForm();
const { insertFormData } = useFormHandler();
const { getProvinceCode, prov_name } = useApi();
const page = usePage();

Object.assign(company_form, page.props.application || {});
Object.assign(chainsaw_form, page.props.application || {});
Object.assign(payment_form, page.props.application || {});

// Refs & Reactives
const chainsaws = reactive<ChainsawForm[]>([{ ...JSON.parse(JSON.stringify(chainsaw_form)) }]);
const toast = useToast();
const userId = page.props.auth?.user?.id;
const roleId = page.props.auth?.user?.role_id;
const officeId = page.props.auth?.user?.office_id;
const files = ref([]);
const routingHistory = ref([]);
const assessmentRows = ref([])

const isLoading = ref(false);
const isloadingSpinner = ref(false);
const applicationData = ref([]);
const currentStep = ref(4);
const errorMessage = ref('');
const selectedFile = ref(null);
const showModal = ref(false);
const showFileModal = ref(false);
const selectedFileToUpdate = ref(null)
const updateFileInput = ref(null)
const confirmDialogRef = ref<any>(null);

const brands = ref([
    {
        name: '',
        models: [{ model: '', quantity: 1 }]
    }
])

const suppliers = ref([
    {
        supplier_name: '',
        supplier_address: '',
        permit_to_sell_no: '',
        serial_no: '',
        issued_date: '',
        valid_until: '',
        issued_by: '',
        brands: [
            {
                name: '',
                models: [
                    { model: '', quantity: 1, serial_no: '' }
                ]
            }
        ]
    }
])

const onsite = ref({
    findings: '',
    recommendations: ''
});

const hasFailed = computed(() => {
    return companyRequirements.value.some(r => r.assessment === 'failed')
})

const updateAssessment = (checklist_entry_id, assessment) => {
    const row = companyRequirements.value.find(
        r => r.checklist_entry_id === checklist_entry_id
    );
    if (row) {
        row.assessment = assessment;
        row.is_saved = false; // unlock save again if changed
    }
};

const handleResubmissionUpload = async (checklistId: number, files: File[]) => {
    try {
        isLoading.value = true; // ✅ SHOW LOADING OVERLAY

        const formData = new FormData();
        files.forEach(file => formData.append('files[]', file));
        formData.append('uploaded_by', userId);
        formData.append('checklist_entry_id', checklistId.toString());
        formData.append('application_no', company_form.application_no);
        formData.append('application_id', page.props.application.id);

        // Example API endpoint
        const response = await axios.post('/api/resubmit-files', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        // Assume response returns the uploaded files with timestamps
        const uploadedFiles = response.data.files; // [{file_name, uploaded_at}, ...]

        // Find the row and push new resubmissions
        const row = companyRequirements.value.find(r => r.checklist_entry_id === checklistId);
        if (row) {
            row.resubmissions.push(...uploadedFiles);
        }
    } catch (error) {
        console.error(error);
    } finally {
        isLoading.value = false; // ✅ HIDE LOADING OVERLAY
    }
};

// Remove handler
const handleRemoveResubmission = (checklistId: number, index: number) => {
    const row = companyRequirements.find(r => r.checklist_entry_id === checklistId);
    if (!row || !row.resubmissions[index]) return;

    // Optional: call API to remove file from server
    // await axios.delete(`/api/remove-resubmission/${row.resubmissions[index].id}`);

    // Remove from array
    row.resubmissions.splice(index, 1);
};
const updateRemarks = (checklist_entry_id, remarks) => {
    const row = companyRequirements.value.find(
        r => r.checklist_entry_id === checklist_entry_id
    );
    if (row) {
        row.remarks = remarks;
        row.is_saved = false;
    }
};

const updateOnsite = ({ field, value }) => {
    onsite.value[field] = value;
};

const returnApplication = async () => {

    const incompleteRows = companyRequirements.value
        .map((row, index) => ({ index: index + 1, assessment: row.assessment }))
        .filter(r => !r.assessment);

    if (incompleteRows.length) {
        alert(`Incomplete assessment on row(s): ${incompleteRows.map(r => r.index).join(', ')}`);
        return;
    }


    await axios.post('/api/returnApplication', {
        application_id: page.props.application.id,
        user_id: userId,
        role_id: roleId,
        assessments: companyRequirements.value.map(row => ({
            permit_checklist_id: row.permit_checklist_id,
            assessment: row.assessment,
            remarks: row.remarks,
        })),
        onsite: {
            findings: onsite.value.findings,
            recommendations: onsite.value.recommendations
        }
    });

    toast.add({
        severity: 'success',
        summary: 'Application Returned',
        detail: 'Application has been returned successfully.',
        life: 5000,
    });

    setTimeout(() => {
        router.get(route('rps.chief.dashboard'));
    }, 2000);
};

// Inside your component setup

const openReturnDialog = (id: number) => {
    const user_id = page.props.auth.user.id;
    const role_id = page.props.auth.user.role_id;

    confirmDialogRef.value?.open({
        header: 'Return Application?',
        message: 'Please indicate the reason and office to return this application.',
        showTextarea: false,  // user can add remarks
        showDropdown: false,  // optional: can be made dynamic later
        onConfirm: async (data?: { remarks?: string }) => {
            try {
                // Build payload for your Laravel return controller
                const payload = {
                    id: id,
                    user_id,
                    role_id,
                    assessments: companyRequirements.value.map(row => ({
                        permit_checklist_id: row.permit_checklist_id,
                        assessment: row.assessment,
                        remarks: row.remarks,
                    })),
                    onsite: {
                        findings: onsite.value.findings,
                        recommendations: onsite.value.recommendations,
                    },
                    extra_remarks: data?.remarks || null,
                };

                await axios.post(route('applications.rps.return'), payload);

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Application returned successfully.',
                    life: 3000,
                });


            } catch (error: any) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.response?.data?.message || 'Something went wrong',
                    life: 5000,
                });
            }
        },
    });
};

const fetchRoutingHistory = async () => {

    routingHistory.value = [];

    try {
        const res = await axios.get(`/api/application-routing/${page.props.application.id}`);
        routingHistory.value = res.data;
    } catch (error) {
        console.error(error);
    }

};

const submitAllAssessments = async (applicationId) => {
    if (roleId !== 1 && roleId !== 4) {
        const incomplete = companyRequirements.value.some(row => !row.assessment);

        if (incomplete) {
            alert('Please complete all assessments before submitting.');
            return;
        }
    }

    const workflowType = roleId === 4 ? 'implementing_agency' : 'smooth'

    await axios.post('/api/saveAssessment', {
        application_id: applicationId,
        userId: userId,
        application_status: 4,//STATUS_ENDORSED_TO_CHIEF_RPS
        toTSD: !hasFailed,
        role_id: roleId,
        workflow_type: workflowType,
        office_id: officeId,
        assessments: companyRequirements.value.map(row => ({
            permit_checklist_id: row.permit_checklist_id,
            assessment: row.assessment,
            remarks: row.remarks
        })),
        onsite: {
            findings: onsite.value.findings,
            recommendations: onsite.value.recommendations
        }
    });
    // setTimeout(() => {
    //     router.get(route('applications.pending_application'));

    // }, 1000);
};





// BRAND ACTIONS
const addBrand = () => {
    brands.value.push({
        name: '',
        models: [{ model: '', quantity: 1 }]
    })
}

const removeBrand = (index: number) => {
    if (brands.value.length > 1) {
        brands.value.splice(index, 1)
    }
}

// MODEL ACTIONS
const addModel = (brandIndex: number) => {
    brands.value[brandIndex].models.push({ model: '', quantity: 1 })
}

const removeModel = (brandIndex: number, modelIndex: number) => {
    const models = brands.value[brandIndex].models
    if (models.length > 1) {
        models.splice(modelIndex, 1)
    }
}






const purposeOptions = ref([
    'For cutting of trees with legal permit',
    'For Post-calamity cleaning operations',
    'For farm lot/tree orchard maintenance',
    'For cutting-trimming of trees posing danger within a private property',
    'For selling / re-selling',
    'For cutting of trees to be used for house repair or perimeter fencing',
    'Forestry/landscaping service provider',
    'Other Legal Purpose',
    'Other Supporting Documents',
]);

// Utility
const getApplicationIdFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('application_id') || urlParams.get('id');
};


// Step Navigation
const isStepValid = (stepId) => {
    // Implement validation per step if needed
    return true;
};

// const handleStepClick = (targetStep) => {
//     if (targetStep <= currentStep.value || isStepValid(currentStep.value)) {
//         currentStep.value = targetStep;
//     } else {
//         showError();
//     }
// };



// ─────────────────────────────────────────────────────────────
// STEPPER
// ─────────────────────────────────────────────────────────────
const steps = ref([
    { label: 'Applicant Form', id: 1 },
    { label: 'Permit to Sell Chainsaw', id: 2 },
    { label: 'Payment of Application Fee', id: 3 },
    { label: 'Submit and Review', id: 4 },
]);

const formValidationRules = {
    1: {
        form: 'company_form',
        fields: [
            'application_no',
            'application_type',
            'company_name',
            'company_address',
            'authorized_representative',
            'c_province',
            'c_city_mun',
            'c_barangay',

        ],
        labels: {
            application_no: 'Application No',
            application_type: 'Application Type',
            company_name: 'Company Name',
            company_address: 'Company Address',
            authorized_representative: 'Authorized Representative',
            c_province: 'Company Province',
            c_city_mun: 'Company City/Municipality',
            c_barangay: 'Company Barangay',
            p_place_of_operation_address: 'Place of Operation Address',
            p_province: 'Place of Operation Province',
            p_city_mun: 'Place of Operation City/Municipality',
            p_barangay: 'Place of Operation Barangay'
        }
    },
    2: {
        form: 'chainsaw_form',
        fields: [
            'permit_validity',
            // 'permit_chainsaw_no',
            'brand',
            'model',
            'quantity',
            'supplier_name',
            'supplier_address',
            'purpose'
        ],
        labels: {
            permit_validity: 'Permit Validity',
            // permit_chainsaw_no: 'Permit Chainsaw No',
            brand: 'Brand',
            model: 'Model',
            quantity: 'Quantity',
            supplier_name: 'Supplier Name',
            supplier_address: 'Supplier Address',
            purpose: 'Purpose'
        }
    },
    3: {
        form: 'payment_form',
        fields: [
            'official_receipt',
            'permit_fee',
            'date_of_payment'
        ],
        labels: {
            official_receipt: 'Official Receipt',
            permit_fee: 'Permit Fee',
            date_of_payment: 'Date of Payment'
        }
    }
};

/**
 * ✅ Validate the current step form dynamically and return missing fields
 */
const validateForm = () => {
    const stepRules = formValidationRules[currentStep.value];

    if (!stepRules || !stepRules.fields || stepRules.fields.length === 0) return true;

    let formToCheck = [];

    // ✅ Determine which form to validate
    if (stepRules.form === 'company_form') {
        formToCheck = [company_form]; // wrap in array for uniform processing
    } else if (stepRules.form === 'chainsaw_form') {
        formToCheck = chainsaws; // this is an array of chainsaws
    } else if (stepRules.form === 'payment_form') {
        formToCheck = [payment_form];
    }

    const missingFields = [];

    // ✅ Loop through each form entry
    formToCheck.forEach((form, index) => {
        stepRules.fields.forEach((field) => {
            if (
                form[field] === '' ||
                form[field] === null ||
                form[field] === undefined
            ) {
                // If multiple chainsaws, indicate which one is missing
                const label = stepRules.labels[field] || field;
                if (formToCheck.length > 1) {
                    missingFields.push(`${label} (Chainsaw ${index + 1})`);
                } else {
                    missingFields.push(label);
                }
            }
        });
    });

    // ✅ Show toast if any missing fields
    if (missingFields.length > 0) {
        toast.add({
            severity: 'warn',
            summary: 'Incomplete Fields',
            detail: `Please fill out the following fields: ${missingFields.join(', ')}`,
            life: 5000,
        });

        return false;
    }

    return true;
};

/**
 * ✅ Next step logic when user clicks "Next" button
 */
const nextStep = async () => {
    if (currentStep.value < steps.value.length) {
        // const isValid = validateForm();

        // Stop if validation fails
        // if (!isValid) return;

        const handlers = [null, updateCompanyApplication, updateChainsawInformation, updatePaymentInfo];
        const isSaved = await handlers[currentStep.value]?.();

        if (isSaved) {
            currentStep.value++;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Save Failed',
                detail: 'There was an issue saving the current step. Please try again.',
                life: 3000,
            });
        }
    }
};



const handleStepClick = (targetStep) => {
    if (targetStep <= currentStep.value || isStepValid(currentStep.value)) {
        currentStep.value = targetStep;
    } else {
        showError();
    }
};


const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const showError = () => {
    toast.add({
        severity: 'error',
        summary: 'Validation Error',
        detail: 'Please complete all required fields before proceeding.',
        life: 3000,
    });
};


const openFileModal = (file) => {
    selectedFile.value = file;
    showModal.value = true;
};


const copyAllFields = (index) => {
    if (chainsaws[index].copyAll && index > 0) {
        const first = chainsaws[0];
        chainsaws[index] = {
            ...first,
            copyAll: true,
            letterRequest: null,
        };
    }
};

const addChainsaw = () => {
    chainsaws.push(JSON.parse(JSON.stringify(chainsaw_form)));
};

const removeChainsaw = (index) => {
    if (chainsaws.length > 1) chainsaws.splice(index, 1);
};

const handleFileUpload = (event, index) => {
    chainsaws[index].letterRequest = event.target.files[0];
};

const handlePurposeFileUpload = (event, fieldName, index) => {
    chainsaws[index][fieldName] = event.target.files[0];
};

const handleORFileUpload = (event, field) => {
    payment_form[field] = event.target.files[0];
};


const updateCompanyApplication = async () => {
    isLoading.value = true;
    isloadingSpinner.value = true;

    const applicationId = page.props.application.id;

    const formData = new FormData();

    // 🔥 Append ALL fields from company_form automatically
    Object.entries(company_form).forEach(([key, value]) => {
        // Skip null values safely
        if (value !== null && value !== undefined) {
            formData.append(key, value);
        }
    });

    // 🔥 Add encoded_by separately
    formData.append("encoded_by", userId);

    try {
        const response = await axios.post(
            `/applications/${applicationId}/update-company-data`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        if (response.data.status === "success") {
            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Company application updated successfully.",
                life: 3000,
            });
            return true;
        } else {
            toast.add({
                severity: "warn",
                summary: "No Changes",
                detail: response.data.message,
                life: 3000,
            });
            return false;
        }
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "There was an error saving the application.",
            life: 3000,
        });
        return false;
    } finally {
        isLoading.value = false;
        isloadingSpinner.value = false;
    }
};

const updateChainsawInformation = async () => {
    isLoading.value = true;
    // isloadingSpinner.value = true;
    const applicationId = page.props.application.id;
    try {
        for (const chainsaw of chainsaws) {
            const formData = new FormData();

            // Object.entries(chainsaw_form).forEach(([key, value]) => {
            //     if (value !== null && value !== undefined && !(value instanceof File)) {
            //         formData.append(key, value);
            //     }
            // });

            ['mayorDTI', 'affidavit', 'otherDocs', 'permit'].forEach((fileKey) => {
                if (chainsaw[fileKey]) formData.append(fileKey, chainsaw[fileKey]);
            });

            const response = await axios.put(`/applications/${applicationId}/update-chainsaw-info`, {
                application_id: applicationId,
                permit_chainsaw_no: chainsaw_form.permit_chainsaw_no,
                permit_validity: chainsaw_form.permit_validity,
                brand: chainsaw_form.brand,
                model: chainsaw_form.model,
                quantity: chainsaw_form.quantity,
                supplier_name: chainsaw_form.supplier_name,
                supplier_address: chainsaw_form.supplier_address,
                purpose: chainsaw_form.purpose,
                other_details: chainsaw_form.other_details,
            });

            if (response.data.status === "success") {
                toast.add({
                    severity: "success",
                    summary: "Updated",
                    detail: "Company application updated successfully.",
                    life: 3000,
                });
                return true;
            } else {
                toast.add({
                    severity: "warn",
                    summary: "No Changes",
                    detail: response.data.message,
                    life: 3000,
                });
                return false;
            }
        }
        return true;
    } catch (error) {
        console.error('Upload failed:', error);
        return false;
    } finally {
        isloadingSpinner.value = false;
    }
};

const updatePaymentInfo = async () => {
    isLoading.value = true;
    isloadingSpinner.value = true;

    const applicationId = page.props.application.id;

    const formData = new FormData();

    // 🔥 Append ALL fields from company_form automatically
    Object.entries(payment_form).forEach(([key, value]) => {
        // Skip null values safely
        if (value !== null && value !== undefined) {
            formData.append(key, value);
        }
    });

    // 🔥 Add encoded_by separately
    formData.append("encoded_by", userId);

    try {
        const response = await axios.post(
            `/applications/${applicationId}/update-company-payment-data`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        if (response.data.status === "success") {
            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Payment Info updated successfully.",
                life: 3000,
            });
            return true;
        } else {
            toast.add({
                severity: "warn",
                summary: "No Changes",
                detail: response.data.message,
                life: 3000,
            });
            return false;
        }
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: "Failed",
            detail: "There was an error saving the application.",
            life: 3000,
        });
        return false;
    } finally {
        isLoading.value = false;
        isloadingSpinner.value = false;
    }
};

const submitApplication = async () => {
    try {
        const applicationId = page.props.application.id;
        const officeId = page.props.application.office_title;

        const response = await axios.put(`/applications/${applicationId}/submit-application`, {

            application_id: applicationId,
            office: officeId
        });

        if (response.data.status === "success") {
            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Company application updated successfully.",
                life: 3000,
            });
            router.get(route('applications.pending_application'));
            return true;
        } else {
            toast.add({
                severity: "warn",
                summary: "No Changes",
                detail: response.data.message,
                life: 3000,
            });
            return false;
        }
    } catch (error) {

    }
}
// API Calls


// Map tab number → allowed prefixes (from your folderMap)
const tabMap = {
    1: ['request_letter', 'secretary_certificate'],
    2: ['mayors_permit', 'notarized_documents', 'permit', 'others'],
    3: ['official_receipt']
};

const filesByTab = ref({
    0: [],
    1: [],
    2: [],
    3: []
});

// const getApplicantFile = async (id) => {
//     try {
//         const response = await axios.get(
//             `http://10.201.10.135:8000/api/getApplicantFile/${id}`
//         )

//         if (response.data.status && Array.isArray(response.data.data)) {
//             assessmentRows.value = response.data.data.map(row => ({
//                 checklist_entry_id: row.checklist_entry_id,
//                 application_type: row.application_type,
//                 requirement: "aaa",
//                 remarks: row.remarks,
//                 file_name: row.file_name,
//                 file_url: row.file_url,
//                 uploaded_at: row.created_at
//                     ? new Date(row.created_at).toLocaleDateString()
//                     : null,

//                 // 🔥 IMPORTANT NORMALIZATION
//                 assessment: row.assessment === 'pass'
//                     ? 'passed'
//                     : row.assessment === 'fail'
//                         ? 'failed'
//                         : null,

//                 // 🔒 first-time assessment
//                 is_saved: row.assessment === 'passed' || row.assessment === 'failed'
//             }))

//         }
//     } catch (err) {
//         console.error(err)
//     }
// }
const getApplicantFile = async (application_id) => {
    try {
        const checklistRes = await axios.get(
            `http://10.201.10.135:8000/api/getChecklistEntries/${application_id}`
        );

        const attachmentsRes = await axios.get(
            `http://10.201.10.135:8000/api/getApplicantFile/${application_id}`
        );

        if (checklistRes.data.status && attachmentsRes.data.status) {
            const checklistEntries = checklistRes.data.data;
            const attachments = attachmentsRes.data.data;

            // Map attachments by checklist_entry_id
            // const attachmentsMap = attachments.reduce((acc, file) => {
            //     if (!acc[file.checklist_entry_id]) {
            //         acc[file.checklist_entry_id] = [];
            //     }
            //     acc[file.checklist_entry_id].push(file);
            //     return acc;
            // }, {});

            const attachmentsMap = attachments.reduce((acc, file) => {
                const id = file.checklist_entry_id;

                if (!acc[id]) {
                    acc[id] = {
                        original: null,
                        resubmissions: []
                    };
                }

                if (file.file_name) {
                    if (/_v\d+\./i.test(file.file_name)) {
                        // ✅ resubmitted file
                        acc[id].resubmissions.push(file);
                    } else {
                        // ✅ original file
                        acc[id].original = file;
                    }
                }

                return acc;
            }, {});
            assessmentRows.value = checklistEntries.map(entry => {
                const entryAttachments = attachmentsMap[entry.checklist_entry_id] || [];

                const files = attachmentsMap[entry.checklist_entry_id] || {
                    original: null,
                    resubmissions: []
                };

                return {
                    ...entry,
                    // permit_checklist_id: entry.chklist_id ?? null,
                    permit_checklist_id: entry.permit_checklist_id ?? null,
                    original_file: files.original,
                    attachments: files.original ? [files.original] : [], // for your existing VIEW button
                    resubmissions: files.resubmissions.sort(
                        (a, b) => new Date(a.created_at) - new Date(b.created_at)
                    ),
                    requirement: entry.requirement || 'N/A',
                    assessment: entry.assessment ?? null,
                    is_saved: Boolean(entry.assessment)
                };
            });

            // assessmentRows.value = checklistEntries.map(entry => {
            //     const entryAttachments = attachmentsMap[entry.checklist_entry_id] || [];

            //     // Get saved assessment from the first attachment if present
            //     const savedAssessment = entryAttachments.length > 0
            //         ? entryAttachments[0].assessment
            //         : null;

            //     return {
            //         ...entry,
            //         permit_checklist_id: entryAttachments[0]?.permit_checklist_id ?? null,
            //         attachments: entryAttachments,
            //         requirement: entry.requirement || 'N/A',

            //         // Use entry.assessment if it exists, otherwise use savedAssessment
            //         assessment: entry.assessment ?? savedAssessment,

            //         // Mark as saved if either entry assessment or attachment assessment was present
            //         is_saved: Boolean(entry.assessment ?? savedAssessment)
            //     };
            // });
        }
    } catch (err) {
        console.error('Error loading applicant data:', err);
    }
};

const loadBrands = async () => {
    const applicationId = page.props.application.id;
    if (!applicationId) {
        errorMessage.value = 'No application ID found in the query.';
        isLoading.value = false;
        return;
    }

    const res = await axios.get(
        // `http://10.201.10.135:8000/api/chainsaw/${applicationId}/brands`
        `http://10.201.10.135:8000/api/chainsaw/${applicationId}/supplier`

    )

    // If data exists, overwrite
    if (res.data.length) {
        suppliers.value = res.data
    }
}

const getDocumentaryRequirements = async () => {
    const applicationId = page.props.application.id;
    if (!applicationId) return;

    try {
        const response = await axios.get(`http://10.201.10.135:8000/api/getApplicantFile/${applicationId}`);
        if (response.data.status && Array.isArray(response.data.data)) {
            files.value = response.data.data.map((file: any) => ({
                name: file.file_name,
                size: 'Unknown',
                dateUploaded: new Date(file.created_at).toLocaleDateString(),
                dateOpened: new Date().toLocaleDateString(),
                icon: 'png',
                thumbnail: null,
                url: file.file_url,
                isPassed: null, // null = not selected, true = pass, false = fail

            }));
        } else {
            console.log('No files');
        }
    } catch (error) {
        console.error('Failed to fetch files:', error);
    }
};

// const getApplicantFile = async () => {
//     const applicationId = page.props.application.id;
//     if (!applicationId) return;

//     try {
//         const response = await axios.get(`http://10.201.10.135:8000/api/getApplicantFile/${applicationId}`);
//         if (response.data.status && Array.isArray(response.data.data)) {
//             files.value = response.data.data.map((file) => ({
//                 name: file.file_name,
//                 size: 'Unknown',
//                 dateUploaded: new Date(file.created_at).toLocaleDateString(),
//                 dateOpened: new Date().toLocaleDateString(),
//                 icon: 'png',
//                 thumbnail: null,
//                 url: file.file_url,
//             }));
//         }
//     } catch (error) {
//         console.error('Failed to fetch files:', error);
//     }
// };


const openFile = (file) => {
    selectedFile.value = file;
    showFileModal.value = true;

};

const triggerUpdateFile = (file) => {
    selectedFileToUpdate.value = file
    updateFileInput.value.click();
}

const handleFileUpdate = async (event) => {
    const newFile = event.target.files[0]
    if (!newFile || !selectedFileToUpdate.value) return

    try {
        const formData = new FormData()
        formData.append('application_id', selectedFileToUpdate.value.application_id)
        formData.append('file', newFile)
        formData.append('attachment_id', selectedFileToUpdate.value.attachment_id)
        formData.append('name', selectedFileToUpdate.value.name)

        const response = await axios.post('http://10.201.10.135:8000/api/files/update', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })

        // Update file list
        const updatedIndex = files.value.findIndex(f => f.id === selectedFileToUpdate.value.id)
        if (updatedIndex !== -1) {
            files.value[updatedIndex] = response.data.updatedFile
        }

        toast.add({ severity: 'success', summary: 'Successful', detail: 'File updated successfully', life: 3000 });
        location.reload();

    } catch (error) {
        console.error(error)
        toast.add({ severity: 'error', summary: 'Failed', detail: 'Failed to update the file.', life: 3000 });
    } finally {
        updateFileInput.value.value = '' // reset file input
        selectedFileToUpdate.value = null
    }
}



const companyRequirements = computed(() =>
    assessmentRows.value.filter(r => r.applicant_type === 'company')
);

const getEmbedUrl = (url) => {
    const match = url.match(/[-\w]{25,}/);
    const fileId = match ? match[0] : null;
    return fileId ? `https://drive.google.com/file/d/${fileId}/preview` : '';
};
onMounted(() => {
    if (props.mode === 'view') {
        currentStep.value = 4; // Jump to last step
    }

    // getProvinceCode();
    fetchRoutingHistory();
    getApplicantFile(page.props.application.id);
    loadBrands();
    // getDocumentaryRequirements()
});
</script>
<template>
    <div class="space-y-6">
        <Toast />
        <ReusableConfirmDialog ref="confirmDialogRef" />

        <!-- <LoadingSpinner :loading="isloadingSpinner" /> -->
        <!-- Stepper -->
        <!-- <div class="mb-6 flex items-center justify-between">
            <div v-for="step in steps" :key="step.id" class="flex-1 cursor-pointer text-center"
                @click="handleStepClick(step.id)">
                <div :class="[
                    'mx-auto flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white',
                    currentStep === step.id ? 'bg-green-900' : 'bg-gray-300',
                ]">
                    {{ step.id }}
                </div>
                <div class="mt-2 text-sm font-medium"
                    :class="currentStep === step.id ? 'text-green-600' : 'text-gray-500'">
                    {{ step.label }}
                </div>
            </div>
        </div> -->

        <!-- <div v-if="currentStep === 3" class="space-y-6">
            <Fieldset legend="Payment of Application Fee" :toggleable="false">
                <div v-if="isLoading"
                    class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-50 rounded-lg">
                    <div class="flex flex-col items-center gap-3">
                        <LoaderCircle class="h-10 w-10 animate-spin text-green-900" />
                        <span class="text-green-900 font-semibold text-sm">Saving, please wait...</span>
                    </div>
                </div>
                <div :class="{ 'pointer-events-none opacity-60': isLoading }">
                    <div class="flex items-center justify-end gap-2 text-sm mb-5">
                        <h1 class="text-xl font-semibold">
                            <button
                                class="flex items-center justify-center gap-2 px-3 py-2 rounded bg-red-900 w-[200px] text-white text-lg">
                                <Info class="h-5 w-5" />
                                {{
                                    applicationData.status_title
                                }}
                            </button>
                        </h1>
                    </div>
                    <div class="relative">



                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div :hidden="false">
                                <FloatLabel>
                                    <InputText v-model="payment_form.application_no" :disabled=true
                                        class="w-full" style="font-weight: bolder;" />
                                    <label>Application No.</label>
                                </FloatLabel>
                            </div>
                            <div>
                                <FloatLabel>
                                    <InputText class="w-full" v-model="payment_form.official_receipt" />
                                    <label>O.R No.</label>
                                </FloatLabel>
                            </div>
                            <div>
                                <FloatLabel>
                                    <InputNumber class="w-full" v-model="payment_form.permit_fee" />
                                    <label>Permit Fee</label>
                                </FloatLabel>
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium text-gray-700">Upload Scanned copy of Official
                                    Receipt</label>
                                <input type="file" accept=".jpg,.jpeg,.pdf"
                                    @change="(e) => handleORFileUpload(e, 'or_copy')"
                                    class="mt-1 w-full cursor-pointer rounded-lg border border-dashed border-gray-400 bg-white p-3 text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-blue-700 hover:bg-gray-50" />
                            </div>
                        </div>
                    </div>
                </div>
            </Fieldset>
        </div> -->


        <div v-if="currentStep === 4" class="">
            <div class="relative">
                <!-- YOUR TABLE / CONTENT -->

                <div v-if="isLoading"
                    class="absolute inset-0 bg-white/70 backdrop-blur-sm flex items-center justify-center z-50 rounded-lg">
                    <div class="flex flex-col items-center gap-3">
                        <LoaderCircle class="h-10 w-10 animate-spin text-green-900" />
                        <span class="text-green-900 font-semibold text-sm">
                            Uploading, please wait...
                        </span>
                    </div>
                </div>

                <Fieldset legend="Applicant Details" :toggleable="true">
                    <!-- Applicant Info (non-file fields) -->
                    <!-- <h1 class="font-xl">Below is the checklist of requirements currently pending approval.</h1> -->

                    <div class="relative">

                        <div class="grid grid-cols-1 gap-x-12 gap-y-4 text-sm text-gray-800 md:grid-cols-2">
                            <div class="flex">
                                <span class="w-48 font-semibold">Application No:</span>
                                <Tag :value="company_form.application_no" severity="success" class="text-center" />
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Application Type:</span>
                                <Tag :value="company_form.application_type" severity="success" class="text-center" />
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Date Applied:</span>
                                <span>{{ company_form.date_applied }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Type of Transaction:</span>
                                <span>{{ company_form.type_of_transaction }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Classification:</span>
                                <span>{{ company_form.classification }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Company Name:</span>
                                <span>{{ company_form.company_name }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Authorized Representative:</span>
                                <span>{{ company_form.authorized_representative }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-48 font-semibold">Region:</span>
                                <span>REGION IV-A (CALABARZON)</span>
                            </div>
                            <!-- <div class="flex">
                        <span class="w-48 font-semibold">Province:</span>
                        <span>{{ company_form.prov_name }}</span>
                    </div>
                    <div class="flex">
                        <span class="w-48 font-semibold">Municipality:</span>
                        <span>Lipa City</span>
                    </div>
                    <div class="flex">
                        <span class="w-48 font-semibold">Barangay:</span>
                        <span>Barangay 1</span>
                    </div> -->
                            <div class="flex">
                                <span class="w-48 font-semibold">Complete Address:</span>
                                <span>{{ company_form.company_address }}</span>
                            </div>

                        </div>
                    </div>
                </Fieldset>
                <Fieldset legend="Routing History" toggleable v-model:collapsed="isRoutingCollapsed">
                    <table class="min-w-full rounded-lg border border-gray-300 bg-white text-[12px]">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-4 py-2 text-left">#</th>
                                <th class="border px-4 py-2 text-left">Sender</th>
                                <th class="border px-4 py-2 text-left">Route Details</th>
                                <th class="border px-4 py-2 text-left">Receiver</th>
                                <th class="border px-4 py-2 text-left">Date Returned</th>
                                <th class="border px-4 py-2 text-left">Date Received</th>
                                <th class="border px-4 py-2 text-left">Date Endorsed</th>
                                <th class="border px-4 py-2 text-left">Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in routingHistory" :key="index" class="hover:bg-gray-50">
                                <!-- # -->
                                <td class="border px-4">
                                    {{ index + 1 }}
                                </td>

                                <!-- Sender -->
                                <td class="border px-4" style="width: 10rem">
                                    <div v-if="[2, 4, 6, 8, 10].includes(item.route_order)"></div>

                                    <div v-else>
                                        <b>{{ item.sender_role }}</b><br />
                                        <i>{{ item.sender }}</i>
                                    </div>
                                </td>

                                <!-- Route details -->
                                <td class="border px-4" style="width: 7rem">
                                    <b>Route No. 2026-00{{ item.route_order }}</b>
                                </td>

                                <!-- Receiver -->
                                <td class="border px-4" style="width: 20rem">
                                    <b>{{ item.receiver_role }}</b><br />

                                    <Tag v-if="item.action === 'Received'" severity="danger" size="small"> Received
                                    </Tag>

                                    <Tag v-else-if="item.action === 'Endorsed'" severity="info" size="small"> Endorsed
                                    </Tag>

                                    <Tag v-else-if="item.action == 'Returned to Technical Staff' || item.action == 'Returned to PENRO Technical Staff'"
                                        severity="danger" size="small">
                                        {{ item.action }}

                                    </Tag>
                                    <Tag v-else severity="success" size="small">
                                        {{ item.action }}

                                    </Tag>



                                    <br />
                                </td>
                                <!-- Date Retured -->
                                <td class="birder px-4">
                                    <span
                                        v-if="item.action == 'Returned to Technical Staff' || item.action === 'Returned to PENRO Technical Staff'">
                                        {{
                                            new Date(item.updated_at).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                </td>

                                <!-- Date Received -->
                                <td class="border px-4">
                                    <span v-if="item.route_order == 2">
                                        {{
                                            new Date(item.date_received_rps_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>

                                    <span v-else-if="item.route_order == 4 && item.action == 'Submitted to CHIEF RPS'">
                                        {{
                                            new Date(item.date_endorsed_chiefrps).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span
                                        v-else-if="item.route_order == 4 && item.action == 'Received by the CENRO Officer'">
                                        {{
                                            new Date(item.date_cenro_chief_received).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 6 && item.action == 'Received by the PENRO'">

                                        {{
                                            new Date(item.date_received_penro_technical).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 8">
                                        {{
                                            new Date(item.date_received_penro_rps_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>

                                    <span v-else-if="item.route_order == 10">
                                        {{
                                            new Date(item.date_received_penro_tsd_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 12">
                                        {{
                                            new Date(item.date_received_penro_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 14">
                                        {{
                                            new Date(item.date_received_region_technical).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 16">
                                        {{
                                            new Date(item.date_received_fus_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 18">
                                        {{
                                            new Date(item.date_received_lpddchief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 20">
                                        {{
                                            new Date(item.date_received_ardts).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 22">
                                        {{
                                            new Date(item.date_received_red).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>

                                </td>

                                <!-- Date Endorsed -->
                                <td class="border px-4">
                                    <span v-if="item.route_order == 1">
                                        {{ item.date_endorsed_chiefrps ? new
                                            Date(item.date_endorsed_chiefrps).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            }) : '-' }}

                                    </span>
                                    <span v-if="item.route_order == 3 && item.action != 'Returned to Technical Staff'">
                                        {{
                                            new Date(item.date_endorsed_cenro_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span
                                        v-if="item.route_order == 5 && item.action === 'Submitted to PENRO Technical Staff'">
                                        {{
                                            new Date(item.date_endorsed_penro_technical).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 7">
                                        {{
                                            new Date(item.date_endorsed_penro_chief_rps).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 9">
                                        {{
                                            new Date(item.date_endorsed_penro_chief_tsd).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 11">
                                        {{
                                            new Date(item.date_endorsed_penro).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 13">
                                        {{
                                            new Date(item.date_endorsed_region_technical).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 15">
                                        {{
                                            new Date(item.date_endorsed_fus_chief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 17">
                                        {{
                                            new Date(item.date_endorsed_lpddchief).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 19">
                                        {{
                                            new Date(item.date_endorsed_ardts).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                    <span v-else-if="item.route_order == 21">
                                        {{
                                            new Date(item.date_endorse_red).toLocaleString('en-PH', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: '2-digit',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                                second: '2-digit',
                                                hour12: true,
                                            })
                                        }}
                                    </span>
                                </td>

                                <!-- Remarks -->
                                <td class="border px-4">
                                    {{ item.remarks ?? '-' }}
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="routingHistory.length === 0">
                                <td colspan="5" class="p-4 text-center text-gray-500">No routing history found</td>
                            </tr>
                        </tbody>
                    </table>
                </Fieldset>
                <Fieldset legend="Chainsaw Information" :toggleable="true">
                    <div class="mt-6 grid grid-cols-1 gap-x-12 gap-y-4 text-sm text-gray-800 md:grid-cols-2">

                        <div class="flex">
                            <span class="w-48 font-semibold">Permit Validity:</span>
                            <span>{{ applicationData.permit_validity }}</span>
                        </div>

                        <div class="flex">
                            <span class="w-48 font-semibold">Supplier Name:</span>
                            <span>{{ applicationData.supplier_name }}</span>
                        </div>

                        <div class="flex">
                            <span class="w-48 font-semibold">Purpose of Purchase:</span>
                            <span>{{ applicationData.purpose }}</span>
                        </div>

                        <div class="flex">
                            <span class="w-48 font-semibold">Other Details:</span>
                            <span class="w-48">Covered by Permit to Sell <b>{{ applicationData.permit_chainsaw_no
                                    }}</b>
                                issued on
                                {{ applicationData.issued_date }}, valid
                                until {{ applicationData.permit_validity }} approved/issued by {{
                                    applicationData.issued_by
                                }}
                            </span>
                        </div>

                        <div class="flex">
                            <span class="w-48 font-semibold">Official Receipt:</span>
                            <Tag :value="applicationData.official_receipt" severity="success" />
                        </div>

                        <div class="flex">
                            <span class="w-48 font-semibold">Permit Fee:</span>
                            <span>₱ {{ applicationData.permit_fee }}.00</span>
                        </div>

                        <!-- ✅ Brands & Models -->
                        <div class="md:col-span-2">
                            <span class="block mb-2 font-semibold">Chainsaw Details:</span>

                            <!-- SUPPLIERS -->
                            <div v-for="(supplier, sIndex) in suppliers" :key="sIndex"
                                class="mb-6 rounded-lg border bg-gray-100 p-4">

                                <!-- Supplier Info -->
                                <div class="mb-3 text-sm">
                                    <div><span class="font-semibold">Supplier:</span> {{ supplier.supplier_name }}
                                    </div>
                                    <div><span class="font-semibold">Permit To Sell:</span> {{
                                        supplier.permit_to_sell_no }}</div>
                                </div>

                                <!-- BRANDS -->
                                <div v-for="(brand, bIndex) in supplier.brands" :key="bIndex"
                                    class="mb-4 rounded-lg border bg-gray-50 p-4">

                                    <div class="mb-2">
                                        <span class="font-semibold">Brand:</span>
                                        <span class="ml-2">{{ brand.name }}</span>
                                    </div>

                                    <!-- MODELS TABLE -->
                                    <table class="w-full text-sm border">
                                        <thead class="bg-blue-900 text-white">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Model</th>
                                                <th class="px-3 py-2 text-left">Serial No</th>
                                                <th class="px-3 py-2 text-center w-32">No. of Units</th>
                                                <th class="px-3 py-2 text-left">Date of Issuances</th>
                                                <th class="px-3 py-2 text-left">Date of Expiry</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="(model, mIndex) in brand.models" :key="mIndex">
                                                <td>{{ model.model }}</td>
                                                <td>{{ model.serial_no }}</td>
                                                <td>{{ model.quantity }}</td>
                                                <td>{{ supplier.issued_date }}</td>
                                                <td>{{ supplier.valid_until }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>

                            </div>
                        </div>

                    </div>
                </Fieldset>

                <AssessmentTable v-if="company_form.application_type === 'Company'"
                    title="Company Applicant Requirements" :collapsed="isCollapsed.value"
                    :application_status="company_form.status_title" :roleId="roleId" :rows="companyRequirements"
                    :onsite="onsite" :company_form="company_form" @view-file="openFileModal"
                    @update-assessment="updateAssessment" @update-remarks="updateRemarks" @update-onsite="updateOnsite"
                    @upload-resubmission="handleResubmissionUpload" @remove-resubmission="handleRemoveResubmission" />

                <Dialog v-model:visible="showModal" modal header="File Preview" :style="{ width: '70vw' }">
                    <iframe v-if="selectedFile" :src="getEmbedUrl(selectedFile.file_url)" width="100%" height="500"
                        allow="autoplay"></iframe>
                </Dialog>
            </div>
        </div>

        <div :class="[
            'pt-6 w-full',
            currentStep > 1 && hasFailed
                ? 'grid grid-cols-2 gap-4'
                : 'grid grid-cols-1 gap-4 flex justify-end'
        ]">

            <!-- SHOW RETURN BUTTON IF MAY FAILED -->
            <!-- @click="returnApplication"  -->

            <Button v-if="hasFailed && company_form.status_title !== 'Draft' && currentStep === 4"
                class="w-full h-10 ml-auto px-4 py-2 flex items-center gap-2 rounded-md bg-red-700 text-white hover:bg-red-800"
                @click="() => openReturnDialog(company_form.id)">
                <Undo2 />
                Return Application
            </Button>
            <!-- <Button
                v-else-if="company_form.status_title == 'Draft' || currentStep == 1 || currentStep === 2 || currentStep === 3"
                class="ml-auto bg-green-900 w-full" @click="submitApplication">
                <Send />
                Submit Application
            </Button> -->

            <!-- OTHERWISE SHOW ASSESSMENT MODAL -->
            <!-- ="currentStep === 4" -->
            <AssessmentModal :status_id="company_form.application_status" class="w-full"
                :applicationId="Number(page.props.application.id)" @submit-assessments="submitAllAssessments" />


            <!-- DRAFT SUBMIT -->


        </div>

    </div>
</template>

<style>
/* HTML:  */
.ribbon {
    font-weight: bold;
    color: #fff;
}

.ribbon {
    --f: 0.5em;
    /* control the folded part */
    z-index: 10;
    /* ensure it's on top */
    font-size: 16px;
    /* or adjust as needed */
    position: absolute;
    top: 0;
    right: 0;
    line-height: 1.8;
    padding-inline: 1lh;
    padding-bottom: var(--f);
    border-image: conic-gradient(#0008 0 0) 51% / var(--f);
    clip-path: polygon(100% calc(100% - var(--f)),
            100% 100%,
            calc(100% - var(--f)) calc(100% - var(--f)),
            var(--f) calc(100% - var(--f)),
            0 100%,
            0 calc(100% - var(--f)),
            999px calc(100% - var(--f) - 999px),
            calc(100% - 999px) calc(100% - var(--f) - 999px));
    transform: translate(calc((1 - cos(45deg)) * 100%), -100%) rotate(45deg);
    transform-origin: 0% 100%;
    background-color: #bd1550;
    /* the main color  */
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #2563eb;
    /* blue */
    font-weight: 500;
    text-decoration: none;
    transition: color 0.3s ease;
}

.file-preview:hover {
    color: #1e40af;
    /* darker blue */
    text-decoration: underline;
}

.file-icon {
    width: 30px;
    height: 40px;
    object-fit: contain;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: #f9f9f9;
    padding: 4px;
}

.file-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.file-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    width: 100%;
}
</style>