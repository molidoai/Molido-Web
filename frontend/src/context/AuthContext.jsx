import { createContext, useContext, useEffect, useState } from 'react'
import { authApi } from '../services/api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const token = localStorage.getItem('molido_token')
    if (token) {
      authApi
        .me()
        .then((res) => setUser(res.data.user))
        .catch(() => {
          localStorage.removeItem('molido_token')
        })
        .finally(() => setLoading(false))
    } else {
      setLoading(false)
    }
  }, [])

  const login = async (email, password) => {
    const res = await authApi.login({ email, password })
    localStorage.setItem('molido_token', res.data.token)
    setUser(res.data.user)
    return res.data
  }

  const register = async (payload) => {
    const res = await authApi.register(payload)
    localStorage.setItem('molido_token', res.data.token)
    setUser(res.data.user)
    return res.data
  }

  const logout = async () => {
    try {
      await authApi.logout()
    } catch (_) {}
    localStorage.removeItem('molido_token')
    setUser(null)
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => useContext(AuthContext)
