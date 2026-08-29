import { useEffect, useState, useRef } from 'react'
import { aiApi } from '../services/api'

export default function Chat() {
  const [conversationId, setConversationId] = useState(null)
  const [messages, setMessages] = useState([])
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [agents, setAgents] = useState([])
  const [teams, setTeams] = useState([])
  const [agent, setAgent] = useState('')
  const [team, setTeam] = useState('')
  const bottomRef = useRef(null)

  useEffect(() => {
    aiApi.agents().then((res) => setAgents(res.data.agents || [])).catch(() => {})
    aiApi.listTeams().then((res) => setTeams(res.data.teams || [])).catch(() => {})
    aiApi
      .createConversation({ title: 'گفتگوی جدید' })
      .then((res) => setConversationId(res.data.conversation.id))
      .catch(() => {})
  }, [])

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages])

  const send = async (e) => {
    e.preventDefault()
    if (!input.trim() || !conversationId || loading) return
    const text = input.trim()
    setInput('')
    setMessages((m) => [...m, { role: 'user', content: text }])
    setLoading(true)
    try {
      const payload = { message: text }
      if (team) payload.team = team
      else if (agent) payload.agent = agent
      const res = await aiApi.send(conversationId, payload)
      const reply = res.data.message?.content || res.data.reply || res.data.content || '—'
      setMessages((m) => [...m, { role: 'assistant', content: reply, meta: res.data.agent || team }])
    } catch (err) {
      setMessages((m) => [
        ...m,
        { role: 'assistant', content: err.response?.data?.message || 'خطا در پاسخ AI' },
      ])
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="flex flex-col h-[calc(100vh-8rem)]">
      <div className="flex flex-wrap gap-2 mb-4 items-center">
        <h1 className="text-xl font-bold ml-auto">چت AI</h1>
        <select
          className="input text-sm py-1.5 w-auto min-w-[140px]"
          value={team}
          onChange={(e) => {
            setTeam(e.target.value)
            if (e.target.value) setAgent('')
          }}
        >
          <option value="">تیم AI (اختیاری)</option>
          {teams.map((t) => (
            <option key={t.id} value={t.slug}>{t.name}</option>
          ))}
        </select>
        <select
          className="input text-sm py-1.5 w-auto min-w-[140px]"
          value={agent}
          disabled={!!team}
          onChange={(e) => setAgent(e.target.value)}
        >
          <option value="">ایجنت تکی</option>
          {agents.map((a) => (
            <option key={a.id} value={a.slug}>{a.name}</option>
          ))}
        </select>
      </div>

      <div className="card flex-1 overflow-y-auto space-y-3 mb-3">
        {messages.map((m, i) => (
          <div
            key={i}
            className={`text-sm p-3 rounded-xl max-w-[90%] ${
              m.role === 'user'
                ? 'bg-cyan-500/20 mr-auto'
                : 'bg-white/5 ml-auto'
            }`}
          >
            {m.content}
          </div>
        ))}
        {loading && <p className="text-slate-500 text-sm">در حال فکر کردن...</p>}
        <div ref={bottomRef} />
      </div>

      <form onSubmit={send} className="flex gap-2">
        <input
          className="input flex-1"
          placeholder="پیام شما..."
          value={input}
          onChange={(e) => setInput(e.target.value)}
        />
        <button className="btn btn-primary" disabled={loading || !conversationId}>
          ارسال
        </button>
      </form>
    </div>
  )
}
