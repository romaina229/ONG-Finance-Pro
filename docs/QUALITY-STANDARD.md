# Finance Pro — Standard de qualité et de vérification

## Principe

Aucune fonctionnalité métier ne doit être considérée comme terminée sur la seule base de son implémentation. Elle doit être vérifiée par le code, les tests, les contraintes de données, la sécurité et le comportement Offline First.

## Sources de vérité du projet

1. Cahier des charges et décisions validées du projet.
2. Schémas PostgreSQL et SQLite versionnés dans le dépôt.
3. Contrats API versionnés.
4. Tests automatisés.
5. Documentation technique maintenue avec le code.

## Règles de développement

- TypeScript strict côté frontend.
- PHP/Laravel avec validation serveur systématique.
- PostgreSQL comme source de vérité distante.
- SQLite comme source de travail locale hors connexion.
- Aucun calcul financier critique basé sur des flottants binaires lorsque des montants décimaux sont nécessaires.
- Aucun secret, mot de passe, token ou clé privée dans Git.
- Toutes les opérations métier sont contrôlées par organisation et permissions.
- Les opérations synchronisables possèdent une identité stable, une version et un état de synchronisation.
- Les suppressions sont propagées explicitement et ne doivent pas être perdues lors d'une synchronisation.

## Vérifications obligatoires avant livraison

### Backend
- migrations exécutables sur une base vide ;
- seeders reproductibles ;
- tests unitaires des règles financières ;
- tests d'autorisation et d'isolation multi-tenant ;
- tests API ;
- tests de synchronisation et conflits ;
- tests de validation des pièces justificatives.

### Frontend / Desktop
- fonctionnement sans réseau ;
- lecture et écriture locales ;
- reprise après interruption ;
- file de synchronisation persistante ;
- absence de double envoi ;
- affichage clair de l'état local/synchronisé/conflit.

### Sécurité
- mots de passe hashés avec un algorithme adapté ;
- HTTPS en production ;
- contrôle RBAC côté serveur ;
- isolation tenant ;
- journal d'audit ;
- sauvegardes et restauration testées.

## Définition de terminé

Une fonctionnalité est `DONE` uniquement si :

1. le code est présent ;
2. la base et les migrations sont cohérentes ;
3. les tests pertinents passent ;
4. le comportement Offline First est couvert lorsqu'il est concerné ;
5. la sécurité et les permissions sont vérifiées ;
6. la documentation est à jour ;
7. le commit est identifiable et descriptif.
