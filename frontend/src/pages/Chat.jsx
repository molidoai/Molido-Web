import { useEffect, useState, useRef } from 'react'
import { aiApi } from '../services/api'

export default function Chat() {
  const [conversationId, setConversationId] = useState(null)
  const [messages, setMessages] = useState([])
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [agents, setAgents] = useState([])
  const [agent, setAgent] = useState('')
  const bottomRef = useRef(null)

  useEffect(() => {
    aiApi.agents().then((res) => setAgents(res.data.agents || [])).catch(() => {})
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
      const res = await aiApi.send(conversationId, { message: text, agent: agent || undefined })
      setMessages((m) => [...m, { role: 'assistant', content: res.data.reply?.content || res.data.message }])
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
    <div className="flex flex-col h-[calc(100vh-4rem)] max-w-3xl">
      <div className="flex items-center justify-between mb-4 gap-3">
        <h1 className="text-2xl font-bold">چت AI</h1>
        <select
          className="input w-auto min-w-[140px]"
          value={agent}
          onChange={(e) => setAgent(e.target.value)}
        >
          <option value="">خودکار</option>
          {agents.map((a) => (
            <option key={a.slug} value={a.slug}>
              {a.name}
            </option>
          ))}
        </select>
      </div>

      <div className="card flex-1 overflow-y-auto mb-4 space-y-3">
        {!messages.length && (
          <p className="text-slate-500 text-sm text-center py-12">پیامی بنویسید تا با MOLIDO AI گفتگو کنید</p>
        )}
        {messages.map((m, i) => (
          <div
            key={i}
            className={`rounded-2xl px-4 py-3 text-sm max-w-[85%] ${
              m.role === 'user'
                ? 'bg-cyan-500/20 mr-auto'
                : 'bg-white/10 ml-auto'
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
          disabled={!conversationId}
        />
        <button className="btn btn-primary" disabled={loading || !conversationId}>
          ارسال
        </button>
      </form>
    </div>
  )
}
