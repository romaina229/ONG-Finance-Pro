# Finance Pro Backend

Le backend doit exposer le moteur de synchronisation via une couche de service transactionnelle. Le dépôt est actuellement au stade de fondation SQL : aucune implémentation Laravel n'est déclarée tant que le squelette Laravel réel n'est pas présent.

## Contrat minimal du service

`SyncEngine::push(SyncOperation $operation)` doit :

1. authentifier utilisateur et appareil ;
2. déterminer l'organisation autorisée côté serveur ;
3. vérifier l'idempotence de `operation_id` ;
4. verrouiller la version courante de l'entité ;
5. comparer `base_server_version` ;
6. accepter et incrémenter la version ou créer un conflit ;
7. journaliser la mutation ;
8. produire une entrée `sync_changes` ;
9. gérer les tombstones pour `delete` ;
10. retourner une réponse rejouable sans doublon.

`SyncEngine::pull(deviceId, organizationId, cursor)` doit retourner un flux ordonné par `sync_changes.sequence` et avancer le curseur uniquement après application transactionnelle côté client.

## Règle importante

Aucune classe Laravel fictive n'est ajoutée à ce stade : elle sera implémentée lorsque le squelette backend Laravel, ses migrations et son système d'authentification seront présents dans le dépôt. Cela évite de créer du code non raccordé au projet réel.
