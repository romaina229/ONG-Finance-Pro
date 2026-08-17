const API_BASE_URL = (import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '');

export async function apiRequest(path, options = {}) {
  const token = localStorage.getItem('finance_pro_token');
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');

  if (options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  if (token) {
    headers.set('Authorization', `Bearer ${token}`);
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  });

  const contentType = response.headers.get('content-type') || '';
  const body = contentType.includes('application/json')
    ? await response.json()
    : await response.text();

  if (!response.ok) {
    const message = typeof body === 'object' && body?.message
      ? body.message
      : `API request failed with status ${response.status}`;
    throw new Error(message);
  }

  return body;
}

export function apiHealth() {
  return apiRequest('/health');
}

export function login(email, password) {
  return apiRequest('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });
}

export function currentUser(organizationId) {
  return apiRequest('/auth/me', {
    headers: organizationId ? { 'X-Organization-Id': String(organizationId) } : {},
  });
}

export function currentOrganization(organizationId) {
  return apiRequest('/organization/current', {
    headers: { 'X-Organization-Id': String(organizationId) },
  });
}
