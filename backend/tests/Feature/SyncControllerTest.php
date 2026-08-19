<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_creates_transaction_and_is_idempotent(): void
    {
        [$user, $organization] = $this->context();
        $operationId = (string) Str::uuid(); $localId = (string) Str::uuid(); $payload = $this->operation($organization->id, $operationId, $localId);
        $this->actingAs($user, 'sanctum')->withHeader('X-Organization-Id', (string) $organization->id)->postJson('/api/sync/push', ['operations' => [$payload]])->assertOk()->assertJsonPath('data.0.status', 'accepted');
        $this->actingAs($user, 'sanctum')->withHeader('X-Organization-Id', (string) $organization->id)->postJson('/api/sync/push', ['operations' => [$payload]])->assertOk()->assertJsonPath('data.0.status', 'accepted');
        $this->assertDatabaseCount('financial_transactions', 1); $this->assertDatabaseCount('sync_operations', 1);
    }

    public function test_update_with_stale_version_is_marked_as_conflict(): void
    {
        [$user, $organization] = $this->context();
        $transaction = FinancialTransaction::create(['organization_id' => $organization->id, 'local_id' => (string) Str::uuid(), 'reference' => 'DEP-TEST-001', 'type' => 'expense', 'label' => 'Fournitures', 'amount' => 1000, 'currency' => 'XOF', 'version' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('X-Organization-Id', (string) $organization->id)->postJson('/api/sync/push', ['operations' => [['operation_id' => (string) Str::uuid(), 'organization_id' => $organization->id, 'entity_type' => 'financial_transaction', 'local_id' => $transaction->local_id, 'server_id' => $transaction->id, 'action' => 'update', 'base_version' => 1, 'payload' => ['label' => 'Modification offline']]]]);
        $response->assertOk()->assertJsonPath('data.0.status', 'conflict')->assertJsonPath('data.0.server_version', 2);
    }

    public function test_delete_increments_version_and_soft_deletes_transaction(): void
    {
        [$user, $organization] = $this->context();
        $transaction = FinancialTransaction::create(['organization_id' => $organization->id, 'local_id' => (string) Str::uuid(), 'reference' => 'DEP-TEST-002', 'type' => 'expense', 'label' => 'Transport', 'amount' => 2500, 'currency' => 'XOF', 'version' => 1]);
        $this->actingAs($user, 'sanctum')->withHeader('X-Organization-Id', (string) $organization->id)->postJson('/api/sync/push', ['operations' => [['operation_id' => (string) Str::uuid(), 'organization_id' => $organization->id, 'entity_type' => 'financial_transaction', 'local_id' => $transaction->local_id, 'server_id' => $transaction->id, 'action' => 'delete', 'base_version' => 1, 'payload' => []]]])->assertOk()->assertJsonPath('data.0.status', 'accepted');
        $this->assertDatabaseHas('financial_transactions', ['id' => $transaction->id, 'is_deleted' => true, 'version' => 2]);
    }

    private function context(): array
    {
        $organization = Organization::create(['name' => 'Test ONG', 'code' => 'TEST-'.Str::upper(Str::random(5))]);
        $role = Role::create(['name' => 'Test', 'slug' => 'test-'.Str::uuid()]);
        $permission = Permission::create(['name' => 'Sync', 'slug' => 'transactions.create']); $role->permissions()->attach($permission->id);
        $user = User::create(['name' => 'Test User', 'email' => Str::uuid().'@finance-pro.local', 'password' => 'Password!123']);
        $user->organizations()->attach($organization->id, ['role_id' => $role->id]);
        return [$user, $organization];
    }

    private function operation(int $organizationId, string $operationId, string $localId): array
    {
        return ['operation_id' => $operationId, 'organization_id' => $organizationId, 'entity_type' => 'financial_transaction', 'local_id' => $localId, 'action' => 'create', 'payload' => ['reference' => 'DEP-TEST-'.Str::upper(Str::random(5)), 'type' => 'expense', 'label' => 'Test fournitures', 'amount' => 5000, 'currency' => 'XOF']];
    }
}
