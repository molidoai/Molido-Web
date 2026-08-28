import { useEffect, useState } from 'react'
import { productApi } from '../services/api'

export default function Products() {
  const [items, setItems] = useState([])
  const [form, setForm] = useState({ name: '', sku: '', price: '', unit: 'عدد' })
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  const load = () => {
    productApi.list().then((r) => setItems(r.data.data || r.data || [])).catch(() => setItems([]))
  }
  useEffect(() => { load() }, [])

  const create = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    try {
      await productApi.create({
        name: form.name,
        sku: form.sku || null,
        price: Number(form.price || 0),
        unit: form.unit,
        is_active: true,
      })
      setMsg('محصول ثبت شد')
      setForm({ name: '', sku: '', price: '', unit: 'عدد' })
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در ثبت')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">ERP — محصولات</h1>
      <p className="text-slate-400 text-sm mb-6">کاتالوگ کالا و قیمت</p>

      <form onSubmit={create} className="card mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input className="input" placeholder="نام محصول *" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
        <input className="input" placeholder="SKU" value={form.sku} onChange={(e) => setForm({ ...form, sku: e.target.value })} />
        <input className="input" type="number" placeholder="قیمت" value={form.price} onChange={(e) => setForm({ ...form, price: e.target.value })} required />
        <input className="input" placeholder="واحد" value={form.unit} onChange={(e) => setForm({ ...form, unit: e.target.value })} />
        <button className="btn btn-primary sm:col-span-2 lg:col-span-4">افزودن محصول</button>
      </form>
      {msg && <p className="text-cyan-300 text-sm mb-2">{msg}</p>}
      {err && <p className="text-red-400 text-sm mb-2">{err}</p>}

      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead className="text-slate-400 border-b border-white/10">
            <tr>
              <th className="text-right py-2 px-2">نام</th>
              <th className="text-right py-2 px-2">SKU</th>
              <th className="text-right py-2 px-2">قیمت</th>
              <th className="text-right py-2 px-2">وضعیت</th>
            </tr>
          </thead>
          <tbody>
            {items.map((p) => (
              <tr key={p.id} className="border-b border-white/5">
                <td className="py-2.5 px-2">{p.name}</td>
                <td className="py-2.5 px-2 text-slate-400">{p.sku || '—'}</td>
                <td className="py-2.5 px-2">{Number(p.price).toLocaleString()}</td>
                <td className="py-2.5 px-2">{p.is_active ? 'فعال' : 'غیرفعال'}</td>
              </tr>
            ))}
            {!items.length && (
              <tr><td colSpan={4} className="py-8 text-center text-slate-500">محصولی نیست</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
