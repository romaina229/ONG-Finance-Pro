import React from 'react';

export default class AppErrorBoundary extends React.Component {
  constructor(props){ super(props); this.state = { error: null }; }
  static getDerivedStateFromError(error){ return { error }; }
  render(){
    if(!this.state.error) return this.props.children;
    return <main className="error-screen"><div className="error-card"><span className="eyebrow">FINANCE PRO</span><h1>Une erreur d’affichage est survenue</h1><p>L’application a protégé votre session au lieu d’afficher une page blanche.</p><pre>{this.state.error.message}</pre><button className="primary" onClick={()=>window.location.reload()}>Recharger l’application</button></div></main>;
  }
}
