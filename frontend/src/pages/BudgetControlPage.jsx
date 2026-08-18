const rows = [
  { code: '610100', label: 'Personnel & consultants', budget: 18500000, committed: 12100000, actual: 10350000 },
  { code: '620200', label: 'Missions & déplacements', budget: 8200000, committed: 4760000, actual: 4210000 },
  { code: '630100', label: 'Fournitures & activités', budget: 11600000, committed: 7280000, actual: 6140000 },
  { code: '640300', label: 'Communication & visibilité', budget: 5400000, committed: 3180000, actual: 2790000 },
  { code: '650100', label: 'Frais administratifs', budget: 4100000, committed: 2240000, actual: 1980000 },
];
const money = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
const pct = (a,b) => Math.round((a/b)*100);

export default function BudgetControlPage() {
  const total = rows.reduce((s,r)=>s+r.budget,0);
  const actual = rows.reduce((s,r)=>s+r.actual,0);
  const committed = rows.reduce((s,r)=>s+r.committed,0);
  return <section className="content">
    <div className="hero"><div><span className="eyebrow">CONTRÔLE BUDGÉTAIRE</span><h2>Budget & engagements</h2><p>Comparez les allocations, engagements et dépenses réelles avant toute validation financière.</p></div><div className="hero-actions"><button className="secondary">Exporter</button><button className="primary">+ Nouvelle ligne budgétaire</button></div></div>
    <div className="stats-grid"><article className="stat-card"><span>Budget approuvé</span><strong>{money(total)}</strong><small>Exercice 2026</small></article><article className="stat-card"><span>Engagé</span><strong>{money(committed)}</strong><small>{pct(committed,total)} % du budget</small></article><article className="stat-card"><span>Réalisé</span><strong>{money(actual)}</strong><small>{pct(actual,total)} % du budget</small></article><article className="stat-card"><span>Disponible</span><strong>{money(total-committed)}</strong><small>Après engagements</small></article></div>
    <section className="panel"><div className="panel-head"><div><span className="eyebrow">SUIVI ANALYTIQUE</span><h3>Exécution par ligne budgétaire</h3></div><span className="period">01 jan — 31 déc 2026</span></div><div className="table-wrap"><table><thead><tr><th>Compte</th><th>Libellé</th><th>Budget</th><th>Engagé</th><th>Réalisé</th><th>Disponible</th><th>Exécution</th></tr></thead><tbody>{rows.map(r=>{const execution=pct(r.actual,r.budget);return <tr key={r.code}><td><strong>{r.code}</strong></td><td>{r.label}</td><td>{money(r.budget)}</td><td>{money(r.committed)}</td><td>{money(r.actual)}</td><td>{money(r.budget-r.committed)}</td><td><div className="budget-meter"><span style={{width:`${execution}%`}}/></div><small>{execution}%</small></td></tr>})}</tbody></table></div></section>
    <div className="grid-two"><section className="panel"><div className="panel-head"><div><span className="eyebrow">RÈGLES</span><h3>Contrôles avant engagement</h3></div></div>{[['Budget disponible','Le montant engagé ne peut dépasser le disponible','ACTIF'],['Pièce justificative','Une pièce est requise avant validation','ACTIF'],['Projet obligatoire','Chaque dépense doit être rattachée à un projet','ACTIF'],['Seuil d’approbation','Les montants élevés suivent un workflow renforcé','ACTIF']].map(x=><div className="control-step" key={x[0]}><span>✓</span><div><strong>{x[0]}</strong><small>{x[1]}</small></div><b>{x[2]}</b></div>)}</section><section className="panel"><div className="panel-head"><div><span className="eyebrow">ALERTES</span><h3>Points de vigilance</h3></div></div><div className="alert-box"><strong>Aucun dépassement critique</strong><small>Les lignes budgétaires restent dans les limites autorisées.</small></div><div className="alert-box warning-box"><strong>3 engagements à documenter</strong><small>Des pièces justificatives doivent encore être associées.</small></div></section></div>
  </section>;
}
