<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_creates_transaction_and_is_idempotent(): void
    {
        $organization = Organization::create(['name' => 'Test ONG', 'code' => 'TEST-001']);
        $operationId = (string) Str::uuid();
        $localId = (string) Str::uuid();

        $payload = $this->operation($organization->id, $operationId, $localId);

        $this->postJson('/api/sync/push', ['operations' => [$payload]])
            ->assertOk()
            ->assertJsonPath('data.0.status', 'accepted');

        $this->postJson('/api/sync/push', ['operations' => [$payload]])
            ->assertOk()
            ->assertJsonPath('data.0.status', 'accepted');

        $this->assertDatabaseCount('financial_transactions', 1);
        $this->assertDatabaseCount('sync_operations', 1);
    }

    public function test_update_with_stale_version_is_marked_as_conflict(): void
    {
        $organization = Organization::create(['name' => 'Test ONG', 'code' => 'TEST-002']);
        $transaction = FinancialTransaction::create([
            'organization_id' => $organization->id,
            'local_id' => (string) Str::uuid(),
            'reference' => 'DEP-TEST-001',
            'type' => 'expense',
            'label' => 'Fournitures',
            'amount' => 1000,
            'currency' => 'XOF',
            'version' => 2,
        ]);

        $response = $this->postJson('/api/sync/push', ['operations' => [[
            'operation_id' => (string) Str::uuid(),
            'organization_id' => $organization->id,
            'entity_type' => 'financial_transaction',
            'local_id' => $transaction->local_id,
            'server_id' => $transaction->id,
            'action' => 'update',
            'base_version' => 1,
            'payload' => ['label' => 'Modification offline'],
        ]]]);

        $response->assertOk()
            ->assertJsonPath('data.0.status', 'conflict')
            ->assertJsonPath('data.0.server_version', 2);
    }

    public function test_delete_increments_version_and_soft_deletes_transaction(): void
    {
        $organization = Organization::create(['name' => 'Test ONG', 'code' => 'TEST-003']);
        $transaction = FinancialTransaction::create([
            'organization_id' => $organization->id,
            'local_id' => (string) Str::uuid(),
            'reference' => 'DEP-TEST-002',
            'type' => 'expense',
            'label' => 'Transport',
            'amount' => 2500,
            'currency' => 'XOF',
            'version' => 1,
        ]);

        $this->postJson('/api/sync/push', ['operations' => [[
            'operation_id' => (string) Str::uuid(),
            'organization_id' => $organization->id,
            'entity_type' => 'financial_transaction',
            'local_id' => $transaction->local_id,
            'server_id' => $transaction->id,
            'action' => 'delete',
            'base_version' => 1,
            'payload' => [],
        ]]])
            ->assertOk()
            ->assertJsonPath('data.0.status', 'accepted');

        $this->assertDatabaseHas('financial_transactions', [
            'id' => $transaction->id,
            'is_deleted' => true,
            'version' => 2,
        ]);
    }

    private function operation(int $organizationId, string $operationId, string $localId): array
    {
        return [
            'operation_id' => $operationId,
            'organization_id' => $organizationId,
            'entity_type' => 'financial_transaction',
            'local_id' => $localId,
            'action' => 'create',
            'payload' => [
                'reference' => 'DEP-TEST-0001',
                'type' => 'expense',
                'label' => 'Test fournitures',
                'amount' => 5000,
                'currency' => 'XOF',
            ],
        ];
    }
}
