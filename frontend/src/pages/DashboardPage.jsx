import React from 'react';
import { useEffect, useState } from 'react';
import { dashboardSummary } from '../api/client';
import { projects as fallbackProjects } from '../data/projects';
import { expenses, revenues } from '../data/transactions';

const money = value => `${new Intl.NumberFormat('fr-FR').format(Number(value || 0))} FCFA`;
const fallback = { kpis: { income: 18760000, expenses: 12480000, balance: 48250000, budget: 52000000, execution_rate: 24, pending: 3 }, projects: fallbackProjects, recent_transactions: [...expenses, ...revenues].slice(0, 6) };

export default function DashboardPage() {
  const [data, setData] = useState(fallback);
  const [live, setLive] = useState(false);
  useEffect(() => { dashboardSummary().then(result => { setData(result); setLive(true); }).catch(() => setLive(false)); }, []);
  const k = data.kpis;
  return <section className="content">
    <div className="hero"><div><span className="eyebrow">VUE EXÉCUTIVE · EXERCICE 2026</span><h2>Tableau de bord financier</h2><p>Pilotage des recettes, dépenses, budgets et engagements de votre organisation.</p></div><div className="hero-actions"><span className="data-mode">{live ? 'Données serveur' : 'Données de démonstration'}</span><button className="primary">+ Nouvelle opération</button></div></div>
    <div className="stats-grid">
      <article className="stat-card"><span>Solde disponible</span><strong>{money(k.balance)}</strong><small>Recettes − dépenses</small></article>
      <article className="stat-card"><span>Recettes</span><strong>{money(k.income)}</strong><small>Encaissements enregistrés</small></article>
      <article className="stat-card"><span>Dépenses</span><strong>{money(k.expenses)}</strong><small>Sorties financières</small></article>
      <article className="stat-card"><span>Exécution budgétaire</span><strong>{Number(k.execution_rate || 0).toFixed(1)} %</strong><small>{k.pending || 0} opération(s) à traiter</small></article>
    </div>
    <div className="dashboard-grid">
      <section className="panel panel-large"><div className="panel-head"><div><span className="eyebrow">PORTEFEUILLE</span><h3>Projets et consommation</h3></div><span className="period">2026</span></div><div className="project-bars">{(data.projects || []).map(project => { const pct = project.budget_amount ? Math.round(project.spent_amount / project.budget_amount * 100) : Math.round(project.spent / project.budget * 100); return <div className="project-bar" key={project.id || project.code}><div><strong>{project.name}</strong><span>{money(project.spent_amount ?? project.spent)} / {money(project.budget_amount ?? project.budget)}</span></div><div className="bar-track"><i style={{ width: `${Math.min(pct, 100)}%` }}/></div><b>{pct}%</b></div>; })}</div></section>
      <section className="panel"><div className="panel-head"><div><span className="eyebrow">ACTIVITÉ RÉCENTE</span><h3>Dernières opérations</h3></div></div><div className="operation-list">{(data.recent_transactions || []).map((item, index) => <div key={item.id || item.reference || index}><span className={item.type === 'revenue' ? 'operation-icon revenue-icon' : 'operation-icon expense-icon'}>{item.type === 'revenue' ? '+' : '−'}</span><div><strong>{item.label}</strong><small>{item.reference || item.date || 'Opération financière'}</small></div><b>{item.type === 'revenue' ? '+' : '−'} {money(item.amount)}</b></div>)}</div></section>
    </div>
  </section>;
}
