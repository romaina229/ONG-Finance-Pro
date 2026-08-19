import React from 'react';
import { useEffect, useState } from 'react';
import { listBudgets, listProjects } from '../api/client';
import { projects as fallbackProjects, budgets as fallbackBudgets } from '../data/projects';

const money = value => `${new Intl.NumberFormat('fr-FR').format(Number(value || 0))} FCFA`;
export default function ProjectsPage() {
  const [projects, setProjects] = useState(fallbackProjects);
  const [budgets, setBudgets] = useState(fallbackBudgets);
  useEffect(() => { listProjects().then(r => setProjects(r.data || [])).catch(() => {}); listBudgets().then(r => setBudgets(r.data || [])).catch(() => {}); }, []);
  const total = projects.reduce((s,p) => s + Number(p.budget_amount ?? p.budget ?? 0), 0);
  const spent = projects.reduce((s,p) => s + Number(p.spent_amount ?? p.spent ?? 0), 0);
  return <section className="content"><div className="hero"><div><span className="eyebrow">PROGRAMMES · PORTEFEUILLE</span><h2>Projets & budgets</h2><p>Structurez les financements par projet, bailleur, ligne budgétaire et période.</p></div><button className="primary">+ Nouveau projet</button></div>
    <div className="stats-grid"><article className="stat-card"><span>Projets</span><strong>{projects.length}</strong><small>portefeuille actif</small></article><article className="stat-card"><span>Budget total</span><strong>{money(total)}</strong><small>allocations approuvées</small></article><article className="stat-card"><span>Consommé</span><strong>{money(spent)}</strong><small>engagements / dépenses</small></article><article className="stat-card"><span>Reste</span><strong>{money(Math.max(0,total-spent))}</strong><small>disponible</small></article></div>
    <div className="grid-two"><section className="panel"><div className="panel-head"><div><span className="eyebrow">PORTEFEUILLE</span><h3>Projets</h3></div><button className="link-button">Exporter</button></div><div className="operation-list">{projects.map(p => { const budget=Number(p.budget_amount ?? p.budget); const used=Number(p.spent_amount ?? p.spent); const pct=budget?Math.round(used/budget*100):0; return <div key={p.id || p.code}><span className="operation-icon revenue-icon">P</span><div><strong>{p.name}</strong><small>{p.code} · {p.donor || 'Fonds propre'} · {p.status || 'active'}</small><div className="progress"><span style={{width:`${Math.min(pct,100)}%`}}/></div></div><b>{pct}%</b></div>; })}</div></section>
    <section className="panel"><div className="panel-head"><div><span className="eyebrow">LIGNES BUDGÉTAIRES</span><h3>Allocations</h3></div></div><div className="operation-list">{budgets.map(b => <div key={b.id || b.code}><span className="operation-icon">B</span><div><strong>{b.category}</strong><small>{b.project?.name || b.project || 'Projet'}</small></div><b>{money(b.allocated_amount ?? b.allocated)}</b></div>)}</div></section></div>
  </section>;
}
