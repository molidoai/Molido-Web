import { useEffect, useState } from 'react'
import { orgApi } from '../services/api'

export default function Settings() {
  const [name, setName] = useState('')
  const [timezone, setTimezone] = useState('Asia/Tehran')
  const [locale, setLocale] = useState('fa')
  const [notify_email, setNotify] = useState('')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  useEffect(() => {
    orgApi.show().then((res) => {
      const o = res.data.organization
      setName(o.name || '')
      setTimezone(o.settings?.timezone || 'Asia/Tehran')
      setLocale(o.settings?.locale || 'fa')
      setNotify(o.settings?.notify_email || '')
    }).catch(() => {})
  }, [])

  const save = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    try {
      const res = await orgApi.update({
        name,
        settings: { timezone, locale, notify_email: notify_email || null },
      })
      setMsg(res.data.message || 'ذخیره شد')
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">تنظیمات سازمان</h1>
      <form onSubmit={save} className="card max-w-lg space-y-3">
        <div>
          <label className="text-xs text-slate-400">نام سازمان</label>
          <input className="input" value={name} onChange={(e) => setName(e.target.value)} />
        </div>
        <div>
          <label className="text-xs text-slate-400">منطقه زمانی</label>
          <input className="input" value={timezone} onChange={(e) => setTimezone(e.target.value)} />
        </div>
        <div>
          <label className="text-xs text-slate-400">زبان</label>
          <select className="input" value={locale} onChange={(e) => setLocale(e.target.value)}>
            <option value="fa">فارسی</option>
            <option value="en">English</option>
          </select>
        </div>
        <div>
          <label className="text-xs text-slate-400">ایمیل اطلاع‌رسانی</label>
          <input className="input" type="email" value={notify_email} onChange={(e) => setNotify(e.target.value)} />
        </div>
        {msg && <p className="text-sm text-cyan-300">{msg}</p>}
        {err && <p className="text-sm text-red-400">{err}</p>}
        <button className="btn btn-primary">ذخیره</button>
      </form>
    </div>
  )
}
