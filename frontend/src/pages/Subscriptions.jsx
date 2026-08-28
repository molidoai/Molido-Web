import { useEffect, useState } from 'react'
import { subscriptionApi } from '../services/api'

export default function Subscriptions() {
  const [plans, setPlans] = useState([])
  const [subs, setSubs] = useState([])
  const [msg, setMsg] = useState('')

  const load = () => {
    subscriptionApi.plans().then((res) => setPlans(res.data.plans || [])).catch(() => {})
    subscriptionApi.list().then((res) => setSubs(res.data.subscriptions || [])).catch(() => {})
  }

  useEffect(() => {
    load()
  }, [])

  const subscribe = async (planSlug) => {
    setMsg('')
    try {
      const res = await subscriptionApi.subscribe({ plan_slug: planSlug })
      setMsg(res.data.message || 'اشتراک فعال شد')
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا')
    }
  }

  const cancel = async (id) => {
    setMsg('')
    try {
      const res = await subscriptionApi.cancel(id, { immediately: false })
      setMsg(res.data.message || 'لغو شد')
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">اشتراک‌ها</h1>
      {msg && <p className="text-cyan-300 text-sm mb-4">{msg}</p>}

      <h2 className="text-lg font-semibold mb-3 text-slate-300">پلن‌ها</h2>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-10">
        {plans.map((p) => (
          <div key={p.id} className="card flex flex-col">
            <h3 className="font-semibold">{p.name}</h3>
            <p className="text-sm text-slate-400 mt-1 flex-1">{p.description}</p>
            <div className="mt-3 flex items-center justify-between">
              <span className="text-sm">
                {Number(p.price).toLocaleString()} {p.currency}
                <span className="text-slate-500"> / {p.interval}</span>
              </span>
              <button className="btn btn-primary text-xs py-1.5 px-3" onClick={() => subscribe(p.slug)}>
                اشتراک
              </button>
            </div>
            {p.trial_days > 0 && (
              <p className="text-xs text-amber-400/80 mt-2">{p.trial_days} روز تریال</p>
            )}
          </div>
        ))}
        {!plans.length && <p className="text-slate-500 text-sm">پلنی تعریف نشده</p>}
      </div>

      <h2 className="text-lg font-semibold mb-3 text-slate-300">اشتراک‌های من</h2>
      <div className="space-y-3">
        {subs.map((s) => (
          <div key={s.id} className="card flex flex-wrap items-center justify-between gap-3">
            <div>
              <div className="font-medium">{s.plan?.name || 'پلن'}</div>
              <div className="text-xs text-slate-500 mt-1">
                وضعیت: {s.status}
                {s.trial_ends_at && ` · تریال تا ${new Date(s.trial_ends_at).toLocaleDateString('fa-IR')}`}
              </div>
            </div>
            {['active', 'trialing'].includes(s.status) && (
              <button className="btn btn-ghost text-xs text-red-300" onClick={() => cancel(s.id)}>
                لغو
              </button>
            )}
          </div>
        ))}
        {!subs.length && <p className="text-slate-500 text-sm py-4">اشتراکی فعال نیست</p>}
      </div>
    </div>
  )
}
