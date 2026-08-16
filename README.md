# Finance Pro

Plateforme de gestion financière et comptable Offline First destinée aux ONG et organisations.

## Architecture

- `frontend/` — interface web React + TypeScript
- `backend/` — API Laravel + PostgreSQL
- `desktop/` — application desktop Electron + moteur Offline First
- `mobile/` — application mobile
- `docs/` — cahier des charges, architecture et spécifications

## Principes

1. Offline First : l'utilisateur peut travailler sans connexion.
2. Synchronisation fiable : les données locales sont synchronisées lorsque le réseau revient.
3. Multi-organisation : isolation stricte des données par organisation.
4. Traçabilité : audit des opérations financières et des synchronisations.
5. Multi-devise et multi-bailleur.
6. Référentiel comptable adapté au contexte SYSCOHADA.

## Nom produit

Le nom produit officiel est **Finance Pro**. Le nom initial `ONG Finance Pro` ne doit plus apparaître dans l'interface utilisateur ni dans la documentation produit, sauf dans l'historique technique si nécessaire.
