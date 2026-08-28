import { useEffect, useState } from 'react'
import { leadApi } from '../services/api'

const statuses = ['new', 'contacted', 'qualified', 'converted', 'lost']

export default function Leads() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [form, setForm] = useState({ title: '', source: '', priority: 'medium', estimated_value: '', notes: '' })
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [filter, setFilter] = useState('')

  const load = () => {
    setLoading(true)
    leadApi
      .list(filter ? { status: filter } : {})
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => { load() }, [filter])

  const create = async (e) => {
    e.preventDefault()
    setMsg('')
    setErr('')
    try {
      await leadApi.create({
        ...form,
        estimated_value: form.estimated_value ? Number(form.estimated_value) : null,
      })
      setForm({ title: '', source: '', priority: 'medium', estimated_value: '', notes: '' })
      setMsg('سرنخ ثبت شد')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  const setStatus = async (id, status) => {
    try {
      await leadApi.update(id, { status })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'به‌روزرسانی نشد')
    }
  }

  const convert = async (id) => {
    try {
      const res = await leadApi.convert(id)
      setMsg(res.data.message || 'تبدیل شد')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'تبدیل ممکن نیست — مشتری متصل باشد')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">CRM — سرنخ‌ها</h1>
      <p className="text-slate-400 text-sm mb-6">مدیریت فرصت‌های فروش</p>

      <form onSubmit={create} className="card mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <input className="input" placeholder="عنوان سرنخ *" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
        <input className="input" placeholder="منبع (سایت، تماس...)" value={form.source} onChange={(e) => setForm({ ...form, source: e.target.value })} />
        <select className="input" value={form.priority} onChange={(e) => setForm({ ...form, priority: e.target.value })}>
          <option value="low">اولویت کم</option>
          <option value="medium">متوسط</option>
          <option value="high">بالا</option>
        </select>
        <input className="input" type="number" placeholder="ارزش تخمینی" value={form.estimated_value} onChange={(e) => setForm({ ...form, estimated_value: e.target.value })} />
        <input className="input lg:col-span-2" placeholder="یادداشت" value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
        <button className="btn btn-primary">ثبت سرنخ</button>
      </form>
      {msg && <p className="text-cyan-300 text-sm mb-3">{msg}</p>}
      {err && <p className="text-red-400 text-sm mb-3">{err}</p>}

      <div className="flex flex-wrap gap-2 mb-4">
        <button className={`btn text-xs ${!filter ? 'btn-primary' : 'btn-ghost'}`} onClick={() => setFilter('')}>همه</button>
        {statuses.map((s) => (
          <button key={s} className={`btn text-xs ${filter === s ? 'btn-primary' : 'btn-ghost'}`} onClick={() => setFilter(s)}>{s}</button>
        ))}
      </div>

      {loading ? <p className="text-slate-500">...</p> : (
        <div className="space-y-3">
          {items.map((l) => (
            <div key={l.id} className="card flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <div className="font-medium">{l.title}</div>
                <div className="text-xs text-slate-500 mt-1">
                  {l.status} · {l.priority}
                  {l.estimated_value != null && ` · ${Number(l.estimated_value).toLocaleString()}`}
                </div>
              </div>
              <div className="flex flex-wrap gap-2">
                {l.status !== 'converted' && l.status !== 'lost' && (
                  <>
                    <select className="input text-xs py-1" value={l.status} onChange={(e) => setStatus(l.id, e.target.value)}>
                      {statuses.filter((s) => s !== 'converted').map((s) => <option key={s} value={s}>{s}</option>)}
                    </select>
                    <button className="btn btn-primary text-xs py-1.5" onClick={() => convert(l.id)}>تبدیل به معامله</button>
                  </>
                )}
              </div>
            </div>
          ))}
          {!items.length && <p className="text-center text-slate-500 py-8">سرنخی نیست</p>}
        </div>
      )}
    </div>
  )
}
