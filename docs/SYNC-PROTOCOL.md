# Finance Pro — Protocole de synchronisation Offline First

## 1. Principe

SQLite est la base de travail locale. PostgreSQL est la source de vérité serveur. L'utilisateur doit pouvoir créer et modifier les données métier sans réseau.

## 2. Identité

Chaque enregistrement créé hors ligne possède un UUID local. `server_id` est renseigné après acceptation serveur. Les relations locales utilisent l'UUID local jusqu'au mapping serveur.

## 3. Versionnement

Chaque entité synchronisable possède `local_version` et `server_version`. Le serveur maintient une version monotone. Une écriture distante doit indiquer la dernière `server_version` connue. Si elle est obsolète, le serveur renvoie un conflit au lieu d'écraser silencieusement la version distante.

## 4. Push

1. Lire les éléments `pending` de `sync_queue` par priorité.
2. Marquer l'élément `syncing` dans une transaction locale.
3. Envoyer l'opération et la version serveur connue.
4. Le serveur accepte, rejette ou retourne un conflit.
5. En cas de succès : mapping `local_id/server_id`, nouvelle version serveur, `synced`, puis suppression logique de l'élément de queue.
6. En cas d'erreur réseau : retour à `pending` avec retry.
7. En cas de conflit : `conflict` et conservation des deux versions.

## 5. Pull

Le client demande les changements depuis son dernier curseur de synchronisation. Les changements reçus sont appliqués transactionnellement. Une donnée locale modifiée ne doit jamais être écrasée silencieusement.

## 6. Suppressions

Une suppression hors ligne est une opération `delete` conservée dans la queue. La ligne locale reste disponible tant que le serveur n'a pas accusé réception. Le serveur conserve un tombstone lorsque cela est nécessaire à la propagation de suppression vers les autres appareils.

## 7. Fichiers

Les métadonnées des documents et les binaires ont des queues distinctes. Les données comptables ont priorité sur les photos/PDF volumineux. Le SHA-256 permet de vérifier l'intégrité et d'éviter les doublons.

## 8. Idempotence

Chaque opération de synchronisation doit disposer d'une clé d'idempotence stable afin qu'un retry après timeout ne crée pas un doublon.

## 9. Sécurité

Le serveur ne fait jamais confiance à `organization_id` fourni par le client. L'organisation autorisée est déterminée à partir de l'utilisateur authentifié et de son appartenance à l'organisation. Les contrôles d'autorisation sont appliqués côté serveur avant toute écriture ou lecture.
