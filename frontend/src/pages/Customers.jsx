import { useEffect, useState } from 'react'
import { customerApi } from '../services/api'

export default function Customers() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [form, setForm] = useState({ first_name: '', last_name: '', email: '', phone: '' })
  const [error, setError] = useState('')

  const load = () => {
    setLoading(true)
    customerApi
      .list()
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const exportCsv = async () => {
    try {
      const res = await customerApi.exportCsv()
      const url = window.URL.createObjectURL(new Blob([res.data]))
      const a = document.createElement('a')
      a.href = url
      a.download = 'customers.csv'
      a.click()
      window.URL.revokeObjectURL(url)
    } catch {
      setError('خروجی CSV ناموفق')
    }
  }

  const create = async (e) => {
    e.preventDefault()
    setError('')
    try {
      await customerApi.create(form)
      setForm({ first_name: '', last_name: '', email: '', phone: '' })
      load()
    } catch (err) {
      setError(err.response?.data?.message || 'خطا در ایجاد')
    }
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6 gap-3">
        <h1 className="text-2xl font-bold">مشتریان</h1>
        <button type="button" className="btn btn-ghost text-xs" onClick={exportCsv}>خروجی CSV</button>
      </div>

      <form onSubmit={create} className="card mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <input className="input" placeholder="نام" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required />
        <input className="input" placeholder="نام خانوادگی" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} />
        <input className="input" placeholder="ایمیل" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
        <input className="input" placeholder="تلفن" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
        <button className="btn btn-primary">افزودن</button>
        {error && <p className="text-red-400 text-sm sm:col-span-full">{error}</p>}
      </form>

      {loading ? (
        <p className="text-slate-500">در حال بارگذاری...</p>
      ) : (
        <div className="card overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-slate-400 border-b border-white/10">
              <tr>
                <th className="text-right py-2 px-2">نام</th>
                <th className="text-right py-2 px-2">ایمیل</th>
                <th className="text-right py-2 px-2">تلفن</th>
                <th className="text-right py-2 px-2">وضعیت</th>
              </tr>
            </thead>
            <tbody>
              {items.map((c) => (
                <tr key={c.id} className="border-b border-white/5">
                  <td className="py-2.5 px-2">{c.first_name} {c.last_name}</td>
                  <td className="py-2.5 px-2 text-slate-400">{c.email || '—'}</td>
                  <td className="py-2.5 px-2 text-slate-400">{c.phone || '—'}</td>
                  <td className="py-2.5 px-2">{c.status}</td>
                </tr>
              ))}
              {!items.length && (
                <tr>
                  <td colSpan={4} className="py-8 text-center text-slate-500">
                    مشتری‌ای نیست
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
