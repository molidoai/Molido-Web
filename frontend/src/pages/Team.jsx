import { useEffect, useState } from 'react'
import { teamApi } from '../services/api'

export default function Team() {
  const [members, setMembers] = useState([])
  const [invites, setInvites] = useState([])
  const [email, setEmail] = useState('')
  const [role, setRole] = useState('member')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [debugLink, setDebugLink] = useState('')

  const load = () => {
    teamApi.list()
      .then((r) => {
        setMembers(r.data.members || [])
        setInvites(r.data.invites || [])
      })
      .catch((e) => setErr(e.response?.data?.message || 'دسترسی ندارید یا خطا'))
  }

  useEffect(() => { load() }, [])

  const invite = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    setDebugLink('')
    try {
      const res = await teamApi.invite({ email, role })
      setMsg(res.data.message || 'دعوت ارسال شد')
      if (res.data.debug_link) setDebugLink(res.data.debug_link)
      setEmail('')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در دعوت')
    }
  }

  const revoke = async (id) => {
    try {
      await teamApi.revoke(id)
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'لغو نشد')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">تیم سازمان</h1>
      <p className="text-slate-400 text-sm mb-6">دعوت همکار با ایمیل — نقش عضو یا مدیر</p>

      <form onSubmit={invite} className="card mb-8 flex flex-wrap gap-3 items-end">
        <div className="flex-1 min-w-[200px]">
          <label className="text-xs text-slate-400">ایمیل</label>
          <input className="input" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required placeholder="colleague@email.com" />
        </div>
        <div>
          <label className="text-xs text-slate-400">نقش</label>
          <select className="input" value={role} onChange={(e) => setRole(e.target.value)}>
            <option value="member">عضو</option>
            <option value="admin">مدیر</option>
            <option value="sales">فروش</option>
            <option value="support">پشتیبانی</option>
          </select>
        </div>
        <button className="btn btn-primary">ارسال دعوت</button>
      </form>
      {msg && <p className="text-cyan-300 text-sm mb-2">{msg}</p>}
      {debugLink && <p className="text-xs text-amber-300/90 mb-2 break-all">لینک تست: {debugLink}</p>}
      {err && <p className="text-red-400 text-sm mb-2">{err}</p>}

      <h2 className="font-semibold mb-3">اعضا</h2>
      <div className="space-y-2 mb-8">
        {members.map((m) => (
          <div key={m.id} className="card flex justify-between gap-2 text-sm">
            <div>
              <div className="font-medium">{m.name}</div>
              <div className="text-xs text-slate-500">{m.email}</div>
            </div>
            <span className="text-xs text-slate-400">{m.role?.display_name || m.role?.name || '—'}</span>
          </div>
        ))}
        {!members.length && <p className="text-slate-500 text-sm">عضوی نیست</p>}
      </div>

      <h2 className="font-semibold mb-3">دعوت‌های در انتظار</h2>
      <div className="space-y-2">
        {invites.filter((i) => i.status === 'pending').map((i) => (
          <div key={i.id} className="card flex justify-between gap-2 text-sm items-center">
            <div>
              <div>{i.email}</div>
              <div className="text-xs text-slate-500">{i.role?.name || 'member'} · تا {i.expires_at ? new Date(i.expires_at).toLocaleDateString('fa-IR') : '—'}</div>
            </div>
            <button type="button" className="btn btn-ghost text-xs text-red-300" onClick={() => revoke(i.id)}>لغو</button>
          </div>
        ))}
        {!invites.filter((i) => i.status === 'pending').length && (
          <p className="text-slate-500 text-sm">دعوت معلقی نیست</p>
        )}
      </div>
    </div>
  )
}
