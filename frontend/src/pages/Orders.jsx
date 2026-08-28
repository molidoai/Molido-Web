import { useEffect, useState } from 'react'
import { orderApi, productApi } from '../services/api'

export default function Orders() {
  const [items, setItems] = useState([])
  const [products, setProducts] = useState([])
  const [productId, setProductId] = useState('')
  const [qty, setQty] = useState(1)
  const [msg, setMsg] = useState('')
  const [err, setErr] = useState('')

  const load = () => {
    orderApi.list().then((r) => setItems(r.data.data || r.data || [])).catch(() => setItems([]))
    productApi.list().then((r) => {
      const list = r.data.data || r.data || []
      setProducts(list)
      if (list[0]) setProductId(String(list[0].id))
    }).catch(() => {})
  }
  useEffect(() => { load() }, [])

  const create = async (e) => {
    e.preventDefault()
    setErr('')
    setMsg('')
    const p = products.find((x) => String(x.id) === String(productId))
    if (!p) {
      setErr('محصول انتخاب کنید')
      return
    }
    try {
      await orderApi.create({
        type: 'sale',
        items: [
          {
            product_id: p.id,
            name: p.name,
            quantity: Number(qty),
            unit_price: Number(p.price),
          },
        ],
      })
      setMsg('سفارش ثبت شد')
      load()
    } catch (ex) {
      setErr(ex.response?.data?.message || 'خطا در ثبت سفارش')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">ERP — سفارش‌ها</h1>
      <p className="text-slate-400 text-sm mb-6">سفارش فروش از روی محصولات</p>

      <form onSubmit={create} className="card mb-6 flex flex-wrap gap-3 items-end">
        <div className="min-w-[180px] flex-1">
          <label className="text-xs text-slate-400">محصول</label>
          <select className="input" value={productId} onChange={(e) => setProductId(e.target.value)}>
            {products.map((p) => (
              <option key={p.id} value={p.id}>{p.name} — {Number(p.price).toLocaleString()}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="text-xs text-slate-400">تعداد</label>
          <input className="input w-24" type="number" min="1" value={qty} onChange={(e) => setQty(e.target.value)} />
        </div>
        <button className="btn btn-primary">ثبت سفارش</button>
      </form>
      {msg && <p className="text-cyan-300 text-sm mb-2">{msg}</p>}
      {err && <p className="text-red-400 text-sm mb-2">{err}</p>}

      <div className="space-y-3">
        {items.map((o) => (
          <div key={o.id} className="card">
            <div className="flex justify-between gap-2">
              <span className="font-mono text-xs text-slate-400">{o.number || o.id}</span>
              <span className="text-sm">{o.status}</span>
            </div>
            <div className="text-sm mt-1">{Number(o.total || o.subtotal || 0).toLocaleString()} {o.currency || 'IRR'}</div>
            {o.items?.length > 0 && (
              <ul className="text-xs text-slate-500 mt-2">
                {o.items.map((it) => (
                  <li key={it.id}>{it.name} × {it.quantity}</li>
                ))}
              </ul>
            )}
          </div>
        ))}
        {!items.length && <p className="text-slate-500 text-center py-8">سفارشی نیست</p>}
      </div>
    </div>
  )
}
