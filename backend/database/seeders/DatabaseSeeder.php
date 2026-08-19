<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BudgetLine;
use App\Models\FinancialTransaction;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'Consulter le tableau de bord', 'slug' => 'dashboard.view'],
            ['name' => 'Gérer les projets', 'slug' => 'projects.manage'],
            ['name' => 'Gérer les budgets', 'slug' => 'budgets.manage'],
            ['name' => 'Créer des transactions', 'slug' => 'transactions.create'],
            ['name' => 'Approuver les dépenses', 'slug' => 'transactions.approve'],
            ['name' => 'Rapprocher les recettes', 'slug' => 'transactions.reconcile'],
            ['name' => 'Consulter les rapports', 'slug' => 'reports.view'],
            ['name' => 'Administrer les utilisateurs', 'slug' => 'access.manage'],
        ])->mapWithKeys(fn ($data) => [$data['slug'] => Permission::updateOrCreate(['slug' => $data['slug']], $data)]);

        $adminRole = Role::updateOrCreate(['slug' => 'administrator'], ['name' => 'Administrateur', 'description' => 'Accès complet à l’organisation']);
        $financeRole = Role::updateOrCreate(['slug' => 'finance-manager'], ['name' => 'Responsable financier', 'description' => 'Gestion financière, budgets et rapports']);
        $accountantRole = Role::updateOrCreate(['slug' => 'accountant'], ['name' => 'Comptable', 'description' => 'Saisie, contrôle et rapprochement']);
        $adminRole->permissions()->sync($permissions->keys()->map(fn ($key) => $permissions[$key]->id)->all());
        $financeRole->permissions()->sync($permissions->except('access.manage')->keys()->map(fn ($key) => $permissions[$key]->id)->all());
        $accountantRole->permissions()->sync($permissions->only(['dashboard.view', 'transactions.create', 'transactions.reconcile', 'reports.view'])->keys()->map(fn ($key) => $permissions[$key]->id)->all());

        $org = Organization::updateOrCreate(['code' => 'ONG-DEMO'], ['name' => 'Mon ONG', 'currency' => 'XOF', 'fiscal_year' => 2026]);
        $admin = User::updateOrCreate(['email' => 'admin@financepro.local'], ['name' => 'Romain Administrateur', 'password' => 'Password!123']);
        $admin->organizations()->syncWithoutDetaching([$org->id => ['role_id' => $adminRole->id]]);

        $projects = [
            ['code' => 'PROJ-2026-001', 'name' => 'Autonomisation des jeunes', 'donor' => 'Global Fund', 'budget_amount' => 24500000, 'spent_amount' => 11250000, 'status' => 'active'],
            ['code' => 'PROJ-2026-002', 'name' => 'Santé communautaire', 'donor' => 'UNFPA', 'budget_amount' => 18000000, 'spent_amount' => 6800000, 'status' => 'active'],
            ['code' => 'PROJ-2026-003', 'name' => 'Renforcement institutionnel', 'donor' => 'Fonds propre', 'budget_amount' => 9500000, 'spent_amount' => 2900000, 'status' => 'draft'],
        ];
        foreach ($projects as $data) {
            $project = Project::updateOrCreate(['organization_id' => $org->id, 'code' => $data['code']], $data + ['organization_id' => $org->id]);
            BudgetLine::updateOrCreate(['project_id' => $project->id, 'code' => 'ACT'], ['category' => 'Activités terrain', 'allocated_amount' => $project->budget_amount * .5, 'committed_amount' => $project->spent_amount, 'spent_amount' => $project->spent_amount]);
        }

        FinancialTransaction::updateOrCreate(['reference' => 'REC-2026-001'], ['organization_id' => $org->id, 'local_id' => Str::uuid(), 'type' => 'revenue', 'label' => 'Subvention Global Fund', 'amount' => 4500000, 'currency' => 'XOF', 'project_code' => 'PROJ-2026-001', 'source' => 'Global Fund', 'tranche' => 'Tranche 1', 'workflow_status' => 'reconciled', 'status' => 'received', 'created_by' => $admin->id, 'reconciled_at' => now(), 'version' => 1]);
        FinancialTransaction::updateOrCreate(['reference' => 'DEP-2026-001'], ['organization_id' => $org->id, 'local_id' => Str::uuid(), 'type' => 'expense', 'label' => 'Achat fournitures', 'amount' => 245000, 'currency' => 'XOF', 'project_code' => 'PROJ-2026-001', 'workflow_status' => 'submitted', 'status' => 'pending', 'created_by' => $admin->id, 'submitted_at' => now(), 'version' => 1]);
    }
}
