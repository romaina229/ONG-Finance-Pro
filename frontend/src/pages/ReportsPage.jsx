import { useEffect, useState } from 'react';
import { financialReport } from '../api/client';
import { reportSummary, reportLines } from '../data/reports';
const money = value => `${new Intl.NumberFormat('fr-FR').format(Number(value || 0))} FCFA`;
export default function ReportsPage(){
 const [report,setReport]=useState({summary:reportSummary,projects:reportLines,ledger:[]});
 useEffect(()=>{financialReport().then(setReport).catch(()=>{});},[]);
 const s=report.summary||reportSummary; const budget=Number(s.budget||0); const expenses=Number(s.expenses||0); const execution=budget?expenses/budget*100:0;
 return <section className="content"><div className="hero"><div><span className="eyebrow">REPORTING FINANCIER</span><h2>Rapports & analyse</h2><p>Une lecture consolidée des recettes, dépenses, budgets et soldes.</p></div><button className="primary">Exporter le rapport</button></div>
 <div className="stats-grid"><article className="stat-card"><span>Recettes</span><strong>{money(s.income)}</strong><small>encaissements</small></article><article className="stat-card"><span>Dépenses</span><strong>{money(s.expenses)}</strong><small>sorties</small></article><article className="stat-card"><span>Solde</span><strong>{money(s.balance)}</strong><small>net financier</small></article><article className="stat-card"><span>Exécution</span><strong>{execution.toFixed(1)} %</strong><small>consommation budgétaire</small></article></div>
 <section className="panel"><div className="panel-head"><div><span className="eyebrow">ANALYSE PAR PROJET</span><h3>Exécution budgétaire</h3></div><span className="period">2026</span></div><div className="project-bars">{(report.projects||[]).map(p=>{const pct=Number(p.execution_rate ?? (Number(p.spent)/Number(p.budget)*100)||0);return <div className="project-bar" key={p.code||p.name||p.label}><div><strong>{p.name||p.label}</strong><span>{money(p.spent)} / {money(p.budget)}</span></div><div className="bar-track"><i style={{width:`${Math.min(pct,100)}%`}}/></div><b>{pct.toFixed(0)}%</b></div>})}</div></section>
 <section className="panel"><div className="panel-head"><div><span className="eyebrow">JOURNAL</span><h3>Dernières écritures</h3></div></div><div className="table-wrap"><table><thead><tr><th>Référence</th><th>Libellé</th><th>Type</th><th>Montant</th><th>Statut</th></tr></thead><tbody>{(report.ledger?.data||[]).map(row=><tr key={row.id}><td><strong>{row.reference}</strong></td><td>{row.label}</td><td>{row.type==='revenue'?'Recette':'Dépense'}</td><td><strong>{money(row.amount)}</strong></td><td><span className={row.workflow_status==='approved'||row.workflow_status==='reconciled'?'badge success':'badge warning'}>{row.workflow_status}</span></td></tr>)}</tbody></table></div></section>
 </section>;
}
