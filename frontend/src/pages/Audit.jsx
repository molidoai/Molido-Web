import { useEffect, useState } from 'react'
import { auditApi } from '../services/api'

export default function Audit() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    auditApi
      .list()
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }, [])

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">لاگ Audit</h1>
      <p className="text-slate-400 text-sm mb-6">رویدادهای امنیتی و عملیاتی سازمان</p>

      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
        <div className="card overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-slate-400 border-b border-white/10">
              <tr>
                <th className="text-right py-2 px-2">زمان</th>
                <th className="text-right py-2 px-2">عملیات</th>
                <th className="text-right py-2 px-2">نتیجه</th>
                <th className="text-right py-2 px-2">IP</th>
              </tr>
            </thead>
            <tbody>
              {items.map((log) => (
                <tr key={log.id} className="border-b border-white/5">
                  <td className="py-2.5 px-2 text-slate-400 text-xs whitespace-nowrap">
                    {log.created_at ? new Date(log.created_at).toLocaleString('fa-IR') : '—'}
                  </td>
                  <td className="py-2.5 px-2 font-mono text-xs text-cyan-300">{log.action}</td>
                  <td className="py-2.5 px-2">{log.result || '—'}</td>
                  <td className="py-2.5 px-2 text-slate-500 text-xs">{log.ip_address || '—'}</td>
                </tr>
              ))}
              {!items.length && (
                <tr>
                  <td colSpan={4} className="py-8 text-center text-slate-500">
                    لاگی ثبت نشده — با ورود مجدد لاگ auth.login ساخته می‌شود
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
