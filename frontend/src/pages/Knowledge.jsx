import { useEffect, useState } from 'react'
import { knowledgeApi } from '../services/api'

export default function Knowledge() {
  const [items, setItems] = useState([])
  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')
  const [category, setCategory] = useState('general')
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')
  const [q, setQ] = useState('')

  const load = () => {
    knowledgeApi.list({ published_only: false }).then((r) => setItems(r.data.data || r.data || [])).catch(() => setItems([]))
  }
  useEffect(() => { load() }, [])

  const create = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    try {
      await knowledgeApi.create({ title, content, category, type: 'article', is_published: true })
      setMsg('مقاله ثبت شد')
      setTitle('')
      setContent('')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا')
    }
  }

  const search = async () => {
    if (!q.trim()) return load()
    try {
      const r = await knowledgeApi.search(q)
      setItems(r.data.data || r.data.articles || r.data || [])
    } catch {
      setErr('جستجو ناموفق')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">دانش (RAG سبک)</h1>
      <p className="text-slate-400 text-sm mb-6">مقالات سازمانی برای پاسخ AI — بدون Vector DB جدا</p>

      <div className="flex gap-2 mb-4">
        <input className="input flex-1" placeholder="جستجو..." value={q} onChange={(e) => setQ(e.target.value)} />
        <button type="button" className="btn btn-ghost" onClick={search}>جستجو</button>
      </div>

      <form onSubmit={create} className="card mb-6 space-y-3">
        <input className="input" placeholder="عنوان" value={title} onChange={(e) => setTitle(e.target.value)} required />
        <input className="input" placeholder="دسته" value={category} onChange={(e) => setCategory(e.target.value)} />
        <textarea className="input min-h-[120px]" placeholder="متن دانش" value={content} onChange={(e) => setContent(e.target.value)} required />
        <button className="btn btn-primary">ثبت مقاله</button>
        {msg && <p className="text-cyan-300 text-sm">{msg}</p>}
        {err && <p className="text-red-400 text-sm">{err}</p>}
      </form>

      <div className="space-y-2">
        {items.map((a) => (
          <div key={a.id} className="card">
            <div className="font-medium">{a.title}</div>
            <div className="text-xs text-slate-500">{a.category} · {a.type}</div>
            {a.summary && <p className="text-sm text-slate-400 mt-1">{a.summary}</p>}
          </div>
        ))}
        {!items.length && <p className="text-slate-500 text-sm">مقاله‌ای نیست</p>}
      </div>
    </div>
  )
}
