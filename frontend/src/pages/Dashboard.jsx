import { useEffect, useState } from 'react'
import { useAuth } from '../context/AuthContext'
import { Link } from 'react-router-dom'
import { dashboardApi } from '../services/api'

const cards = [
  { title: 'مشتریان', desc: 'هویت مرکزی', to: '/customers', color: 'from-cyan-500/20', key: 'customers' },
  { title: 'سرنخ‌ها', desc: 'CRM', to: '/leads', color: 'from-purple-500/20', key: 'leads' },
  { title: 'محصولات', desc: 'ERP', to: '/products', color: 'from-emerald-500/20', key: 'products' },
  { title: 'چت AI', desc: 'ایجنت‌ها', to: '/chat', color: 'from-sky-500/20' },
  { title: 'تسک‌ها', desc: 'صف AI', to: '/tasks', color: 'from-indigo-500/20', key: 'tasks' },
  { title: 'تأییدها', desc: 'Human Approval', to: '/approvals', color: 'from-rose-500/20' },
  { title: 'ماژول‌ها', desc: 'مارکت‌پلیس', to: '/modules', color: 'from-amber-500/20' },
  { title: 'پرداخت', desc: 'تراکنش‌ها', to: '/payments', color: 'from-lime-500/20' },
  { title: 'اشتراک', desc: 'پلن‌ها', to: '/subscriptions', color: 'from-fuchsia-500/20', key: 'subscriptions_active' },
  { title: 'Audit', desc: 'لاگ امنیتی', to: '/audit', color: 'from-slate-500/20' },
]

export default function Dashboard() {
  const { user } = useAuth()
  const [stats, setStats] = useState(null)

  useEffect(() => {
    dashboardApi.stats().then((res) => setStats(res.data)).catch(() => {})
  }, [])

  return (
    <div>
      <h1 className="text-2xl font-bold mb-1">سلام، {user?.name}</h1>
      <p className="text-slate-400 mb-6 text-sm">
        سازمان: {user?.organization?.name || '—'} · نقش: {user?.role?.display_name || user?.role?.name || '—'}
      </p>

      {stats && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
          <div className="card py-4">
            <div className="text-2xl font-bold text-cyan-300">{stats.counts?.customers ?? 0}</div>
            <div className="text-xs text-slate-500">مشتری</div>
          </div>
          <div className="card py-4">
            <div className="text-2xl font-bold text-purple-300">{stats.counts?.leads ?? 0}</div>
            <div className="text-xs text-slate-500">سرنخ</div>
          </div>
          <div className="card py-4">
            <div className="text-2xl font-bold text-emerald-300">{stats.counts?.orders ?? 0}</div>
            <div className="text-xs text-slate-500">سفارش</div>
          </div>
          <div className="card py-4">
            <div className="text-2xl font-bold text-amber-300">
              {Number(stats.revenue?.paid_total || 0).toLocaleString()}
            </div>
            <div className="text-xs text-slate-500">درآمد (پرداخت‌شده)</div>
          </div>
        </div>
      )}

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {cards.map((c) => (
          <Link
            key={c.to}
            to={c.to}
            className={`card bg-gradient-to-br ${c.color} to-transparent hover:border-cyan-500/30 transition`}
          >
            <div className="flex justify-between items-start">
              <h2 className="font-semibold text-lg mb-1">{c.title}</h2>
              {c.key && stats?.counts?.[c.key] != null && (
                <span className="text-sm text-slate-400">{stats.counts[c.key]}</span>
              )}
            </div>
            <p className="text-sm text-slate-400">{c.desc}</p>
          </Link>
        ))}
      </div>
    </div>
  )
}
