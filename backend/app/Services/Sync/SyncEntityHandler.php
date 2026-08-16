<?php

declare(strict_types=1);

namespace App\Services\Sync;

interface SyncEntityHandler
{
    /**
     * Applies a mutation while the caller owns the synchronization transaction.
     * Returns the authoritative server id and serialized state.
     */
    public function apply(SyncOperation $operation): array;

    /** Returns the current authoritative version of the entity, or null for a new entity. */
    public function currentVersion(SyncOperation $operation): ?int;
}
