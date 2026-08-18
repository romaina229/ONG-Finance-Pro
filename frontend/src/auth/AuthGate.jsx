import { useEffect, useState } from 'react';
import { currentUser, login } from '../api/client';

const TOKEN_KEY = 'finance_pro_token';

export default function AuthGate({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    if (!localStorage.getItem(TOKEN_KEY)) {
      setLoading(false);
      return;
    }

    currentUser()
      .then((response) => setUser(response.user))
      .catch(() => localStorage.removeItem(TOKEN_KEY))
      .finally(() => setLoading(false));
  }, []);

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await login(email, password);
      localStorage.setItem(TOKEN_KEY, response.token);
      setUser(response.user);
    } catch (err) {
      setError(err.message || 'Connexion impossible.');
    } finally {
      setLoading(false);
    }
  }

  if (loading) {
    return <div className="auth-screen"><p>Connexion à Finance Pro…</p></div>;
  }

  if (user) return children;

  return (
    <main className="auth-screen">
      <form className="auth-card" onSubmit={handleSubmit}>
        <div className="brand-mark">F</div>
        <span className="eyebrow">FINANCE PRO</span>
        <h1>Connexion</h1>
        <p>Connectez-vous à votre espace financier.</p>

        <label>
          Adresse e-mail
          <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required autoComplete="email" />
        </label>

        <label>
          Mot de passe
          <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required autoComplete="current-password" />
        </label>

        {error && <p role="alert" className="auth-error">{error}</p>}

        <button className="primary" type="submit" disabled={loading}>
          {loading ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>
    </main>
  );
}
