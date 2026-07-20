<script lang="ts" setup>
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
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
</script>

<template>
    <ConfirmDialog group="insert-staff">
        
    </ConfirmDialog>
</template>