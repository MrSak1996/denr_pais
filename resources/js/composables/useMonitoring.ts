import { ref, computed } from 'vue'
import { monitoringService } from '../pages/applications/service/monitoringService'

export function useMonitoring() {
    const data = ref<any[]>([])
    const loading = ref(false)

    const fetchMonitoring = async (filters: any = {}) => {
        loading.value = true
        try {
            const res = await monitoringService.getMonthlyMonitoring(filters)
            data.value = res
        } finally {
            loading.value = false
        }
    }

    // ✅ AUTO TOTALS
    const totals = computed(() => {
        const months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']

        const result: any = {}

        months.forEach(m => {
            result[m] = data.value.reduce((sum, row) => sum + Number(row[m] || 0), 0)
        })

        result.approved_resolutions = data.value.reduce((s, r) => s + Number(r.approved_resolutions || 0), 0)
        result.approved_clearances = data.value.reduce((s, r) => s + Number(r.approved_clearances || 0), 0)
        result.received = data.value.reduce((s, r) => s + Number(r.received || 0), 0)
        result.pamb_minutes = data.value.reduce((s, r) => s + Number(r.pamb_minutes || 0), 0)

        return result
    })

    return {
        data,
        totals,
        loading,
        fetchMonitoring
    }
}

export function useSubmissionMonitoring() {

    const data = ref<any[]>([])
    const loading = ref(false)

    const fetchSubmissionMonitoring = async (filters: any = {}) => {

        loading.value = true

        try {

            const res = await monitoringService.getMonthlySubmissionMonitoring(filters)

            data.value = res

        } catch (error) {

            console.error(error)

        } finally {

            loading.value = false
        }
    }
    const getIEC = async (filters:any = {}) => {
        loading.value = true

        try {

            const res = await monitoringService.getIECCEPAdata(filters)

            data.value = res

        } catch (error) {

            console.error(error)

        } finally {

            loading.value = false
        }
    }
    // ✅ TOTALS
    const totals = computed(() => {

        const months = [
            'jan', 'feb', 'mar',
            'apr', 'may', 'jun',
            'jul', 'aug', 'sep',
            'oct', 'nov', 'dec'
        ]

        const result: any = {}

        months.forEach((m) => {

            result[m] = data.value.reduce(
                (sum, row) => sum + Number(row[m] || 0),
                0
            )
        })

        result.total_submitted = data.value.reduce(
            (s, r) => s + Number(r.total_submitted || 0),
            0
        )

        result.total_reports = data.value.reduce(
            (s, r) => s + Number(r.total_reports || 0),
            0
        )

        return result
    })

    return {
        data,
        totals,
        loading,
        getIEC,
        fetchSubmissionMonitoring
    }
}

export function useDATSMonitoring() {
    const datsData = ref<any[]>([])
    const totalRecords = ref(0)
    const loading = ref(false)

    // =========================
    // FETCH DATA
    // =========================
    const fetchDATSData = async (filters: any = {}) => {
        loading.value = true

        try {
            const res = await monitoringService.getDATSData(filters)

            datsData.value = res.data || []
            totalRecords.value = res.total_records || 0

        } catch (error) {
            console.error("Failed to fetch DATS data:", error)
        } finally {
            loading.value = false
        }
    }

    // =========================
    // SAFE FIELD HELPERS
    // =========================

    const getField = (row: any, key: string) => {
        return (row?.[key] ?? "").toString()
    }

    const getDatsNumber = (row: any) => {
        return (
            row["DATS NUMBER"] ||
            row["DATS NO"] ||
            row["DATS_NUMBER"] ||
            row["DATS NUMBER "] || // trailing space safety
            "N/A"
        ).toString().trim()
    }

    const getOutgoingCDD = (row: any) => {
        return getField(row, "OUTGOING DATE\n(CDD)").trim()
    }

    const getCategory = (row: any) => {
        return getField(row, "CATEGORY").toUpperCase()
    }

    const getMov = (row: any) => {
        return getField(row, "MOV'S").toUpperCase().trim()
    }

    // =========================
    // STATUS CONDITIONS
    // =========================
    const isCompletedPambMinutes = (row: any) => {
        const isMinutes =
            (row["CATEGORY"] || "")
                .toUpperCase()
                .includes("MINUTES")

        const hasMov =
            (row["MOV'S"] || "")
                .toUpperCase()
                .trim() === "YES"

        const hasOutgoingCDD =
            (row["OUTGOING DATE\n(CDD)"] || "")
                .toString()
                .trim() !== ""

        return isMinutes && hasMov && hasOutgoingCDD
    }

    // const isCompleted = (row: any) => {
    //     const isMinutes = getCategory(row).includes("MINUTES")
    //     const hasMov = getMov(row) === "YES"
    //     const hasOutgoingCDD = getOutgoingCDD(row) !== ""

    //     return getOutgoingCDD(row) !== ""
    // }

    const isPendingMinutes = (row: any) => {
        const isMinutes = getCategory(row).includes("MINUTES")
        const hasMov = getMov(row) === "YES"
        const hasOutgoingCDD = getOutgoingCDD(row) !== ""

        return isMinutes && hasMov && !hasOutgoingCDD
    }

    const isReceivedThisMonth = (row: any) => {
        const date = row["DATE ENCODED"]

        if (!date) return false

        const d = new Date(date)
        if (isNaN(d.getTime())) return false

        const now = new Date()

        return (
            d.getMonth() === now.getMonth() &&
            d.getFullYear() === now.getFullYear()
        )
    }

    const isYear2026 = (row: any) => {
        const date = row["DATE ENCODED"];
        if (!date) return false;

        const d = new Date(date);
        if (isNaN(d.getTime())) return false;

        return d.getFullYear() === 2026;
    };

    // =========================
    // KPI COMPUTED
    // =========================

    const dats = computed(() => {
        const rows = datsData.value;

        // 🔥 FIRST: filter only 2026 data
        const rows2026 = rows.filter(isYear2026);

        const completedPambMinutes = rows2026.filter(isCompletedPambMinutes);
        const pendingMinutes = rows2026.filter(isPendingMinutes);
        const receivedThisMonth = rows2026.filter(isReceivedThisMonth);

        const pendingMinutesDocuments = pendingMinutes.map(row => ({
            ...row,
            dats_number: getDatsNumber(row)
        }));

        const completedMinutesDocuments = completedPambMinutes.map(row => ({
            ...row,
            dats_number: getDatsNumber(row)
        }));

        return {
            total_documents: rows2026.length, // 🔥 FIXED
            completed: completedPambMinutes.length,
            received_this_month: receivedThisMonth.length,
            pending_minutes: pendingMinutes.length,

            completed_minutes_documents: completedMinutesDocuments,
            pending_minutes_documents: pendingMinutesDocuments
        };
    });

    // =========================
    // RETURN
    // =========================

    return {
        datsData,
        totalRecords,
        loading,
        dats,
        fetchDATSData
    }
}

// LAWIN PATROLLING

export function useLawinMonitoring() {
    const lawinData = ref<any[]>([])
    const totalRecords = ref(0)
    const loading = ref(false)

    const fetchLawinData = async (filters: any = {}) => {
        loading.value = true

        try {
            const res = await monitoringService.getLawinData(filters)
            lawinData.value = res;
        } finally {
            loading.value = false
        }
    }

    return {
        lawinData,
        loading,
        fetchLawinData
    }
}