<?php

declare(strict_types=1);

namespace Tests\Unit\Sync;

use App\Services\Sync\SyncOperation;
use PHPUnit\Framework\TestCase;

final class SyncOperationTest extends TestCase
{
    public function test_it_preserves_the_idempotency_and_versioning_contract(): void
    {
        $operation = new SyncOperation(
            operationId: '11111111-1111-4111-8111-111111111111',
            organizationId: '22222222-2222-4222-8222-222222222222',
            deviceId: '33333333-3333-4333-8333-333333333333',
            userId: '44444444-4444-4444-8444-444444444444',
            entityType: 'expense',
            localId: '55555555-5555-4555-8555-555555555555',
            operation: 'update',
            baseServerVersion: 7,
            payload: ['amount' => 150000],
            serverId: '66666666-6666-4666-8666-666666666666',
        );

        self::assertSame(7, $operation->baseServerVersion);
        self::assertSame('expense', $operation->entityType);
        self::assertSame('update', $operation->operation);
        self::assertSame('150000', (string) $operation->payload['amount']);
    }
}
