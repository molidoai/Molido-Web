import { useAuth } from '../context/AuthContext'
import { Link } from 'react-router-dom'

const cards = [
  { title: 'مشتریان', desc: 'مدیریت هویت مرکزی مشتری', to: '/customers', color: 'from-cyan-500/20' },
  { title: 'سرنخ‌ها', desc: 'CRM و پیگیری فروش', to: '/leads', color: 'from-purple-500/20' },
  { title: 'محصولات', desc: 'ERP و موجودی', to: '/products', color: 'from-emerald-500/20' },
  { title: 'چت AI', desc: 'گفتگو با ایجنت‌های مجازی', to: '/chat', color: 'from-sky-500/20' },
  { title: 'تسک‌ها', desc: 'صف کار AI و پیگیری', to: '/tasks', color: 'from-indigo-500/20' },
  { title: 'تأییدها', desc: 'تأیید انسانی اقدامات حساس', to: '/approvals', color: 'from-rose-500/20' },
  { title: 'ماژول‌ها', desc: 'مارکت‌پلیس', to: '/modules', color: 'from-amber-500/20' },
  { title: 'پرداخت', desc: 'تراکنش‌ها و درگاه', to: '/payments', color: 'from-lime-500/20' },
  { title: 'اشتراک', desc: 'پلن‌ها و تریال', to: '/subscriptions', color: 'from-fuchsia-500/20' },
]

export default function Dashboard() {
  const { user } = useAuth()

  return (
    <div>
      <h1 className="text-2xl font-bold mb-1">سلام، {user?.name}</h1>
      <p className="text-slate-400 mb-8 text-sm">
        سازمان: {user?.organization?.name || '—'} · نقش: {user?.role?.display_name || user?.role?.name || '—'}
      </p>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {cards.map((c) => (
          <Link
            key={c.to}
            to={c.to}
            className={`card bg-gradient-to-br ${c.color} to-transparent hover:border-cyan-500/30 transition`}
          >
            <h2 className="font-semibold text-lg mb-1">{c.title}</h2>
            <p className="text-sm text-slate-400">{c.desc}</p>
          </Link>
        ))}
      </div>
    </div>
  )
}
