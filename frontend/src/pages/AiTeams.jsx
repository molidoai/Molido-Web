import { useEffect, useState } from 'react'
import { aiApi } from '../services/api'

export default function AiTeams() {
  const [teams, setTeams] = useState([])
  const [agents, setAgents] = useState([])
  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const [selected, setSelected] = useState([])
  const [leadId, setLeadId] = useState('')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  const load = () => {
    aiApi.listTeams().then((r) => setTeams(r.data.teams || [])).catch(() => setTeams([]))
    aiApi.agents().then((r) => {
      const list = r.data.agents || []
      setAgents(list)
      if (list[0] && !leadId) setLeadId(String(list[0].id))
    }).catch(() => {})
  }

  useEffect(() => { load() }, [])

  const toggle = (id) => {
    setSelected((prev) =>
      prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
    )
  }

  const create = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    if (!selected.length) {
      setErr('حداقل یک کارمند مجازی انتخاب کنید')
      return
    }
    try {
      const res = await aiApi.createTeam({
        name,
        description: description || null,
        agent_ids: selected.map(Number),
        lead_agent_id: leadId ? Number(leadId) : selected[0],
      })
      setMsg(res.data.message || 'تیم ساخته شد')
      setName('')
      setDescription('')
      setSelected([])
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در ساخت تیم')
    }
  }

  const remove = async (id) => {
    if (!confirm('حذف تیم؟')) return
    try {
      await aiApi.deleteTeam(id)
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'حذف نشد')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">تیم‌های هوش مصنوعی</h1>
      <p className="text-slate-400 text-sm mb-6">
        چند کارمند مجازی را در یک تیم جمع کنید — مسیریابی خودکار بر اساس موضوع پیام
      </p>

      <form onSubmit={create} className="card mb-8 space-y-4">
        <input className="input" placeholder="نام تیم (مثلاً تیم فروش)" value={name} onChange={(e) => setName(e.target.value)} required />
        <input className="input" placeholder="توضیح کوتاه" value={description} onChange={(e) => setDescription(e.target.value)} />

        <div>
          <div className="text-xs text-slate-400 mb-2">اعضای تیم</div>
          <div className="flex flex-wrap gap-2">
            {agents.map((a) => (
              <button
                key={a.id}
                type="button"
                onClick={() => toggle(a.id)}
                className={`btn text-xs ${selected.includes(a.id) ? 'btn-primary' : 'btn-ghost'}`}
              >
                {a.name}
              </button>
            ))}
          </div>
        </div>

        <div>
          <label className="text-xs text-slate-400">سرپرست تیم (Lead)</label>
          <select className="input" value={leadId} onChange={(e) => setLeadId(e.target.value)}>
            {agents.filter((a) => selected.includes(a.id) || selected.length === 0).map((a) => (
              <option key={a.id} value={a.id}>{a.name}</option>
            ))}
          </select>
        </div>

        <button className="btn btn-primary">ساخت تیم جدید</button>
        {msg && <p className="text-cyan-300 text-sm">{msg}</p>}
        {err && <p className="text-red-400 text-sm">{err}</p>}
      </form>

      <div className="grid gap-3 sm:grid-cols-2">
        {teams.map((t) => (
          <div key={t.id} className="card">
            <div className="flex justify-between gap-2">
              <div>
                <div className="font-semibold">{t.name}</div>
                <div className="text-xs text-slate-500 mt-1">{t.slug}</div>
              </div>
              <span className="text-xs text-slate-400">{t.is_system ? 'سیستمی' : 'سازمانی'}</span>
            </div>
            {t.description && <p className="text-sm text-slate-400 mt-2">{t.description}</p>}
            <div className="flex flex-wrap gap-1 mt-3">
              {(t.agents || []).map((a) => (
                <span key={a.id} className="text-[10px] px-2 py-0.5 rounded-full bg-white/5 text-cyan-300/80">
                  {a.name}
                </span>
              ))}
            </div>
            {t.lead_agent && (
              <p className="text-xs text-slate-500 mt-2">سرپرست: {t.lead_agent.name}</p>
            )}
            {!t.is_system && (
              <button type="button" className="btn btn-ghost text-xs text-red-300 mt-3" onClick={() => remove(t.id)}>
                حذف
              </button>
            )}
          </div>
        ))}
        {!teams.length && <p className="text-slate-500 text-sm">تیمی نیست — از فرم بالا بسازید</p>}
      </div>
    </div>
  )
}
