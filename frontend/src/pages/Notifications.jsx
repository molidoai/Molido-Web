import { useEffect, useState } from 'react'
import { notificationApi } from '../services/api'

export default function Notifications() {
  const [items, setItems] = useState([])
  const [unread, setUnread] = useState(0)

  const load = () => {
    notificationApi.list()
      .then((r) => {
        setItems(r.data.notifications || [])
        setUnread(r.data.unread || 0)
      })
      .catch(() => setItems([]))
  }

  useEffect(() => { load() }, [])

  const read = async (id) => {
    await notificationApi.read(id)
    load()
  }

  const readAll = async () => {
    await notificationApi.readAll()
    load()
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6 gap-3">
        <div>
          <h1 className="text-2xl font-bold">اعلان‌ها</h1>
          <p className="text-slate-400 text-sm">{unread} خوانده‌نشده</p>
        </div>
        <button className="btn btn-ghost text-xs" onClick={readAll}>همه را خواندم</button>
      </div>
      <div className="space-y-2">
        {items.map((n) => (
          <div
            key={n.id}
            className={`card cursor-pointer ${!n.read_at ? 'border-cyan-500/30' : ''}`}
            onClick={() => !n.read_at && read(n.id)}
          >
            <div className="flex justify-between gap-2">
              <div className="font-medium text-sm">{n.title}</div>
              <span className="text-[10px] text-slate-500">{n.type}</span>
            </div>
            {n.body && <p className="text-xs text-slate-400 mt-1">{n.body}</p>}
            <div className="text-[10px] text-slate-600 mt-2">
              {n.created_at ? new Date(n.created_at).toLocaleString('fa-IR') : ''}
              {!n.read_at && ' · جدید'}
            </div>
          </div>
        ))}
        {!items.length && <p className="text-slate-500 text-center py-8">اعلانی نیست</p>}
      </div>
    </div>
  )
}
