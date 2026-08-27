import { useEffect, useState } from 'react'
import { productApi } from '../services/api'

export default function Products() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    productApi
      .list()
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }, [])

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">محصولات (ERP)</h1>
      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
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
                <tr>
                  <td colSpan={4} className="py-8 text-center text-slate-500">
                    محصولی نیست
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
