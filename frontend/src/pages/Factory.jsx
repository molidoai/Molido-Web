import { useEffect, useState } from 'react'
import { factoryApi } from '../services/api'

export default function Factory() {
  const [items, setItems] = useState([])
  const [templates, setTemplates] = useState([])
  const [name, setName] = useState('')
  const [template, setTemplate] = useState('ai_api')
  const [description, setDescription] = useState('')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [twin, setTwin] = useState(null)

  const load = () => {
    factoryApi.list().then((r) => setItems(r.data.data || r.data || [])).catch(() => setItems([]))
    factoryApi.templates().then((r) => {
      const t = r.data.templates || []
      setTemplates(t)
      if (t[0]) setTemplate(t[0].key)
    }).catch(() => {})
  }

  useEffect(() => { load() }, [])

  const create = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    try {
      const res = await factoryApi.create({ name, template, description: description || null })
      setMsg(res.data.message || 'ایجاد شد')
      setName('')
      setDescription('')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  const openTwin = async (id) => {
    try {
      const res = await factoryApi.get(id)
      setTwin(res.data)
    } catch {
      setTwin(null)
    }
  }

  const setStatus = async (id, status) => {
    try {
      await factoryApi.update(id, { status })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-1">AI Factory</h1>
      <p className="text-slate-400 text-sm mb-6">
        موتور ساخت پروژه AI داخل MOLIDO CORE — یک پلتفرم، نه استک جدا
      </p>

      <form onSubmit={create} className="card mb-8 grid gap-3 sm:grid-cols-2">
        <input className="input" placeholder="نام پروژه *" value={name} onChange={(e) => setName(e.target.value)} required />
        <select className="input" value={template} onChange={(e) => setTemplate(e.target.value)}>
          {templates.map((t) => (
            <option key={t.key} value={t.key}>{t.label}</option>
          ))}
        </select>
        <input className="input sm:col-span-2" placeholder="توضیح" value={description} onChange={(e) => setDescription(e.target.value)} />
        <button className="btn btn-primary sm:col-span-2">ایجاد پروژه Factory</button>
        {msg && <p className="text-cyan-300 text-sm sm:col-span-2">{msg}</p>}
        {err && <p className="text-red-400 text-sm sm:col-span-2">{err}</p>}
      </form>

      <div className="space-y-3 mb-8">
        {items.map((p) => (
          <div key={p.id} className="card flex flex-wrap justify-between gap-3 items-center">
            <div>
              <div className="font-medium">{p.name}</div>
              <div className="text-xs text-slate-500">{p.template} · {p.status} · {p.slug}</div>
            </div>
            <div className="flex flex-wrap gap-2">
              <select className="input text-xs py-1 w-auto" value={p.status} onChange={(e) => setStatus(p.id, e.target.value)}>
                {['draft', 'active', 'paused', 'archived'].map((s) => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
              <button type="button" className="btn btn-ghost text-xs" onClick={() => openTwin(p.id)}>وضعیت واقعی</button>
            </div>
          </div>
        ))}
        {!items.length && <p className="text-slate-500 text-sm">پروژه‌ای نیست</p>}
      </div>

      {twin && (
        <div className="card border-cyan-500/20">
          <h2 className="font-semibold mb-2">Digital Twin — {twin.project?.name}</h2>
          <pre className="text-xs text-slate-400 overflow-x-auto">{JSON.stringify(twin.digital_twin, null, 2)}</pre>
          <p className="text-[11px] text-slate-600 mt-2">فقط وضعیت واقعی فیلدها — بدون داده جعلی</p>
        </div>
      )}
    </div>
  )
}
