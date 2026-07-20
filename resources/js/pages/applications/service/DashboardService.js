import axios from 'axios'

const API_URL = 'http://localhost:8000/api'

export const dashboardService = {
    async getSummary() {
        const response = await axios.get(`${API_URL}/summary`)
        return response.data
    }
}