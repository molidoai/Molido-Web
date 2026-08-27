import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('molido_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('molido_token')
      localStorage.removeItem('molido_user')
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(err)
  }
)

export const authApi = {
  register: (data) => api.post('/auth/register', data),
  login: (data) => api.post('/auth/login', data),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
}

export const customerApi = {
  list: (params) => api.get('/customers', { params }),
  create: (data) => api.post('/customers', data),
  get: (id) => api.get(`/customers/${id}`),
}

export const aiApi = {
  conversations: () => api.get('/ai/conversations'),
  createConversation: (data) => api.post('/ai/conversations', data),
  messages: (id) => api.get(`/ai/conversations/${id}/messages`),
  send: (id, data) => api.post(`/ai/conversations/${id}/send`, data),
  agents: () => api.get('/ai/agents'),
}

export const moduleApi = {
  list: () => api.get('/modules'),
  my: () => api.get('/modules/my'),
  activate: (slug) => api.post(`/modules/${slug}/activate`),
}

export const leadApi = {
  list: (params) => api.get('/crm/leads', { params }),
}

export const productApi = {
  list: (params) => api.get('/erp/products', { params }),
}

export default api
