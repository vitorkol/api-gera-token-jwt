import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Register() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' })
  const [error, setError] = useState('')
  const [fieldErrors, setFieldErrors] = useState({})
  const [submitting, setSubmitting] = useState(false)

  function set(field, value) {
    setForm((f) => ({ ...f, [field]: value }))
  }

  async function handleSubmit(e) {
    e.preventDefault()
    setError('')
    setFieldErrors({})
    setSubmitting(true)
    try {
      await register(form)
      navigate('/', { replace: true })
    } catch (err) {
      setError(err.message || 'Falha no cadastro')
      if (err.errors) setFieldErrors(err.errors)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="page-center">
      <form className="card login-card" onSubmit={handleSubmit}>
        <h1>📝 Cadastro</h1>
        <p className="muted">Novos usuários recebem a role <code>user</code>.</p>

        <label htmlFor="name">Nome</label>
        <input id="name" required value={form.name} onChange={(e) => set('name', e.target.value)} />
        {fieldErrors.name && <div className="alert-error">{fieldErrors.name[0]}</div>}

        <label htmlFor="email">E-mail</label>
        <input id="email" type="email" required value={form.email} onChange={(e) => set('email', e.target.value)} />
        {fieldErrors.email && <div className="alert-error">{fieldErrors.email[0]}</div>}

        <label htmlFor="password">Senha (mín. 8)</label>
        <input id="password" type="password" required value={form.password} onChange={(e) => set('password', e.target.value)} />
        {fieldErrors.password && <div className="alert-error">{fieldErrors.password[0]}</div>}

        <label htmlFor="password_confirmation">Confirmar senha</label>
        <input id="password_confirmation" type="password" required value={form.password_confirmation} onChange={(e) => set('password_confirmation', e.target.value)} />

        {error && <div className="alert-error">{error}</div>}

        <button type="submit" disabled={submitting}>
          {submitting ? 'Criando…' : 'Criar conta'}
        </button>

        <p className="muted small">
          Já tem conta? <Link to="/login">Entrar</Link>
        </p>
      </form>
    </div>
  )
}
