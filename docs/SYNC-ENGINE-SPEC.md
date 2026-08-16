# Finance Pro — Spécification du moteur de synchronisation

## Objectif

Garantir qu'un utilisateur peut travailler sans réseau puis synchroniser ses opérations sans perte, doublon ni écrasement silencieux.

## Contrat d'opération

Chaque mutation synchronisable transporte :

- `operation_id` : UUID global unique et idempotent ;
- `device_id` ;
- `organization_id` ;
- `entity_type` ;
- `local_id` ;
- `server_id` si connu ;
- `operation` : insert/update/delete ;
- `base_server_version` : dernière version connue par le client ;
- `payload` ;
- `client_updated_at`.

## Traitement serveur

Une opération est traitée dans une transaction :

1. authentifier l'utilisateur et le device ;
2. déterminer l'organisation à partir de l'appartenance autorisée ;
3. vérifier l'idempotence de `operation_id` ;
4. vérifier que l'entité appartient à l'organisation ;
5. comparer `base_server_version` à la version courante ;
6. accepter et incrémenter la version si elles correspondent ;
7. retourner un conflit si la version distante est différente ;
8. enregistrer l'opération dans l'audit ;
9. retourner `server_id`, `server_version` et l'état final.

## Idempotence

Une même `operation_id` rejouée après un timeout doit retourner le même résultat logique et ne doit jamais créer une seconde écriture métier.

## Pull

Le serveur expose un curseur monotone de changements. Le client demande les changements après son curseur connu. L'application des changements locaux se fait dans une transaction SQLite.

## Suppressions

Les suppressions sont représentées par des tombstones côté serveur afin que les appareils déconnectés puissent recevoir l'information de suppression ultérieurement. Le nettoyage des tombstones est différé jusqu'à ce qu'une politique de rétention sûre soit définie.

## Conflits

Une modification concurrente ne doit jamais être résolue silencieusement par « dernier arrivé ». Le serveur retourne les deux versions. La résolution peut être `server_wins`, `client_wins` ou `manual`, selon les règles du module métier.

## Priorités

1. écritures comptables et approbations ;
2. référentiels et métadonnées ;
3. documents et médias ;
4. tâches non critiques.

## Reprise

Les opérations échouées restent persistées localement. Le retry utilise une stratégie progressive avec limite configurable. Une erreur de validation métier ne doit pas être réessayée indéfiniment : elle passe en `error` et doit être présentée à l'utilisateur.
