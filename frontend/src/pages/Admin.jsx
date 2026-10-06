import { useEffect, useState } from 'react'
import { api } from '../api'
import { useAuth } from '../context/AuthContext'

export default function Admin() {
  const { user } = useAuth()
  const [ping, setPing] = useState(null)

  useEffect(() => {
    api.adminPing()
      .then((res) => setPing({ ok: true, message: res.message }))
      .catch((e) => setPing({ ok: false, message: e.message }))
  }, [])

  return (
    <div className="page-center">
      <div className="card">
        <h1>🛡️ Área Admin</h1>
        <p className="muted">
          Restrita à role <code>admin</code> — protegida no backend por
          <code> auth:api</code> + <code>role:admin</code>.
        </p>
        {ping?.ok ? (
          <div className="alert-success">✅ {ping.message}</div>
        ) : (
          <div className="alert-error">⛔ {ping?.message}</div>
        )}
        <p className="muted small">Usuário autenticado: {user?.email}</p>
      </div>
    </div>
  )
}
