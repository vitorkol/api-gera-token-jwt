import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../api'
import { useAuth } from '../context/AuthContext'

export default function Home() {
  const { user } = useAuth()
  const [rolePayload, setRolePayload] = useState(null)
  const [error, setError] = useState('')
  const [ping, setPing] = useState(null)

  // GET /api/role — valida o token e devolve a rule
  useEffect(() => {
    api.role()
      .then(setRolePayload)
      .catch((e) => setError(e.message))
  }, [])

  async function callAdminPing() {
    setPing(null)
    try {
      const res = await api.adminPing()
      setPing({ ok: true, message: res.message })
    } catch (e) {
      setPing({ ok: false, status: e.status, message: e.message })
    }
  }

  return (
    <div className="page-center">
      <div className="card">
        <h1>Olá, {user?.name} 👋</h1>
        <p className="muted">Sessão validada por JWT contra a API Laravel.</p>

        <dl className="profile">
          <div><dt>E-mail</dt><dd>{user?.email}</dd></div>
          <div><dt>Role (via /api/me)</dt><dd><code>{user?.role}</code></dd></div>
          <div>
            <dt>Role (via /api/role)</dt>
            <dd>
              {rolePayload ? <code>{rolePayload.role}</code> : <span className="muted">{error || 'carregando…'}</span>}
            </dd>
          </div>
        </dl>

        <hr />

        <h2>Testar validação de rule</h2>
        <p className="muted">
          <code>GET /api/admin/ping</code> exige <code>role:admin</code> (403 para as demais).
        </p>
        <button onClick={callAdminPing}>Chamar endpoint admin</button>

        {ping && (
          <div className={ping.ok ? 'alert-success' : 'alert-error'}>
            {ping.ok
              ? `✅ 200 — ${ping.message}`
              : `⛔ ${ping.status} — ${ping.message}`}
          </div>
        )}

        {user?.role === 'admin' && (
          <p>
            <Link to="/admin">Ir para a área admin →</Link>
          </p>
        )}
      </div>
    </div>
  )
}
