# Finance Pro — Audit initial du schéma Offline First

## État constaté

Le dépôt GitHub était vide au moment de l'audit : aucune arborescence ni aucun schéma SQL n'était encore versionné. Le schéma fourni dans la conversation n'était donc pas vérifiable directement dans GitHub.

## Corrections à appliquer avant le développement métier

### 1. Déclencheur SQLite de `expenses_local`

Le trigger fourni est problématique :

`AFTER UPDATE ... WHEN NEW.is_dirty = 0`

Il ne couvre pas correctement les modifications normales et peut provoquer des comportements difficiles à prévoir. La responsabilité de marquer une entité `dirty` doit être portée par le repository/service transactionnel, avec un trigger limité à la journalisation si nécessaire.

### 2. Identifiants locaux et serveur

Le modèle `id` local + `server_id` est conservé. Il faut cependant une règle stricte : les relations entre objets créés hors ligne utilisent les IDs locaux jusqu'à synchronisation ; le moteur de synchronisation doit maintenir le mapping `local_id -> server_id` et réécrire les références dépendantes de manière transactionnelle.

### 3. Détection de conflit

`updated_at` seul n'est pas suffisant pour une synchronisation robuste. Ajouter une version monotone côté serveur, par exemple `version BIGINT`, sur les entités synchronisables. Le client envoie la dernière version serveur connue ; le serveur refuse une écriture obsolète et retourne le conflit.

### 4. Suppression hors ligne

Le `is_deleted` local est conservé. Une suppression doit être synchronisée comme une opération explicite avant purge physique. Le serveur utilise le soft delete pour les entités métier lorsque la traçabilité l'exige.

### 5. Multi-tenant

`organization_id` doit être obligatoire sur toutes les tables métier qui contiennent des données d'une ONG. Les contrôles d'organisation doivent exister dans les policies, services et requêtes API ; RLS ne doit pas être considéré comme l'unique mécanisme de sécurité.

### 6. Documents

Le fichier binaire et ses métadonnées doivent avoir des cycles de synchronisation distincts. Le hash SHA-256 est conservé. L'upload du fichier ne doit jamais bloquer la synchronisation d'une dépense ou d'une recette.

### 7. Approbations

Les actions d'approbation doivent être immuables dans le journal d'audit. Une modification du montant après approbation doit obligatoirement remettre l'opération dans un état nécessitant une nouvelle validation.

### 8. Données financières

Les montants serveur doivent rester en `NUMERIC`, jamais en flottant. Côté SQLite, `REAL` est acceptable pour le MVP technique mais devra être remplacé par une représentation exacte (entier en unité mineure lorsque possible, ou chaîne décimale) pour éviter les erreurs d'arrondi dans les calculs financiers.

## Décision

Le schéma fourni constitue une bonne base fonctionnelle, mais il ne doit pas être utilisé tel quel en production. Le développement commence par la fondation de synchronisation, puis les modules comptables.
