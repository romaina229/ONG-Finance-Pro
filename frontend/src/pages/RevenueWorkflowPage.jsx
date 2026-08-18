import { useMemo, useState } from 'react';

const initialReceipts = [
  { id: 'REC-2026-001', date: '16/08/2026', label: 'Subvention reçue', source: 'Global Fund', project: 'Autonomisation des jeunes', amount: 4500000, tranche: 'Tranche 1', status: 'Rapprochée', reference: 'GF-2026-08', document: 'Avis-credit-GF.pdf' },
  { id: 'REC-2026-002', date: '12/08/2026', label: 'Financement programme', source: 'UNFPA', project: 'Santé communautaire', amount: 3200000, tranche: 'Décaissement initial', status: 'À rapprocher', reference: 'UNFPA-2026-14', document: 'Avis-credit-UNFPA.pdf' },
  { id: 'REC-2026-003', date: '10/08/2026', label: 'Contribution propre', source: 'Fonds propre', project: 'Renforcement institutionnel', amount: 900000, tranche: 'Contribution', status: 'En attente', reference: 'FP-2026-003', document: null },
];

const money = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
const statusClass = status => status === 'Rapprochée' ? 'badge success' : status === 'En attente' ? 'badge warning' : 'badge';

export default function RevenueWorkflowPage() {
  const [receipts, setReceipts] = useState(initialReceipts);
  const [selected, setSelected] = useState(initialReceipts[0]);
  const [filter, setFilter] = useState('Toutes');
  const visible = useMemo(() => filter === 'Toutes' ? receipts : receipts.filter(r => r.status === filter), [receipts, filter]);
  const reconcile = () => {
    if (!selected || !selected.document) return;
    setReceipts(list => list.map(r => r.id === selected.id ? { ...r, status: 'Rapprochée' } : r));
    setSelected(r => r ? { ...r, status: 'Rapprochée' } : r);
  };

  return <section className="content">
    <div className="hero"><div><span className="eyebrow">CIRCUIT DES RECETTES</span><h2>Recettes, financements & subventions</h2><p>Enregistrez les financements, contrôlez les pièces et rapprochez les encaissements avant comptabilisation.</p></div><button className="primary">+ Nouvelle recette</button></div>
    <div className="stats-grid">
      <article className="stat-card"><span>Encaissements</span><strong>{receipts.length}</strong><small>sur la période</small></article>
      <article className="stat-card"><span>Total reçu</span><strong>{money(receipts.reduce((s,r) => s + r.amount, 0))}</strong><small>financements enregistrés</small></article>
      <article className="stat-card"><span>À rapprocher</span><strong>{receipts.filter(r => r.status === 'À rapprocher').length}</strong><small>relevé bancaire à contrôler</small></article>
      <article className="stat-card"><span>En attente</span><strong>{receipts.filter(r => r.status === 'En attente').length}</strong><small>pièce ou confirmation requise</small></article>
    </div>
    <section className="panel"><div className="panel-head"><div><span className="eyebrow">REGISTRE DES ENCAISSEMENTS</span><h3>Financements reçus</h3></div><div className="filters">{['Toutes','Rapprochée','À rapprocher','En attente'].map(x => <button key={x} className={filter === x ? 'filter active' : 'filter'} onClick={() => setFilter(x)}>{x}</button>)}</div></div>
      <div className="table-wrap"><table><thead><tr><th>Référence</th><th>Financeur / source</th><th>Projet</th><th>Tranche</th><th>Montant</th><th>Statut</th></tr></thead><tbody>{visible.map(r => <tr key={r.id} onClick={() => setSelected(r)} className="clickable-row"><td><strong>{r.id}</strong><small>{r.date} · {r.reference}</small></td><td>{r.source}<small>{r.label}</small></td><td>{r.project}</td><td>{r.tranche}</td><td><strong>{money(r.amount)}</strong></td><td><span className={statusClass(r.status)}>{r.status}</span></td></tr>)}</tbody></table></div>
    </section>
    {selected && <div className="grid-two"><section className="panel"><div className="panel-head"><div><span className="eyebrow">FICHE FINANCEMENT</span><h3>{selected.id}</h3></div><span className={statusClass(selected.status)}>{selected.status}</span></div>
      <div className="detail-grid"><div><small>Financeur / source</small><strong>{selected.source}</strong></div><div><small>Référence financeur</small><strong>{selected.reference}</strong></div><div><small>Projet</small><strong>{selected.project}</strong></div><div><small>Tranche</small><strong>{selected.tranche}</strong></div><div><small>Montant</small><strong>{money(selected.amount)}</strong></div><div><small>Justificatif</small><strong>{selected.document || 'Aucun document'}</strong></div></div>
      <div className="workflow"><div className="workflow-step done"><span>1</span><div><strong>Financement enregistré</strong><small>Source et montant identifiés</small></div></div><div className="workflow-step done"><span>2</span><div><strong>Projet identifié</strong><small>Rattachement au programme financier</small></div></div><div className={selected.document ? 'workflow-step done' : 'workflow-step blocked'}><span>3</span><div><strong>Pièce justificative</strong><small>{selected.document ? 'Avis de crédit disponible' : 'Avis de crédit requis'}</small></div></div><div className={selected.status === 'Rapprochée' ? 'workflow-step done' : 'workflow-step current'}><span>4</span><div><strong>Rapprochement bancaire</strong><small>Correspondance avec l'encaissement bancaire</small></div></div><div className="workflow-step"><span>5</span><div><strong>Écriture comptable</strong><small>Génération après validation</small></div></div></div>
      <div className="action-row"><button className="secondary">Signaler un écart</button><button className="primary" disabled={!selected.document || selected.status === 'Rapprochée'} onClick={reconcile}>Valider le rapprochement</button></div>
    </section><section className="panel"><div className="panel-head"><div><span className="eyebrow">CONTRÔLES</span><h3>Avant comptabilisation</h3></div></div>
      {[['Source identifiée',selected.source,'OK'],['Projet rattaché',selected.project,'OK'],['Pièce justificative',selected.document || 'Document absent',selected.document ? 'OK' : 'BLOQUÉ'],['Rapprochement bancaire',selected.status === 'Rapprochée' ? 'Correspondance confirmée' : 'À effectuer',selected.status === 'Rapprochée' ? 'OK' : 'REQUIS'],['Traçabilité','Référence financeur + date + montant','ACTIF']].map(([title,text,state]) => <div className="control-step" key={title}><span>{state === 'OK' || state === 'ACTIF' ? '✓' : '!'}</span><div><strong>{title}</strong><small>{text}</small></div><b>{state}</b></div>)}
      <div className="audit-note"><strong>Règle comptable</strong><small>Une recette ne devient une écriture comptable validée qu'après contrôle de la source, du projet, de la pièce et du rapprochement.</small></div></section></div>}
  </section>;
}
