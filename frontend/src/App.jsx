import { useState } from 'react';
import { organizations, roles, users } from './data/organizations';

const navigation = [
  ['Tableau de bord', 'dashboard'],
  ['Organisations', 'organizations'],
  ['Utilisateurs & rôles', 'access'],
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

function ManagementPage({ mode }) {
  const isAccess = mode === 'access';
  return (
    <section className="content">
      <div className="hero">
        <div><span className="eyebrow">ADMINISTRATION</span><h2>{isAccess ? 'Utilisateurs, rôles et permissions' : 'Organisations'}</h2><p>{isAccess ? 'Contrôlez les accès à Finance Pro avec des rôles explicites.' : 'Gérez les organisations et leur contexte financier.'}</p></div>
        <button className="primary">+ {isAccess ? 'Inviter un utilisateur' : 'Nouvelle organisation'}</button>
      </div>
      <div className="stats-grid">
        {isAccess ? <>
          <article className="stat-card"><span>Utilisateurs</span><strong>{users.length}</strong><small>comptes configurés</small></article>
          <article className="stat-card"><span>Rôles</span><strong>{roles.length}</strong><small>profils disponibles</small></article>
          <article className="stat-card"><span>Actifs</span><strong>{users.filter((u) => u.status === 'active').length}</strong><small>accès actifs</small></article>
          <article className="stat-card"><span>En attente</span><strong>{users.filter((u) => u.status === 'pending').length}</strong><small>invitations</small></article>
        </> : <>
          <article className="stat-card"><span>Organisations</span><strong>{organizations.length}</strong><small>référencées localement</small></article>
          <article className="stat-card"><span>Actives</span><strong>{organizations.filter((o) => o.status === 'active').length}</strong><small>espaces disponibles</small></article>
          <article className="stat-card"><span>Devise</span><strong>XOF</strong><small>référentiel actuel</small></article>
          <article className="stat-card"><span>Exercice</span><strong>2026</strong><small>année fiscale active</small></article>
        </>}
      </div>
      <div className="grid-two">
        <section className="panel"><div className="panel-head"><div><span className="eyebrow">LISTE</span><h3>{isAccess ? 'Utilisateurs' : 'Organisations enregistrées'}</h3></div></div>
          <div className="operation-list">{(isAccess ? users : organizations).map((item) => <div key={item.id}>
            <span className="operation-icon">{isAccess ? item.name.charAt(0) : 'O'}</span>
            <div><strong>{item.name}</strong><small>{isAccess ? `${item.email} · ${item.role}` : `${item.code} · ${item.country} · ${item.currency}`}</small></div>
            <b>{isAccess ? (item.status === 'active' ? 'Actif' : 'En attente') : item.status === 'active' ? 'Active' : item.status}</b>
          </div>)}</div>
        </section>
        <section className="panel"><div className="panel-head"><div><span className="eyebrow">CONTRÔLE D'ACCÈS</span><h3>{isAccess ? 'Rôles disponibles' : 'Paramètres actifs'}</h3></div></div>
          {isAccess ? <div className="operation-list">{roles.map((role) => <div key={role.id}><span className="operation-icon revenue-icon">R</span><div><strong>{role.name}</strong><small>{role.description}</small></div><b>{role.permissions} droits</b></div>)}</div> : <div className="operation-list"><div><span className="operation-icon revenue-icon">✓</span><div><strong>Isolation organisationnelle</strong><small>Chaque espace possède son propre contexte financier.</small></div></div><div><span className="operation-icon revenue-icon">✓</span><div><strong>Devise</strong><small>Les opérations sont actuellement configurées en XOF.</small></div></div><div><span className="operation-icon revenue-icon">✓</span><div><strong>Exercice fiscal</strong><small>2026 est l'exercice actif.</small></div></div></div>}
        </section>
      </div>
    </section>
  );
}

function Dashboard({ online }) {
  return <section className="content">
    <div className="hero"><div><span className="eyebrow">VUE FINANCIÈRE</span><h2>Bonjour, Romain.</h2><p>Suivez la situation financière de votre organisation, même sans connexion.</p></div><button className="primary">+ Nouvelle opération</button></div>
    <div className="stats-grid">{stats.map((stat) => <article className="stat-card" key={stat.label}><span>{stat.label}</span><strong>{stat.value}</strong><small>{stat.trend}</small></article>)}</div>
    <div className="grid-two"><section className="panel"><div className="panel-head"><div><span className="eyebrow">ACTIVITÉ</span><h3>Flux financiers</h3></div><span className="period">Jan — Déc 2026</span></div><div className="chart"><div className="chart-line line-a" /><div className="chart-line line-b" /><div className="chart-bars">{[42,58,47,70,64,81,55,74,68,88,77,94].map((height,index)=><span key={index} style={{height:`${height}%`}} />)}</div></div><div className="legend"><span><i className="dot expense" />Dépenses</span><span><i className="dot revenue" />Recettes</span></div></section>
    <section className="panel"><div className="panel-head"><div><span className="eyebrow">À TRAITER</span><h3>Dernières opérations</h3></div><button className="link-button">Voir tout</button></div><div className="operation-list"><div><span className="operation-icon expense-icon">−</span><div><strong>Achat fournitures</strong><small>PROJ-2026-001 · Aujourd'hui</small></div><b>− 245 000 FCFA</b></div><div><span className="operation-icon revenue-icon">+</span><div><strong>Subvention reçue</strong><small>Bailleur institutionnel · Hier</small></div><b>+ 4 500 000 FCFA</b></div><div><span className="operation-icon expense-icon">−</span><div><strong>Mission terrain</strong><small>PROJ-2026-002 · Hier</small></div><b>− 680 000 FCFA</b></div></div></section></div>
  </section>;
}

function App() {
  const [active, setActive] = useState('dashboard');
  const [online, setOnline] = useState(false);
  const title = navigation.find(([, id]) => id === active)?.[0] ?? 'Tableau de bord';
  return <div className="app-shell">
    <aside className="sidebar"><div className="brand"><div className="brand-mark">F</div><div><strong>Finance Pro</strong><span>Gestion financière ONG</span></div></div><div className="workspace"><span>Organisation active</span><strong>Mon ONG</strong><small>Exercice 2026 · XOF</small></div><nav aria-label="Navigation principale"><small className="nav-label">ESPACE FINANCIER</small>{navigation.map(([label,id])=><button className={active===id?'nav-item active':'nav-item'} key={id} onClick={()=>setActive(id)}><span className="nav-icon">{label.charAt(0)}</span>{label}</button>)}</nav><div className="sidebar-bottom"><div className={online?'sync-card online':'sync-card'}><div><span className="status-dot" />{online?'Connecté':'Mode hors connexion'}</div><small>{online?'Synchronisation disponible':'Vos données restent disponibles localement'}</small><button onClick={()=>setOnline((value)=>!value)}>{online?'Passer hors ligne':'Simuler la connexion'}</button></div><div className="user-card"><div className="avatar">RA</div><div><strong>Romain</strong><span>Administrateur ONG</span></div></div></div></aside>
    <main className="main-content"><header className="topbar"><div><span className="eyebrow">Finance Pro / 2026</span><h1>{title}</h1></div><div className="top-actions"><span className="connection"><i /> {online?'En ligne':'Hors connexion'}</span><button className="icon-button" aria-label="Notifications">●</button><button className="profile-button">RA</button></div></header>{active==='dashboard'?<Dashboard online={online}/>:active==='organizations'||active==='access'?<ManagementPage mode={active}/>:<Dashboard online={online}/>}</main>
  </div>;
}

export default App;
