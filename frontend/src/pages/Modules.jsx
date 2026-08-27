import { useEffect, useState } from 'react'
import { moduleApi } from '../services/api'

export default function Modules() {
  const [modules, setModules] = useState([])
  const [msg, setMsg] = useState('')

  const load = () => {
    moduleApi.list().then((res) => setModules(res.data.modules || [])).catch(() => setModules([]))
  }

  useEffect(() => {
    load()
  }, [])

  const activate = async (slug) => {
    setMsg('')
    try {
      const res = await moduleApi.activate(slug)
      setMsg(res.data.message || 'فعال شد')
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">مارکت‌پلیس ماژول</h1>
      {msg && <p className="text-cyan-300 text-sm mb-4">{msg}</p>}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {modules.map((m) => (
          <div key={m.id} className="card flex flex-col">
            <div className="flex justify-between items-start mb-2">
              <h2 className="font-semibold">{m.name}</h2>
              <span className="text-xs text-slate-500">{m.version}</span>
            </div>
            <p className="text-sm text-slate-400 flex-1 mb-4">{m.description}</p>
            <div className="flex items-center justify-between">
              <span className="text-sm">
                {m.billing_type === 'free' || Number(m.price) === 0
                  ? 'رایگان'
                  : `${Number(m.price).toLocaleString()} ${m.currency}`}
              </span>
              {m.is_entitled ? (
                <span className="text-xs text-emerald-400">فعال</span>
              ) : (
                <button className="btn btn-primary text-xs py-1.5 px-3" onClick={() => activate(m.slug)}>
                  فعال‌سازی
                </button>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
