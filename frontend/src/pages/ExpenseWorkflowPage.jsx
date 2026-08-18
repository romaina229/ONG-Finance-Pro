import { useMemo, useState } from 'react';

const initialExpenses = [
  { id: 'DEP-2026-001', date: '18/08/2026', label: 'Fournitures de bureau', project: 'PROJ-2026-001', budget: '630100', amount: 245000, requester: 'Romain AKPO', status: 'À valider', document: 'Facture-FB-024.pdf' },
  { id: 'DEP-2026-002', date: '17/08/2026', label: 'Mission terrain', project: 'PROJ-2026-002', budget: '620200', amount: 480000, requester: 'Marie H.', status: 'Approuvée', document: 'OM-2026-018.pdf' },
  { id: 'DEP-2026-003', date: '16/08/2026', label: 'Communication projet', project: 'PROJ-2026-001', budget: '640300', amount: 750000, requester: 'Jean K.', status: 'Pièce manquante', document: null },
];

const money = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
const statusClass = status => status === 'Approuvée' ? 'badge success' : status === 'Pièce manquante' ? 'badge warning' : 'badge';

export default function ExpenseWorkflowPage() {
  const [expenses, setExpenses] = useState(initialExpenses);
  const [selected, setSelected] = useState(initialExpenses[0]);
  const [filter, setFilter] = useState('Toutes');

  const visible = useMemo(() => filter === 'Toutes' ? expenses : expenses.filter(e => e.status === filter), [expenses, filter]);

  const approve = () => {
    if (!selected || !selected.document) return;
    setExpenses(list => list.map(e => e.id === selected.id ? { ...e, status: 'Approuvée' } : e));
    setSelected(e => e ? { ...e, status: 'Approuvée' } : e);
  };

  return <section className="content">
    <div className="hero"><div><span className="eyebrow">CIRCUIT DE DÉPENSE</span><h2>Dépenses</h2><p>De la demande de dépense à l'approbation, avec contrôle budgétaire et pièces justificatives.</p></div><button className="primary">+ Nouvelle demande</button></div>

    <div className="stats-grid">
      <article className="stat-card"><span>Demandes</span><strong>{expenses.length}</strong><small>sur la période</small></article>
      <article className="stat-card"><span>Montant engagé</span><strong>{money(expenses.reduce((s,e) => s + e.amount, 0))}</strong><small>cumul des demandes</small></article>
      <article className="stat-card"><span>À valider</span><strong>{expenses.filter(e => e.status === 'À valider').length}</strong><small>action requise</small></article>
      <article className="stat-card"><span>Pièces manquantes</span><strong>{expenses.filter(e => !e.document).length}</strong><small>bloquantes</small></article>
    </div>

    <section className="panel">
      <div className="panel-head"><div><span className="eyebrow">REGISTRE DES DEMANDES</span><h3>Workflow des dépenses</h3></div><div className="filters">{['Toutes','À valider','Approuvée','Pièce manquante'].map(x => <button key={x} className={filter === x ? 'filter active' : 'filter'} onClick={() => setFilter(x)}>{x}</button>)}</div></div>
      <div className="table-wrap"><table><thead><tr><th>Référence</th><th>Dépense</th><th>Projet</th><th>Ligne</th><th>Montant</th><th>Demandeur</th><th>Statut</th></tr></thead><tbody>{visible.map(e => <tr key={e.id} onClick={() => setSelected(e)} className="clickable-row"><td><strong>{e.id}</strong><small>{e.date}</small></td><td>{e.label}</td><td>{e.project}</td><td>{e.budget}</td><td><strong>{money(e.amount)}</strong></td><td>{e.requester}</td><td><span className={statusClass(e.status)}>{e.status}</span></td></tr>)}</tbody></table></div>
    </section>

    {selected && <div className="grid-two">
      <section className="panel"><div className="panel-head"><div><span className="eyebrow">FICHE DÉPENSE</span><h3>{selected.id}</h3></div><span className={statusClass(selected.status)}>{selected.status}</span></div><div className="detail-grid"><div><small>Libellé</small><strong>{selected.label}</strong></div><div><small>Projet</small><strong>{selected.project}</strong></div><div><small>Ligne budgétaire</small><strong>{selected.budget}</strong></div><div><small>Montant</small><strong>{money(selected.amount)}</strong></div><div><small>Demandeur</small><strong>{selected.requester}</strong></div><div><small>Justificatif</small><strong>{selected.document || 'Aucun document'}</strong></div></div><div className="workflow"><div className="workflow-step done"><span>1</span><div><strong>Demande créée</strong><small>Informations saisies</small></div></div><div className="workflow-step done"><span>2</span><div><strong>Contrôle budgétaire</strong><small>Disponible suffisant</small></div></div><div className={selected.document ? 'workflow-step current' : 'workflow-step blocked'}><span>3</span><div><strong>Pièce justificative</strong><small>{selected.document ? 'Document présent' : 'Document requis'}</small></div></div><div className={selected.status === 'Approuvée' ? 'workflow-step done' : 'workflow-step'}><span>4</span><div><strong>Approbation</strong><small>Validation par l'autorité habilitée</small></div></div><div className="workflow-step"><span>5</span><div><strong>Écriture comptable</strong><small>Génération après approbation</small></div></div></div><div className="action-row"><button className="secondary">Demander correction</button><button className="primary" disabled={!selected.document || selected.status === 'Approuvée'} onClick={approve}>Approuver la dépense</button></div></section>
      <section className="panel"><div className="panel-head"><div><span className="eyebrow">CONTRÔLES</span><h3>Avant approbation</h3></div></div>{[['Budget disponible','245 000 FCFA disponibles sur la ligne','OK'],['Projet rattaché',selected.project,'OK'],['Pièce justificative',selected.document || 'Document absent',selected.document ? 'OK' : 'BLOQUÉ'],['Niveau d’autorité','Responsable financier','REQUIS']].map(([title,text,state]) => <div className="control-step" key={title}><span>{state === 'OK' ? '✓' : '!'}</span><div><strong>{title}</strong><small>{text}</small></div><b>{state}</b></div>)}<div className="audit-note"><strong>Traçabilité</strong><small>Toute modification de cette demande devra être historisée avant l’écriture comptable.</small></div></section>
    </div>}
  </section>;
}
