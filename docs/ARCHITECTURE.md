# Finance Pro — Architecture technique

## Architecture cible

Finance Pro est Offline First :

```text
Utilisateur
  -> Web / Desktop / Mobile
  -> Offline Data Layer
  -> SQLite local
  -> Sync Engine / Queue
  -> Laravel REST API (connexion disponible)
  -> PostgreSQL + Object Storage
```

## Règle fondamentale

L'interface métier ne dépend jamais directement d'un appel réseau pour afficher ou enregistrer une opération. Elle passe par une couche de données locale.

## Synchronisation

```text
Création/modification locale
 -> transaction SQLite
 -> sync_queue
 -> pending
 -> push
 -> validation serveur
 -> mapping local_id/server_id
 -> synced
```

Les documents volumineux passent après les données comptables critiques.

## Multi-tenant

Toutes les opérations métier sont rattachées à `organization_id`. L'API impose l'organisation courante. PostgreSQL RLS sert de défense supplémentaire sur les tables sensibles.

## Conflits

Une modification locale ancienne ne doit jamais écraser silencieusement une version serveur plus récente. Résolutions : `server_wins`, `client_wins`, `manual`, `pending`.

## Documents

Les métadonnées sont synchronisées avec les données métier. Les fichiers sont stockés dans un stockage objet et contrôlés par SHA-256.

## Sécurité

- HTTPS obligatoire.
- Hashage sécurisé des mots de passe.
- Chiffrement des secrets et données locales sensibles.
- RBAC côté API.
- Audit des opérations.
- Sauvegardes PostgreSQL automatisées.
- Aucun secret dans Git.

## Décision structurante

Le moteur Offline First est une partie centrale du produit. Les écrans métiers complexes seront construits au-dessus de services de domaine communs et non directement au-dessus de SQLite ou de l'API.
