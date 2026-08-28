import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

const links = [
  { to: '/', label: 'داشبورد', end: true },
  { to: '/customers', label: 'مشتریان' },
  { to: '/leads', label: 'سرنخ‌ها' },
  { to: '/deals', label: 'معاملات' },
  { to: '/products', label: 'محصولات' },
  { to: '/orders', label: 'سفارش‌ها' },
  { to: '/chat', label: 'چت AI' },
  { to: '/workforce', label: 'کارمندان AI' },
  { to: '/tasks', label: 'تسک‌ها' },
  { to: '/approvals', label: 'تأییدها' },
  { to: '/modules', label: 'ماژول‌ها' },
  { to: '/payments', label: 'پرداخت' },
  { to: '/subscriptions', label: 'اشتراک' },
  { to: '/feature-flags', label: 'فلگ‌ها' },
  { to: '/audit', label: 'Audit' },
  { to: '/settings', label: 'تنظیمات' },
]

export default function AppLayout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <div className="min-h-screen flex bg-slate-950">
      <aside className="w-60 shrink-0 border-l border-white/10 bg-slate-900/80 p-4 flex flex-col">
        <div className="mb-8 px-2">
          <div className="text-xl font-bold bg-gradient-to-l from-cyan-400 to-purple-400 bg-clip-text text-transparent tracking-wider">
            MOLIDO
          </div>
          <div className="text-xs text-slate-500 mt-1">Command Center</div>
        </div>

        <nav className="flex-1 space-y-1">
          {links.map((l) => (
            <NavLink
              key={l.to}
              to={l.to}
              end={l.end}
              className={({ isActive }) =>
                `block rounded-xl px-3 py-2.5 text-sm transition ${
                  isActive
                    ? 'bg-cyan-500/15 text-cyan-300'
                    : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'
                }`
              }
            >
              {l.label}
            </NavLink>
          ))}
        </nav>

        <div className="border-t border-white/10 pt-4 mt-4">
          <div className="px-2 text-sm text-slate-300 truncate">{user?.name}</div>
          <div className="px-2 text-xs text-slate-500 truncate mb-3">{user?.email}</div>
          <button onClick={handleLogout} className="btn btn-ghost w-full text-xs">
            خروج
          </button>
        </div>
      </aside>

      <main className="flex-1 overflow-auto p-6 md:p-8">
        <Outlet />
      </main>
    </div>
  )
}
