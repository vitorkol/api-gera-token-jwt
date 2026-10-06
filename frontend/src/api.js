const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const TOKEN_KEY = 'jwt_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token)
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
}

async function request(path, { method = 'GET', body, auth = true } = {}) {
  const headers = { Accept: 'application/json' }
  if (body) headers['Content-Type'] = 'application/json'
  if (auth) {
    const token = getToken()
    if (token) headers.Authorization = `Bearer ${token}`
  }

  const response = await fetch(`${API_URL}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  })

  // 401 com token salvo => sessão inválida/expirada
  if (response.status === 401 && auth && getToken()) {
    clearToken()
  }

  const data = await response.json().catch(() => ({}))
  if (!response.ok) {
    const error = new Error(data.message || `Erro ${response.status}`)
    error.status = response.status
    error.errors = data.errors
    throw error
  }
  return data
}

export const api = {
  login: (email, password) =>
    request('/login', { method: 'POST', body: { email, password }, auth: false }),
  register: (payload) =>
    request('/register', { method: 'POST', body: payload, auth: false }),
  me: () => request('/me'),
  role: () => request('/role'),
  refresh: () => request('/refresh', { method: 'POST' }),
  logout: () => request('/logout', { method: 'POST' }),
  adminPing: () => request('/admin/ping'),
}
