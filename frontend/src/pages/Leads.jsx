import { useEffect, useState } from 'react'
import { leadApi } from '../services/api'

export default function Leads() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    leadApi
      .list()
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }, [])

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">سرنخ‌ها (CRM)</h1>
      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
        <div className="card overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-slate-400 border-b border-white/10">
              <tr>
                <th className="text-right py-2 px-2">عنوان</th>
                <th className="text-right py-2 px-2">وضعیت</th>
                <th className="text-right py-2 px-2">اولویت</th>
              </tr>
            </thead>
            <tbody>
              {items.map((l) => (
                <tr key={l.id} className="border-b border-white/5">
                  <td className="py-2.5 px-2">{l.title}</td>
                  <td className="py-2.5 px-2">{l.status}</td>
                  <td className="py-2.5 px-2">{l.priority}</td>
                </tr>
              ))}
              {!items.length && (
                <tr>
                  <td colSpan={3} className="py-8 text-center text-slate-500">
                    سرنخی ثبت نشده — از API ایجاد کنید
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
