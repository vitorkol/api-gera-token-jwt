import { Navigate, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function ProtectedRoute({ children, roles = null }) {
  const { isAuthenticated, loading, user } = useAuth()
  const location = useLocation()

  if (loading) {
    return <div className="page-center muted">Carregando sessão…</div>
  }
  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />
  }
  // Validação de rule no cliente (a definitiva é no backend: 403)
  if (roles && !roles.includes(user?.role)) {
    return (
      <div className="page-center">
        <div className="card error">
          <h2>403 — Acesso negado</h2>
          <p className="muted">
            Esta área requer a role: <code>{roles.join(', ')}</code>. Sua role:{' '}
            <code>{user?.role}</code>
          </p>
        </div>
      </div>
    )
  }
  return children
}
