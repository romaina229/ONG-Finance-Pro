# Finance Pro

Plateforme de gestion financière et comptable destinée aux ONG et organisations à but non lucratif.

## Architecture

- `frontend/` : React 19 + Vite, interface Finance Pro.
- `backend/` : Laravel 12 + Sanctum, API REST.
- Base locale : SQLite pour démarrer rapidement.
- Production : PostgreSQL ou MySQL selon l'infrastructure.

## Démarrage du backend — Windows PowerShell

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Démarrage du backend — macOS / Linux

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Compte de démonstration : `admin@financepro.local` / `Password!123`.

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

Sous PowerShell, remplacez `cp` par `Copy-Item .env.example .env`.

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
- Gestion documentaire prête pour le stockage local/public

## Flux financier

`Financement → Recette → Rapprochement → Comptabilité → Reporting`

`Projet → Budget → Demande de dépense → Contrôle → Approbation → Comptabilité`

Le workflow CI est conservé en mode manuel pendant la phase de construction et sera lancé explicitement après stabilisation.
