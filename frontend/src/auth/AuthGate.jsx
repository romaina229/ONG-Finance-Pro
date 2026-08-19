import { useEffect, useState } from 'react';
import React from 'react';
import { currentUser, login, ORG_KEY, TOKEN_KEY } from '../api/client';

export default function AuthGate({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [email, setEmail] = useState('admin@financepro.local');
  const [password, setPassword] = useState('Password!123');
  const [error, setError] = useState('');

  useEffect(() => {
    if (!localStorage.getItem(TOKEN_KEY)) { setLoading(false); return; }
    currentUser()
      .then(response => {
        setUser(response.user);
        const organization = response.user?.organizations?.[0];
        if (organization && !localStorage.getItem(ORG_KEY)) localStorage.setItem(ORG_KEY, String(organization.id));
      })
      .catch(() => { localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(ORG_KEY); })
      .finally(() => setLoading(false));
  }, []);

  async function handleSubmit(event) {
    event.preventDefault(); setError(''); setLoading(true);
    try {
      const response = await login(email, password);
      localStorage.setItem(TOKEN_KEY, response.token);
      const organization = response.organizations?.[0];
      if (organization) localStorage.setItem(ORG_KEY, String(organization.id));
      setUser(response.user);
    } catch (err) {
      setError(err.message || 'Connexion impossible. Vérifiez que le backend est démarré.');
    } finally { setLoading(false); }
  }

  if (loading) return <div className="auth-screen"><div className="auth-loading"><div className="brand-mark">F</div><strong>Finance Pro</strong><span>Initialisation de votre espace financier…</span></div></div>;
  if (user) return children;

  return <main className="auth-screen"><form className="auth-card" onSubmit={handleSubmit}>
    <div className="brand-mark">F</div><span className="eyebrow">FINANCE PRO · ONG</span><h1>Bienvenue</h1><p>Connectez-vous à votre espace financier sécurisé.</p>
    <label>Adresse e-mail<input type="email" value={email} onChange={e => setEmail(e.target.value)} required autoComplete="email" /></label>
    <label>Mot de passe<input type="password" value={password} onChange={e => setPassword(e.target.value)} required autoComplete="current-password" /></label>
    {error && <p role="alert" className="auth-error">{error}</p>}
    <button className="primary auth-submit" type="submit" disabled={loading}>{loading ? 'Connexion…' : 'Se connecter'}</button>
    <small className="auth-hint">Compte de démonstration : admin@financepro.local</small>
  </form></main>;
}
