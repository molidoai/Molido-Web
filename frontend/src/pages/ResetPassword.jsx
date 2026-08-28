import { useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { authApi } from '../services/api'

export default function ResetPassword() {
  const [params] = useSearchParams()
  const [email, setEmail] = useState(params.get('email') || '')
  const [token, setToken] = useState(params.get('token') || '')
  const [password, setPassword] = useState('')
  const [password_confirmation, setConfirm] = useState('')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  const submit = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    try {
      const res = await authApi.resetPassword({ email, token, password, password_confirmation })
      setMsg(res.data.message || 'رمز تغییر کرد')
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-slate-950">
      <form onSubmit={submit} className="card w-full max-w-md space-y-3">
        <h1 className="text-xl font-bold">رمز جدید</h1>
        <input className="input" type="email" placeholder="ایمیل" value={email} onChange={(e) => setEmail(e.target.value)} required />
        <input className="input" placeholder="توکن" value={token} onChange={(e) => setToken(e.target.value)} required />
        <input className="input" type="password" placeholder="رمز جدید" value={password} onChange={(e) => setPassword(e.target.value)} required />
        <input className="input" type="password" placeholder="تکرار رمز" value={password_confirmation} onChange={(e) => setConfirm(e.target.value)} required />
        {msg && <p className="text-sm text-emerald-400">{msg}</p>}
        {err && <p className="text-sm text-red-400">{err}</p>}
        <button className="btn btn-primary w-full">ذخیره رمز</button>
        <Link to="/login" className="text-sm text-slate-400 block text-center">ورود</Link>
      </form>
    </div>
  )
}
