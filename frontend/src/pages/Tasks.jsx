import { useEffect, useState } from 'react'
import { taskApi } from '../services/api'

const statusColor = {
  pending: 'text-amber-400',
  working: 'text-sky-400',
  waiting_approval: 'text-purple-400',
  completed: 'text-emerald-400',
  failed: 'text-red-400',
  cancelled: 'text-slate-500',
}

export default function Tasks() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [form, setForm] = useState({ title: '', description: '', priority: 'medium' })
  const [error, setError] = useState('')
  const [filter, setFilter] = useState('')

  const load = () => {
    setLoading(true)
    taskApi
      .list(filter ? { status: filter } : {})
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [filter])

  const create = async (e) => {
    e.preventDefault()
    setError('')
    try {
      await taskApi.create(form)
      setForm({ title: '', description: '', priority: 'medium' })
      load()
    } catch (err) {
      setError(err.response?.data?.message || 'خطا در ایجاد تسک')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">تسک‌های AI</h1>

      <form onSubmit={create} className="card mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input
          className="input lg:col-span-1"
          placeholder="عنوان تسک"
          value={form.title}
          onChange={(e) => setForm({ ...form, title: e.target.value })}
          required
        />
        <input
          className="input lg:col-span-1"
          placeholder="توضیح (اختیاری)"
          value={form.description}
          onChange={(e) => setForm({ ...form, description: e.target.value })}
        />
        <select
          className="input"
          value={form.priority}
          onChange={(e) => setForm({ ...form, priority: e.target.value })}
        >
          <option value="low">کم</option>
          <option value="medium">متوسط</option>
          <option value="high">بالا</option>
          <option value="critical">بحرانی</option>
        </select>
        <button className="btn btn-primary">ایجاد تسک</button>
        {error && <p className="text-red-400 text-sm sm:col-span-full">{error}</p>}
      </form>

      <div className="flex gap-2 mb-4 flex-wrap">
        {['', 'pending', 'working', 'waiting_approval', 'completed', 'failed'].map((s) => (
          <button
            key={s || 'all'}
            onClick={() => setFilter(s)}
            className={`btn text-xs py-1.5 px-3 ${filter === s ? 'btn-primary' : 'btn-ghost'}`}
          >
            {s || 'همه'}
          </button>
        ))}
      </div>

      {loading ? (
        <p className="text-slate-500">در حال بارگذاری...</p>
      ) : (
        <div className="space-y-3">
          {items.map((t) => (
            <div key={t.id} className="card flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <div className="font-medium">{t.title}</div>
                {t.description && <p className="text-sm text-slate-400 mt-1">{t.description}</p>}
                <div className="flex gap-3 mt-2 text-xs text-slate-500">
                  <span>اولویت: {t.priority}</span>
                  {t.agent?.name && <span>ایجنت: {t.agent.name}</span>}
                </div>
              </div>
              <span className={`text-sm font-medium ${statusColor[t.status] || 'text-slate-400'}`}>
                {t.status}
              </span>
            </div>
          ))}
          {!items.length && (
            <p className="text-center text-slate-500 py-8">تسکی وجود ندارد</p>
          )}
        </div>
      )}
    </div>
  )
}
