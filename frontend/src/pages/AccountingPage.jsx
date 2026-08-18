const journal = [
  ['OD-2026-001', 'Subvention Global Fund', '701100', '+ 4 500 000 FCFA', 'Validée'],
  ['OD-2026-002', 'Achat fournitures', '601200', '− 245 000 FCFA', 'Validée'],
  ['OD-2026-003', 'Frais de mission', '625100', '− 180 000 FCFA', 'À valider'],
  ['OD-2026-004', 'Frais bancaires', '627100', '− 35 500 FCFA', 'Validée'],
];

function Stat({ label, value, note }) {
  return <article className="stat-card"><span>{label}</span><strong>{value}</strong><small>{note}</small></article>;
}

export default function AccountingPage() {
  return <section className="content">
    <div className="hero">
      <div><span className="eyebrow">COMPTABILITÉ</span><h2>Journal comptable</h2><p>Suivez les écritures, contrôles, validations et équilibres de l'exercice.</p></div>
      <div className="hero-actions"><button className="secondary">Plan comptable</button><button className="primary">+ Nouvelle écriture</button></div>
    </div>
    <div className="stats-grid">
      <Stat label="Écritures" value="1 284" note="exercice 2026" />
      <Stat label="Débit" value="62 480 000 FCFA" note="cumul exercice" />
      <Stat label="Crédit" value="62 480 000 FCFA" note="cumul exercice" />
      <Stat label="À contrôler" value="18" note="écritures en attente" />
    </div>
    <div className="accounting-layout">
      <section className="panel">
        <div className="panel-head"><div><span className="eyebrow">JOURNAL</span><h3>Dernières écritures</h3></div><button className="link-button">Voir le journal complet</button></div>
        <div className="table-wrap"><table><thead><tr><th>Référence</th><th>Libellé</th><th>Compte</th><th>Montant</th><th>Statut</th></tr></thead><tbody>{journal.map(row => <tr key={row[0]}>{row.map((cell, i) => <td key={i}>{i === 4 ? <span className={cell === 'Validée' ? 'badge success' : 'badge warning'}>{cell}</span> : cell}</td>)}</tr>)}</tbody></table></div>
      </section>
      <section className="panel control-panel">
        <div className="panel-head"><div><span className="eyebrow">CONTRÔLES</span><h3>Cycle financier</h3></div></div>
        {[['01','Pièce justificative','Document associé à chaque opération','OK'],['02','Validation budgétaire','Contrôle du budget disponible','OK'],['03','Approbation','Workflow selon le niveau d’autorité','18'],['04','Audit trail','Historique des changements','ACTIF']].map(([n,title,text,status]) => <div className="control-step" key={n}><span>{n}</span><div><strong>{title}</strong><small>{text}</small></div><b>{status}</b></div>)}
      </section>
    </div>
  </section>;
}
