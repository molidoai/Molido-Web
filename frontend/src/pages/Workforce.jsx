import { useEffect, useState } from 'react'
import { aiApi } from '../services/api'

export default function Workforce() {
  const [agents, setAgents] = useState([])
  const [templates, setTemplates] = useState([])
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [form, setForm] = useState({
    name: '',
    role: 'sales',
    department: 'Sales',
    description: '',
    system_instructions: '',
    skills: '',
  })

  const load = () => {
    aiApi.agents().then((r) => setAgents(r.data.agents || [])).catch(() => setAgents([]))
    aiApi.agentTemplates().then((r) => setTemplates(r.data.templates || [])).catch(() => {})
  }

  useEffect(() => { load() }, [])

  const applyTemplate = (tpl) => {
    setForm({
      name: tpl.name,
      role: tpl.role,
      department: tpl.department,
      description: '',
      system_instructions: tpl.system_instructions,
      skills: (tpl.skills || []).join(', '),
    })
  }

  const create = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    try {
      const payload = {
        name: form.name,
        role: form.role,
        department: form.department,
        description: form.description || null,
        system_instructions: form.system_instructions,
        skills: form.skills
          ? form.skills.split(',').map((s) => s.trim()).filter(Boolean)
          : [],
      }
      const res = await aiApi.createAgent(payload)
      setMsg(res.data.message || 'ساخته شد')
      setForm({ name: '', role: 'sales', department: 'Sales', description: '', system_instructions: '', skills: '' })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در ساخت')
    }
  }

  const remove = async (id) => {
    if (!confirm('حذف این کارمند مجازی؟')) return
    try {
      await aiApi.deleteAgent(id)
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'حذف نشد')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">نیروی کار مجازی AI</h1>
      <p className="text-slate-400 text-sm mb-6">ساخت و مدیریت کارمندان مجازی سازمان شما</p>

      <div className="flex flex-wrap gap-2 mb-4">
        {templates.map((t) => (
          <button key={t.key} type="button" className="btn btn-ghost text-xs" onClick={() => applyTemplate(t)}>
            قالب: {t.name}
          </button>
        ))}
      </div>

      <form onSubmit={create} className="card mb-8 grid gap-3 sm:grid-cols-2">
        <input className="input" placeholder="نام کارمند مجازی" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
        <input className="input" placeholder="نقش (role)" value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })} required />
        <input className="input" placeholder="دپارتمان" value={form.department} onChange={(e) => setForm({ ...form, department: e.target.value })} />
        <input className="input" placeholder="مهارت‌ها (با ویرگول)" value={form.skills} onChange={(e) => setForm({ ...form, skills: e.target.value })} />
        <input className="input sm:col-span-2" placeholder="توضیح کوتاه" value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
        <textarea className="input sm:col-span-2 min-h-[120px]" placeholder="دستورالعمل سیستم (شخصیت و قوانین کارمند)" value={form.system_instructions} onChange={(e) => setForm({ ...form, system_instructions: e.target.value })} required />
        <button className="btn btn-primary sm:col-span-2">ساخت کارمند مجازی</button>
        {msg && <p className="text-cyan-300 text-sm sm:col-span-2">{msg}</p>}
        {err && <p className="text-red-400 text-sm sm:col-span-2">{err}</p>}
      </form>

      <h2 className="text-lg font-semibold mb-3">لیست کارمندان</h2>
      <div className="grid gap-3 sm:grid-cols-2">
        {agents.map((a) => (
          <div key={a.id} className="card">
            <div className="flex justify-between gap-2">
              <div>
                <div className="font-semibold">{a.name}</div>
                <div className="text-xs text-slate-500 mt-1">{a.role} · {a.department}</div>
              </div>
              <span className="text-xs text-slate-400">{a.is_system ? 'سیستمی' : 'سازمانی'}</span>
            </div>
            {a.description && <p className="text-sm text-slate-400 mt-2">{a.description}</p>}
            <div className="flex flex-wrap gap-1 mt-2">
              {(a.skills || []).slice(0, 6).map((s) => (
                <span key={s} className="text-[10px] px-2 py-0.5 rounded-full bg-white/5 text-cyan-300/80">{s}</span>
              ))}
            </div>
            {!a.is_system && (
              <button type="button" className="btn btn-ghost text-xs text-red-300 mt-3" onClick={() => remove(a.id)}>
                حذف
              </button>
            )}
          </div>
        ))}
        {!agents.length && <p className="text-slate-500 text-sm">کارمندی نیست — seed یا بسازید</p>}
      </div>
    </div>
  )
}
