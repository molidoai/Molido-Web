import { useEffect, useState } from 'react'
import { approvalApi } from '../services/api'

export default function Approvals() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [msg, setMsg] = useState('')

  const load = () => {
    setLoading(true)
    approvalApi
      .list({ status: 'pending' })
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const review = async (id, decision) => {
    setMsg('')
    try {
      const res = await approvalApi.review(id, { decision })
      setMsg(res.data.message || (decision === 'approved' ? 'تأیید شد' : 'رد شد'))
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">تأییدهای انسانی</h1>
      <p className="text-slate-400 text-sm mb-6">اقدامات حساس AI که نیاز به تأیید دارند</p>

      {msg && <p className="text-cyan-300 text-sm mb-4">{msg}</p>}

      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
        <div className="space-y-3">
          {items.map((a) => (
            <div key={a.id} className="card">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div className="font-medium">{a.action}</div>
                  {a.reason && <p className="text-sm text-slate-400 mt-1">{a.reason}</p>}
                  <div className="flex gap-3 mt-2 text-xs text-slate-500">
                    <span>ریسک: {a.risk_level}</span>
                    <span>وضعیت: {a.status}</span>
                  </div>
                </div>
                {a.status === 'pending' && (
                  <div className="flex gap-2">
                    <button
                      className="btn btn-primary text-xs py-1.5 px-4"
                      onClick={() => review(a.id, 'approved')}
                    >
                      تأیید
                    </button>
                    <button
                      className="btn btn-ghost text-xs py-1.5 px-4 text-red-300"
                      onClick={() => review(a.id, 'rejected')}
                    >
                      رد
                    </button>
                  </div>
                )}
              </div>
            </div>
          ))}
          {!items.length && (
            <p className="text-center text-slate-500 py-8">درخواست تأییدی در انتظار نیست</p>
          )}
        </div>
      )}
    </div>
  )
}
