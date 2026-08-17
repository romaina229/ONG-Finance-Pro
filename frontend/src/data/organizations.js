export const organizations = [
  {
    id: 'org-001',
    name: 'Mon ONG',
    code: 'ONG-001',
    country: 'Bénin',
    currency: 'XOF',
    fiscalYear: 2026,
    status: 'active',
  },
  {
    id: 'org-002',
    name: 'Organisation partenaire',
    code: 'ONG-002',
    country: 'Bénin',
    currency: 'XOF',
    fiscalYear: 2026,
    status: 'active',
  },
];

export const roles = [
  { id: 'role-admin', name: 'Administrateur', description: 'Accès complet à l’organisation', permissions: 12 },
  { id: 'role-finance', name: 'Responsable financier', description: 'Gestion financière et rapports', permissions: 9 },
  { id: 'role-accountant', name: 'Comptable', description: 'Saisie et suivi des opérations', permissions: 7 },
  { id: 'role-auditor', name: 'Auditeur', description: 'Consultation et contrôle', permissions: 4 },
];

export const users = [
  { id: 'usr-001', name: 'Romain', email: 'admin@financepro.local', role: 'Administrateur', status: 'active', lastActivity: 'Aujourd’hui' },
  { id: 'usr-002', name: 'Responsable financier', email: 'finance@financepro.local', role: 'Responsable financier', status: 'active', lastActivity: 'Aujourd’hui' },
  { id: 'usr-003', name: 'Comptable', email: 'compta@financepro.local', role: 'Comptable', status: 'pending', lastActivity: 'Hier' },
];
