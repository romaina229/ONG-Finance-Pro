<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BudgetLine;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_project_and_budget_line(): void
    {
        [$user, $organization] = $this->context();
        $headers = ['X-Organization-Id' => (string) $organization->id];
        $project = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/projects', ['code' => 'PROJ-TEST-001', 'name' => 'Projet de test', 'donor' => 'Bailleur', 'budget_amount' => 1000000, 'status' => 'active'])->assertCreated()->json();
        $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/budgets', ['project_id' => $project['id'], 'code' => 'ACT-001', 'category' => 'Activités', 'allocated_amount' => 500000])->assertCreated()->assertJsonPath('project.id', $project['id']);
    }

    public function test_authenticated_user_can_submit_and_approve_an_expense(): void
    {
        [$user, $organization] = $this->context();
        $headers = ['X-Organization-Id' => (string) $organization->id];
        $project = Project::create(['organization_id' => $organization->id, 'code' => 'PROJ-APPROVAL', 'name' => 'Projet approbation', 'budget_amount' => 1000000, 'spent_amount' => 0, 'status' => 'active']);
        $line = BudgetLine::create(['project_id' => $project->id, 'code' => 'ACT-APPROVAL', 'category' => 'Activités', 'allocated_amount' => 500000, 'committed_amount' => 0, 'spent_amount' => 0]);
        $transaction = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/transactions', ['type' => 'expense', 'label' => 'Fournitures', 'amount' => 25000, 'currency' => 'XOF', 'project_id' => $project->id, 'budget_line_id' => $line->id, 'document_url' => 'https://example.com/facture.pdf'])->assertCreated()->json();
        $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/transactions/'.$transaction['id'].'/submit')->assertOk()->assertJsonPath('workflow_status', 'submitted');
        $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/transactions/'.$transaction['id'].'/approve')->assertOk()->assertJsonPath('workflow_status', 'approved');
        $this->assertDatabaseHas('budget_lines', ['id' => $line->id, 'committed_amount' => 25000]);
    }

    private function context(): array
    {
        $organization = Organization::create(['name' => 'Test ONG', 'code' => 'API-'.substr(md5((string) microtime(true)), 0, 8)]);
        $role = Role::create(['name' => 'Test API', 'slug' => 'api-'.substr(md5((string) microtime(true)), 0, 10)]);
        $user = User::create(['name' => 'API User', 'email' => uniqid('api-', true).'@finance-pro.local', 'password' => 'Password!123']);
        $user->organizations()->attach($organization->id, ['role_id' => $role->id]);
        return [$user, $organization];
    }
}
