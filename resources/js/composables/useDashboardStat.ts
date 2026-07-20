import { ref, computed } from 'vue'
import { dashboardService } from '../pages/applications/service/DashboardService'

export function useDashboardStats() {
    const dashboardData = ref<any>({})
    const loading = ref(false)
    const error = ref<string | null>(null)

    const fetchDashboardData = async () => {
        loading.value = true
        error.value = null

        try {
            const data = await dashboardService.getSummary()
            dashboardData.value = data
        } catch (err: any) {
            error.value = 'Failed to fetch dashboard data'
            console.error(err)
        } finally {
            loading.value = false
        }
    }

    // ✅ Computed statistics
const totalApplications = computed(() => Number(dashboardData.value.total) || 0)
const totalPending = computed(() => Number(dashboardData.value.pending) || 0)
const totalDeferred = computed(() => Number(dashboardData.value.deferred) || 0)
const totalApproved = computed(() => Number(dashboardData.value.approved) || 0)

    return {
        dashboardData,
        loading,
        error,
        fetchDashboardData,
        totalApplications,
        totalApproved,
        totalDeferred,
        totalPending
    }
}