import { useState } from 'react';

const plans = [
  { id:'essential', name:'Essentiel', price:15000, users:'3 utilisateurs', description:'Pour les petites ONG et associations.', features:['Comptabilité générale','Dépenses & recettes','Projets & budgets','Pièces justificatives','Rapports de base'] },
  { id:'professional', name:'Professionnel', price:30000, users:'10 utilisateurs', featured:true, description:'Le meilleur équilibre pour une ONG structurée.', features:['Tout Essentiel','Contrôle budgétaire','Subventions & bailleurs','Rapprochement bancaire','Workflow d’approbation','Audit trail','Rapports financiers avancés'] },
  { id:'plus', name:'ONG Plus', price:60000, users:'25 utilisateurs', description:'Pour les ONG multi-projets et multi-bailleurs.', features:['Tout Professionnel','Multi-fonds & programmes','Consolidation','Reporting bailleurs avancé','API & intégrations','Contrôles internes avancés'] },
  { id:'enterprise', name:'Enterprise', price:null, users:'Sur mesure', description:'Pour les grandes ONG et groupes internationaux.', features:['Architecture multi-entités','SSO & sécurité avancée','Intégrations sur mesure','Migration de données','Support prioritaire','Contrat et SLA sur mesure'] },
];
const money = n => new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';

export default function SubscriptionPage(){
 const [billing,setBilling]=useState('monthly'); const [selected,setSelected]=useState('professional');
 return <section className="content subscription-page">
  <div className="hero"><div><span className="eyebrow">FINANCE PRO · ABONNEMENT</span><h2>Choisissez votre formule</h2><p>14 jours d’essai gratuit avec accès complet. Aucune donnée n’est supprimée à la fin de l’essai.</p></div><div className="trial-card"><strong>14 jours</strong><span>d’essai gratuit</span></div></div>
  <div className="subscription-toolbar"><div><strong>Facturation</strong><button className={billing==='monthly'?'toggle active':'toggle'} onClick={()=>setBilling('monthly')}>Mensuelle</button><button className={billing==='annual'?'toggle active':'toggle'} onClick={()=>setBilling('annual')}>Annuelle</button></div><span>Les tarifs sont administrables depuis le Backend.</span></div>
  <div className="plans-grid">{plans.map(plan=> <article key={plan.id} className={plan.featured?'plan-card featured':'plan-card'} onClick={()=>setSelected(plan.id)}>
    {plan.featured && <div className="popular">RECOMMANDÉ</div>}<div className="plan-head"><div><span className="eyebrow">{plan.users}</span><h3>{plan.name}</h3></div>{selected===plan.id && <span className="selected-plan">✓</span>}</div><p>{plan.description}</p><div className="plan-price">{plan.price ? <><strong>{money(plan.price)}</strong><span>/ mois</span></> : <strong>Sur devis</strong>}</div>{billing==='annual' && plan.price && <small className="annual-note">Équivalent à {money(plan.price*10)} / an</small>}<button className={selected===plan.id?'primary':'secondary'}>{plan.price ? (selected===plan.id?'Formule sélectionnée':'Choisir cette formule'):'Contacter Finance Pro'}</button><ul>{plan.features.map(f=><li key={f}>✓ {f}</li>)}</ul>
  </article>)}</div>
  <section className="subscription-footer panel"><div><span className="eyebrow">APRÈS L’ESSAI</span><h3>Votre organisation garde le contrôle de ses données</h3><p>À l’expiration des 14 jours, les fonctionnalités financières sont suspendues jusqu’à l’activation d’un abonnement. L’accès aux paramètres, aux factures et à l’export des données reste disponible.</p></div><div className="subscription-security"><strong>Paiement sécurisé</strong><span>Facturation XOF · Historique des factures · Activation contrôlée</span></div></section>
 </section>;
}
