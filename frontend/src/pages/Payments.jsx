import { useEffect, useState } from 'react'
import { paymentApi, moduleApi } from '../services/api'

export default function Payments() {
  const [items, setItems] = useState([])
  const [modules, setModules] = useState([])
  const [loading, setLoading] = useState(true)
  const [slug, setSlug] = useState('')
  const [msg, setMsg] = useState('')

  const load = () => {
    setLoading(true)
    paymentApi
      .list()
      .then((res) => setItems(res.data.data || res.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
    moduleApi.list().then((res) => {
      const paid = (res.data.modules || []).filter(
        (m) => m.billing_type !== 'free' && Number(m.price) > 0
      )
      setModules(paid)
      if (paid[0]) setSlug(paid[0].slug)
    }).catch(() => {})
  }, [])

  const initiate = async (e) => {
    e.preventDefault()
    setMsg('')
    if (!slug) return
    try {
      const res = await paymentApi.initiate({ module_slug: slug })
      setMsg(res.data.message || 'تراکنش ایجاد شد')
      if (res.data.redirect_url) {
        window.open(res.data.redirect_url, '_blank')
      }
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا در پرداخت')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">پرداخت‌ها</h1>

      <form onSubmit={initiate} className="card mb-6 flex flex-wrap gap-3 items-end">
        <div className="flex-1 min-w-[180px]">
          <label className="text-xs text-slate-400 mb-1 block">ماژول</label>
          <select className="input" value={slug} onChange={(e) => setSlug(e.target.value)}>
            {modules.map((m) => (
              <option key={m.slug} value={m.slug}>
                {m.name} — {Number(m.price).toLocaleString()} {m.currency}
              </option>
            ))}
            {!modules.length && <option value="">ماژول پولی نیست</option>}
          </select>
        </div>
        <button type="submit" className="btn btn-primary" disabled={!slug}>
          شروع پرداخت (Mock)
        </button>
      </form>

      {msg && <p className="text-cyan-300 text-sm mb-4">{msg}</p>}

      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
        <div className="card overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-slate-400 border-b border-white/10">
              <tr>
                <th className="text-right py-2 px-2">شناسه</th>
                <th className="text-right py-2 px-2">مبلغ</th>
                <th className="text-right py-2 px-2">وضعیت</th>
                <th className="text-right py-2 px-2">درگاه</th>
              </tr>
            </thead>
            <tbody>
              {items.map((t) => (
                <tr key={t.id} className="border-b border-white/5">
                  <td className="py-2.5 px-2 font-mono text-xs">{t.uuid?.slice(0, 8)}…</td>
                  <td className="py-2.5 px-2">
                    {Number(t.amount).toLocaleString()} {t.currency}
                  </td>
                  <td className="py-2.5 px-2">{t.status}</td>
                  <td className="py-2.5 px-2 text-slate-400">{t.provider}</td>
                </tr>
              ))}
              {!items.length && (
                <tr>
                  <td colSpan={4} className="py-8 text-center text-slate-500">
                    تراکنشی نیست
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
