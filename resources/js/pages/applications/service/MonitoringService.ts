import axios from 'axios'

const API_URL = 'http://10.201.10.208:8000/api'

export const monitoringService = {
    async getMonthlyMonitoring(params: any) {
        const res = await axios.get(`${API_URL}/monthly-monitoring`, {
            params
        })
        return res.data
    },

    // ✅ STAFF SUBMISSION MONITORING
    async getMonthlySubmissionMonitoring(filters: any = {}) {
        const res = await axios.get(`${API_URL}/monthly-accomplishment`, {
            params: filters
        })

        return res.data
    },
    async getIECCEPAdata(filters: any = {}) {
        const res = await axios.get(`${API_URL}/iec_cepa`, {
            params: filters
        })
        return res.data
    },

    async getDATSData(filters: any = {}) {
        const res = await axios.get(`${API_URL}/googlesheet`, {
            params: filters
        })

        return res.data
    },
    // SEND DATA TO GOOGLE SHEET
  
    async exportToGoogleSheet() {
    const res = await axios.post(`${API_URL}/googlesheet/export`)
    return res.data
},
    // LAWIN PATROLLING
    async getLawinData(filters: any = {}) {
        const res = await axios.get(`${API_URL}/lawin/data`, {
            params: filters
        })
        return res.data
    }
}