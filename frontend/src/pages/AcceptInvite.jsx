import { useEffect, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { teamApi } from '../services/api'

export default function AcceptInvite() {
  const [params] = useSearchParams()
  const token = params.get('token') || ''
  const [preview, setPreview] = useState(null)
  const [name, setName] = useState('')
  const [password, setPassword] = useState('')
  const [password_confirmation, setConfirm] = useState('')
  const [err, setErr] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    if (!token) return
    teamApi.preview(token)
      .then((r) => setPreview(r.data))
      .catch((e) => setErr(e.response?.data?.message || 'دعوت نامعتبر'))
  }, [token])

  const submit = async (e) => {
    e.preventDefault()
    setErr('')
    try {
      const res = await teamApi.accept({ token, name, password, password_confirmation })
      if (res.data.token) {
        localStorage.setItem('molido_token', res.data.token)
        localStorage.setItem('molido_user', JSON.stringify(res.data.user))
      }
      navigate('/')
      window.location.reload()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-slate-950">
      <form onSubmit={submit} className="card w-full max-w-md space-y-3">
        <h1 className="text-xl font-bold">پذیرش دعوت</h1>
        {preview && (
          <p className="text-sm text-slate-400">
            دعوت به <strong className="text-cyan-300">{preview.organization?.name || 'سازمان'}</strong>
            {preview.email && <> برای {preview.email}</>}
          </p>
        )}
        <input className="input" placeholder="نام شما" value={name} onChange={(e) => setName(e.target.value)} required />
        <input className="input" type="password" placeholder="رمز عبور" value={password} onChange={(e) => setPassword(e.target.value)} required />
        <input className="input" type="password" placeholder="تکرار رمز" value={password_confirmation} onChange={(e) => setConfirm(e.target.value)} required />
        {err && <p className="text-red-400 text-sm">{err}</p>}
        <button className="btn btn-primary w-full" disabled={!token}>عضویت</button>
        <Link to="/login" className="text-sm text-slate-500 block text-center">ورود</Link>
      </form>
    </div>
  )
}
