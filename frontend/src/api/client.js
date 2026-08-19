const API_BASE_URL = (import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '');
export const TOKEN_KEY = 'finance_pro_token';
export const ORG_KEY = 'finance_pro_organization';

export async function apiRequest(path, options = {}) {
  const token = localStorage.getItem(TOKEN_KEY);
  const organizationId = localStorage.getItem(ORG_KEY);
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  if (options.body && !headers.has('Content-Type')) headers.set('Content-Type', 'application/json');
  if (token) headers.set('Authorization', `Bearer ${token}`);
  if (organizationId && !headers.has('X-Organization-Id')) headers.set('X-Organization-Id', organizationId);

  const response = await fetch(`${API_BASE_URL}${path}`, { ...options, headers });
  const contentType = response.headers.get('content-type') || '';
  const body = contentType.includes('application/json') ? await response.json() : await response.text();
  if (!response.ok) {
    if (response.status === 401) localStorage.removeItem(TOKEN_KEY);
    const message = typeof body === 'object' && body?.message ? body.message : `API request failed with status ${response.status}`;
    throw new Error(message);
  }
  return body;
}

export const apiHealth = () => apiRequest('/health');
export const login = (email, password) => apiRequest('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) });
export const currentUser = () => apiRequest('/auth/me');
export const logout = () => apiRequest('/auth/logout', { method: 'POST' });
export const currentOrganization = () => apiRequest('/organization/current');
export const dashboardSummary = () => apiRequest('/dashboard/summary');
export const listProjects = () => apiRequest('/projects');
export const createProject = payload => apiRequest('/projects', { method: 'POST', body: JSON.stringify(payload) });
export const listBudgets = () => apiRequest('/budgets');
export const createBudget = payload => apiRequest('/budgets', { method: 'POST', body: JSON.stringify(payload) });
export const listTransactions = (type = '') => apiRequest(`/transactions${type ? `?type=${encodeURIComponent(type)}` : ''}`);
export const createTransaction = payload => apiRequest('/transactions', { method: 'POST', body: JSON.stringify(payload) });
export const submitTransaction = id => apiRequest(`/transactions/${id}/submit`, { method: 'POST' });
export const approveTransaction = id => apiRequest(`/transactions/${id}/approve`, { method: 'POST' });
export const reconcileTransaction = id => apiRequest(`/transactions/${id}/reconcile`, { method: 'POST' });
export const financialReport = params => {
  const query = new URLSearchParams(params || {}).toString();
  return apiRequest(`/reports/financial${query ? `?${query}` : ''}`);
};
