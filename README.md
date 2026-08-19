# Finance Pro

Plateforme de gestion financière et comptable destinée aux ONG et organisations à but non lucratif.

## Architecture

- `frontend/` : React 19 + Vite, interface Finance Pro.
- `backend/` : Laravel 12 + Sanctum, API REST.
- Base locale : SQLite pour démarrer rapidement.
- Production : PostgreSQL ou MySQL selon l'infrastructure.

## Démarrage du backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Compte de démonstration :

- E-mail : `admin@financepro.local`
- Mot de passe : `Password!123`

API : `http://127.0.0.1:8000/api`

Contrôle de santé : `GET /api/health`

## Démarrage du frontend

Dans un deuxième terminal :

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Interface : `http://localhost:5173`

## Modules

- Tableau de bord exécutif
- Organisations
- Utilisateurs, rôles et permissions
- Projets et portefeuille budgétaire
- Contrôle budgétaire
- Dépenses et workflow d'approbation
- Recettes, financements et subventions
- Rapprochement bancaire
- Journal comptable
- Rapports financiers
- Synchronisation offline avec gestion des versions et conflits

## Flux financier cible

`Financement → Recette → Rapprochement → Comptabilité → Reporting`

`Projet → Budget → Demande de dépense → Contrôle → Approbation → Comptabilité`

Le workflow CI est conservé en mode manuel pendant la phase de construction. Il pourra être lancé explicitement une fois la plateforme stabilisée.
