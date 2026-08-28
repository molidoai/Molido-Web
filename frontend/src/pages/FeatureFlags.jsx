import { useEffect, useState } from 'react'
import { featureFlagApi } from '../services/api'

export default function FeatureFlags() {
  const [flags, setFlags] = useState([])
  const [loading, setLoading] = useState(true)
  const [msg, setMsg] = useState('')

  const load = () => {
    setLoading(true)
    featureFlagApi
      .list()
      .then((res) => setFlags(res.data.flags || []))
      .catch(() => setFlags([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const toggle = async (key, enabled) => {
    setMsg('')
    try {
      await featureFlagApi.update(key, { enabled: !enabled })
      setMsg(`فلگ ${key} به‌روز شد`)
      load()
    } catch (err) {
      setMsg(err.response?.data?.message || 'خطا — فقط ادمین می‌تواند تغییر دهد')
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">Feature Flags</h1>
      <p className="text-slate-400 text-sm mb-6">فعال/غیرفعال کردن قابلیت‌های سیستم</p>
      {msg && <p className="text-cyan-300 text-sm mb-4">{msg}</p>}

      {loading ? (
        <p className="text-slate-500">...</p>
      ) : (
        <div className="space-y-2">
          {flags.map((f) => (
            <div key={f.key} className="card flex items-center justify-between gap-4">
              <div>
                <div className="font-mono text-sm text-cyan-300">{f.key}</div>
                <div className="text-xs text-slate-500 mt-1">
                  {f.enabled ? 'فعال' : 'غیرفعال'}
                </div>
              </div>
              <button
                type="button"
                onClick={() => toggle(f.key, f.enabled)}
                className={`relative h-7 w-12 rounded-full transition ${
                  f.enabled ? 'bg-cyan-500' : 'bg-slate-600'
                }`}
              >
                <span
                  className={`absolute top-0.5 h-6 w-6 rounded-full bg-white transition ${
                    f.enabled ? 'right-0.5' : 'right-5'
                  }`}
                />
              </button>
            </div>
          ))}
          {!flags.length && (
            <p className="text-slate-500 text-sm">فلگی نیست — php artisan db:seed را اجرا کنید</p>
          )}
        </div>
      )}
    </div>
  )
}
