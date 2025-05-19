import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true // Important for handling sessions
})

// Views
export const viewsApi = {
  getDashboard: () => window.location.href = '/dashboard',
  getPosts: () => window.location.href = '/posts',
  getSchedules: () => window.location.href = '/schedules',
  getNotifications: () => window.location.href = '/notifications',
  getContentGenerator: () => window.location.href = '/generate-content-ui'
}

// API Endpoints
export const contentApi = {
  generateContent: (params) => api.post('/api/generate-content', params)
}

// OAuth
export const oauthApi = {
  connectAccount: (platform) => window.location.href = `/oauth/${platform}/login`,
  disconnectAccount: (platform) => api.post(`/oauth/${platform}/disconnect`)
}

// Session Management
export const authApi = {
  login: (credentials) => api.post('/login', credentials),
  logout: () => window.location.href = '/logout',
  register: (userData) => api.post('/register', userData)
}

// Add request interceptor to handle authentication
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Redirect to login page if unauthorized
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
