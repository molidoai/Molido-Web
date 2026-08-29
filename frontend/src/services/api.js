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
  forgotPassword: (data) => api.post('/auth/forgot-password', data),
  resetPassword: (data) => api.post('/auth/reset-password', data),
}

export const orgApi = {
  show: () => api.get('/organization'),
  update: (data) => api.put('/organization', data),
}


export const customerApi = {
  list: (params) => api.get('/customers', { params }),
  create: (data) => api.post('/customers', data),
  get: (id) => api.get(`/customers/${id}`),
  exportCsv: () => api.get('/customers/export', { responseType: 'blob' }),
}

export const aiApi = {
  conversations: () => api.get('/ai/conversations'),
  createConversation: (data) => api.post('/ai/conversations', data),
  messages: (id) => api.get(`/ai/conversations/${id}/messages`),
  send: (id, data) => api.post(`/ai/conversations/${id}/send`, data),
  agents: () => api.get('/ai/agents'),
  agentTemplates: () => api.get('/ai/agents/templates'),
  createAgent: (data) => api.post('/ai/agents', data),
  updateAgent: (id, data) => api.put(`/ai/agents/${id}`, data),
  deleteAgent: (id) => api.delete(`/ai/agents/${id}`),
  listTeams: () => api.get('/ai/teams'),
  createTeam: (data) => api.post('/ai/teams', data),
  deleteTeam: (id) => api.delete(`/ai/teams/${id}`),
}

export const moduleApi = {
  list: () => api.get('/modules'),
  my: () => api.get('/modules/my'),
  activate: (slug) => api.post(`/modules/${slug}/activate`),
}

export const leadApi = {
  list: (params) => api.get('/crm/leads', { params }),
  create: (data) => api.post('/crm/leads', data),
  update: (id, data) => api.put(`/crm/leads/${id}`, data),
  remove: (id) => api.delete(`/crm/leads/${id}`),
  convert: (id, data) => api.post(`/crm/leads/${id}/convert`, data || {}),
}

export const dealApi = {
  list: (params) => api.get('/crm/deals', { params }),
  create: (data) => api.post('/crm/deals', data),
  update: (id, data) => api.put(`/crm/deals/${id}`, data),
}

export const productApi = {
  list: (params) => api.get('/erp/products', { params }),
  create: (data) => api.post('/erp/products', data),
  update: (id, data) => api.put(`/erp/products/${id}`, data),
}

export const orderApi = {
  list: (params) => api.get('/erp/orders', { params }),
  create: (data) => api.post('/erp/orders', data),
  update: (id, data) => api.put(`/erp/orders/${id}`, data),
}

export const taskApi = {
  list: (params) => api.get('/ai/tasks', { params }),
  create: (data) => api.post('/ai/tasks', data),
  get: (id) => api.get(`/ai/tasks/${id}`),
  updateStatus: (id, data) => api.patch(`/ai/tasks/${id}/status`, data),
}

export const approvalApi = {
  list: (params) => api.get('/ai/approvals', { params }),
  create: (data) => api.post('/ai/approvals', data),
  review: (id, data) => api.post(`/ai/approvals/${id}/review`, data),
}

export const paymentApi = {
  list: (params) => api.get('/payments', { params }),
  initiate: (data) => api.post('/payments/initiate', data),
  get: (uuid) => api.get(`/payments/${uuid}`),
}

export const subscriptionApi = {
  plans: () => api.get('/subscriptions/plans'),
  list: () => api.get('/subscriptions'),
  subscribe: (data) => api.post('/subscriptions/subscribe', data),
  cancel: (id, data) => api.post(`/subscriptions/${id}/cancel`, data),
}

export const auditApi = {
  list: (params) => api.get('/audit-logs', { params }),
}

export const dashboardApi = {
  stats: () => api.get('/dashboard/stats'),
}

export const featureFlagApi = {
  list: () => api.get('/feature-flags'),
  enabled: () => api.get('/feature-flags/enabled'),
  update: (key, data) => api.put(`/feature-flags/${key}`, data),
}

export const teamApi = {
  list: () => api.get('/team'),
  invite: (data) => api.post('/team/invites', data),
  revoke: (id) => api.post(`/team/invites/${id}/revoke`),
  preview: (token) => api.get('/invites/preview', { params: { token } }),
  accept: (data) => api.post('/invites/accept', data),
}

export const notificationApi = {
  list: () => api.get('/notifications'),
  read: (id) => api.post(`/notifications/${id}/read`),
  readAll: () => api.post('/notifications/read-all'),
}

export const knowledgeApi = {
  list: (params) => api.get('/knowledge', { params }),
  create: (data) => api.post('/knowledge', data),
  search: (q) => api.get('/knowledge/search', { params: { q } }),
}

export const factoryApi = {
  templates: () => api.get('/factory/projects/templates'),
  list: (params) => api.get('/factory/projects', { params }),
  create: (data) => api.post('/factory/projects', data),
  get: (id) => api.get(`/factory/projects/${id}`),
  update: (id, data) => api.put(`/factory/projects/${id}`, data),
  remove: (id) => api.delete(`/factory/projects/${id}`),
}

export default api



