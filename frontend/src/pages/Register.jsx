import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Register() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    organization_name: '',
  })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value })

  const onSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await register(form)
      navigate('/')
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {})?.[0]?.[0] ||
        'ثبت‌نام ناموفق'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-slate-950">
      <div className="w-full max-w-md card">
        <h1 className="text-2xl font-bold mb-1 text-cyan-300">ثبت‌نام</h1>
        <p className="text-slate-400 text-sm mb-6">سازمان شما ساخته می‌شود</p>

        <form onSubmit={onSubmit} className="space-y-3">
          <input className="input" placeholder="نام" value={form.name} onChange={set('name')} required />
          <input className="input" placeholder="نام سازمان" value={form.organization_name} onChange={set('organization_name')} required />
          <input className="input" type="email" placeholder="ایمیل" value={form.email} onChange={set('email')} required />
          <input className="input" type="password" placeholder="رمز عبور" value={form.password} onChange={set('password')} required />
          <input className="input" type="password" placeholder="تکرار رمز" value={form.password_confirmation} onChange={set('password_confirmation')} required />
          {error && <p className="text-red-400 text-sm">{error}</p>}
          <button type="submit" disabled={loading} className="btn btn-primary w-full">
            {loading ? '...' : 'ثبت‌نام'}
          </button>
        </form>

        <p className="text-sm text-slate-500 mt-6 text-center">
          <Link to="/login" className="text-cyan-400 hover:underline">
            ورود
          </Link>
        </p>
      </div>
    </div>
  )
}
