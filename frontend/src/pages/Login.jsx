import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Login() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const onSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await login(email, password)
      navigate('/')
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'ورود ناموفق')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-slate-950">
      <div className="w-full max-w-md card">
        <h1 className="text-2xl font-bold mb-1 bg-gradient-to-l from-cyan-400 to-purple-400 bg-clip-text text-transparent">
          MOLIDO
        </h1>
        <p className="text-slate-400 text-sm mb-6">ورود به مرکز فرمان</p>

        <form onSubmit={onSubmit} className="space-y-4">
          <div>
            <label className="text-xs text-slate-400 mb-1 block">ایمیل</label>
            <input className="input" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </div>
          <div>
            <label className="text-xs text-slate-400 mb-1 block">رمز عبور</label>
            <input className="input" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </div>
          {error && <p className="text-red-400 text-sm">{error}</p>}
          <button type="submit" disabled={loading} className="btn btn-primary w-full">
            {loading ? '...' : 'ورود'}
          </button>
        </form>

        <p className="text-sm text-slate-500 mt-6 text-center">
          حساب ندارید؟{' '}
          <Link to="/register" className="text-cyan-400 hover:underline">
            ثبت‌نام
          </Link>
        </p>
      </div>
    </div>
  )
}
