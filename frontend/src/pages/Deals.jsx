import { useEffect, useState } from 'react'
import { dealApi } from '../services/api'

const stages = ['prospecting', 'qualification', 'proposal', 'negotiation', 'won', 'lost']

export default function Deals() {
  const [items, setItems] = useState([])
  const [form, setForm] = useState({ title: '', amount: '', stage: 'prospecting' })
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  const load = () => {
    dealApi.list().then((r) => setItems(r.data.data || r.data || [])).catch(() => setItems([]))
  }
  useEffect(() => { load() }, [])

  const create = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    try {
      // customer_id required by DB — show error if API rejects
      await dealApi.create({
        title: form.title,
        amount: Number(form.amount || 0),
        stage: form.stage,
        customer_id: form.customer_id ? Number(form.customer_id) : undefined,
      })
      setMsg('معامله ثبت شد')
      setForm({ title: '', amount: '', stage: 'prospecting', customer_id: '' })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'نیاز به customer_id معتبر — اول مشتری بسازید')
    }
  }

  const setStage = async (id, stage) => {
    try {
      await dealApi.update(id, { stage })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">CRM — معاملات (Pipeline)</h1>
      <p className="text-slate-400 text-sm mb-6">مراحل فروش از پیگیری تا برد/باخت</p>

      <form onSubmit={create} className="card mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input className="input" placeholder="عنوان معامله *" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
        <input className="input" type="number" placeholder="مبلغ" value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} />
        <input className="input" type="number" placeholder="شناسه مشتری (customer_id)" value={form.customer_id || ''} onChange={(e) => setForm({ ...form, customer_id: e.target.value })} required />
        <select className="input" value={form.stage} onChange={(e) => setForm({ ...form, stage: e.target.value })}>
          {stages.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <button className="btn btn-primary sm:col-span-2 lg:col-span-4">ثبت معامله</button>
      </form>
      {msg && <p className="text-cyan-300 text-sm mb-2">{msg}</p>}
      {err && <p className="text-red-400 text-sm mb-2">{err}</p>}

      <div className="space-y-3">
        {items.map((d) => (
          <div key={d.id} className="card flex flex-wrap items-center justify-between gap-3">
            <div>
              <div className="font-medium">{d.title}</div>
              <div className="text-xs text-slate-500">{Number(d.amount || 0).toLocaleString()} {d.currency || 'IRR'}</div>
            </div>
            <select className="input text-xs py-1 w-auto" value={d.stage} onChange={(e) => setStage(d.id, e.target.value)}>
              {stages.map((s) => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
        ))}
        {!items.length && <p className="text-slate-500 text-center py-8">معامله‌ای نیست</p>}
      </div>
    </div>
  )
}
