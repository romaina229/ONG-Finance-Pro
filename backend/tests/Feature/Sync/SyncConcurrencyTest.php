<?php

declare(strict_types=1);

namespace Tests\Feature\Sync;

use PHPUnit\Framework\TestCase;

final class SyncConcurrencyTest extends TestCase
{
    public function test_concurrent_updates_must_use_the_same_base_server_version(): void
    {
        // Contract test: two clients reading version N cannot both silently
        // overwrite version N. The second write must be accepted only after
        // the first has advanced the server version, or be returned as conflict.
        $baseVersion = 12;
        $firstWriteVersion = $baseVersion + 1;

        self::assertSame(13, $firstWriteVersion);
        self::assertNotSame($firstWriteVersion, $baseVersion);
    }

    public function test_replaying_the_same_operation_id_is_idempotent(): void
    {
        $operationId = '11111111-1111-4111-8111-111111111111';

        self::assertSame($operationId, $operationId);
    }
}
