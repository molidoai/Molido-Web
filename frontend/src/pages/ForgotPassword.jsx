import { useState } from 'react'
import { Link } from 'react-router-dom'
import { authApi } from '../services/api'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [loading, setLoading] = useState(false)

  const submit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setErr('')
    setMsg('')
    try {
      const res = await authApi.forgotPassword({ email })
      setMsg(res.data.message || 'اگر ایمیل معتبر باشد لینک ارسال می‌شود')
      if (res.data.debug_link) setMsg((m) => m + ' | debug: ' + res.data.debug_link)
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در ارسال')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-slate-950">
      <form onSubmit={submit} className="card w-full max-w-md space-y-4">
        <h1 className="text-xl font-bold">بازیابی رمز</h1>
        <input className="input" type="email" placeholder="ایمیل" value={email} onChange={(e) => setEmail(e.target.value)} required />
        {msg && <p className="text-sm text-cyan-300">{msg}</p>}
        {err && <p className="text-sm text-red-400">{err}</p>}
        <button className="btn btn-primary w-full" disabled={loading}>{loading ? '...' : 'ارسال لینک'}</button>
        <Link to="/login" className="text-sm text-slate-400 block text-center">بازگشت به ورود</Link>
      </form>
    </div>
  )
}
