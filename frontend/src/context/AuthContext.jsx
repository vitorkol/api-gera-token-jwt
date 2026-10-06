import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { api, clearToken, getToken, setToken } from '../api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  // Ao montar: se há token salvo, valida com GET /api/me
  useEffect(() => {
    let cancelled = false
    async function bootstrap() {
      if (!getToken()) {
        setLoading(false)
        return
      }
      try {
        const { user: me } = await api.me()
        if (!cancelled) setUser(me)
      } catch {
        clearToken()
      } finally {
        if (!cancelled) setLoading(false)
      }
    }
    bootstrap()
    return () => {
      cancelled = true
    }
  }, [])

  async function login(email, password) {
    const { access_token } = await api.login(email, password)
    setToken(access_token)
    const { user: me } = await api.me()
    setUser(me)
    return me
  }

  async function register(payload) {
    const { access_token } = await api.register(payload)
    setToken(access_token)
    const { user: me } = await api.me()
    setUser(me)
    return me
  }

  async function logout() {
    try {
      await api.logout()
    } catch {
      // token já inválido: segue o logout local
    }
    clearToken()
    setUser(null)
  }

  const value = useMemo(
    () => ({ user, loading, login, register, logout, isAuthenticated: !!user }),
    [user, loading],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth deve ser usado dentro de <AuthProvider>')
  return ctx
}
