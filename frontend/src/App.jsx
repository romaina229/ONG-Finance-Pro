import { useState } from 'react';

const navigation = [
  ['Tableau de bord', 'dashboard'],
  ['Organisations', 'organizations'],
  ['Projets & budgets', 'projects'],
  ['Dépenses', 'expenses'],
  ['Recettes', 'revenues'],
  ['Rapports', 'reports'],
];

const stats = [
  { label: 'Solde disponible', value: '48 250 000 FCFA', trend: '+8,4 %' },
  { label: 'Dépenses du mois', value: '12 480 000 FCFA', trend: '+3,1 %' },
  { label: 'Recettes du mois', value: '18 760 000 FCFA', trend: '+11,7 %' },
  { label: 'Opérations en attente', value: '24', trend: 'À traiter' },
];

function App() {
  const [active, setActive] = useState('dashboard');
  const [online, setOnline] = useState(false);

  const title = navigation.find(([, id]) => id === active)?.[0] ?? 'Tableau de bord';

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-mark">F</div>
          <div><strong>Finance Pro</strong><span>Gestion financière ONG</span></div>
        </div>

        <div className="workspace">
          <span>Organisation active</span>
          <strong>Mon ONG</strong>
          <small>Exercice 2026 · XOF</small>
        </div>

        <nav aria-label="Navigation principale">
          <small className="nav-label">ESPACE FINANCIER</small>
          {navigation.map(([label, id]) => (
            <button className={active === id ? 'nav-item active' : 'nav-item'} key={id} onClick={() => setActive(id)}>
              <span className="nav-icon">{label.charAt(0)}</span>
              {label}
            </button>
          ))}
        </nav>

        <div className="sidebar-bottom">
          <div className={online ? 'sync-card online' : 'sync-card'}>
            <div><span className="status-dot" />{online ? 'Connecté' : 'Mode hors connexion'}</div>
            <small>{online ? 'Synchronisation disponible' : 'Vos données restent disponibles localement'}</small>
            <button onClick={() => setOnline((value) => !value)}>{online ? 'Passer hors ligne' : 'Simuler la connexion'}</button>
          </div>
          <div className="user-card"><div className="avatar">RA</div><div><strong>Romain</strong><span>Administrateur ONG</span></div></div>
        </div>
      </aside>

      <main className="main-content">
        <header className="topbar">
          <div><span className="eyebrow">Finance Pro / 2026</span><h1>{title}</h1></div>
          <div className="top-actions"><span className="connection"><i /> {online ? 'En ligne' : 'Hors connexion'}</span><button className="icon-button" aria-label="Notifications">●</button><button className="profile-button">RA</button></div>
        </header>

        <section className="content">
          <div className="hero">
            <div><span className="eyebrow">VUE FINANCIÈRE</span><h2>Bonjour, Romain.</h2><p>Suivez la situation financière de votre organisation, même sans connexion.</p></div>
            <button className="primary">+ Nouvelle opération</button>
          </div>

          <div className="stats-grid">
            {stats.map((stat) => <article className="stat-card" key={stat.label}><span>{stat.label}</span><strong>{stat.value}</strong><small>{stat.trend}</small></article>)}
          </div>

          <div className="grid-two">
            <section className="panel"><div className="panel-head"><div><span className="eyebrow">ACTIVITÉ</span><h3>Flux financiers</h3></div><span className="period">Jan — Déc 2026</span></div><div className="chart"><div className="chart-line line-a" /><div className="chart-line line-b" /><div className="chart-bars">{[42, 58, 47, 70, 64, 81, 55, 74, 68, 88, 77, 94].map((height, index) => <span key={index} style={{ height: `${height}%` }} />)}</div></div><div className="legend"><span><i className="dot expense" />Dépenses</span><span><i className="dot revenue" />Recettes</span></div></section>
            <section className="panel"><div className="panel-head"><div><span className="eyebrow">À TRAITER</span><h3>Dernières opérations</h3></div><button className="link-button">Voir tout</button></div><div className="operation-list"><div><span className="operation-icon expense-icon">−</span><div><strong>Achat fournitures</strong><small>PROJ-2026-001 · Aujourd'hui</small></div><b>− 245 000 FCFA</b></div><div><span className="operation-icon revenue-icon">+</span><div><strong>Subvention reçue</strong><small>Bailleur institutionnel · Hier</small></div><b>+ 4 500 000 FCFA</b></div><div><span className="operation-icon expense-icon">−</span><div><strong>Mission terrain</strong><small>PROJ-2026-002 · Hier</small></div><b>− 680 000 FCFA</b></div></div></section>
          </div>
        </section>
      </main>
    </div>
  );
}

export default App;
