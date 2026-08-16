<?php

declare(strict_types=1);

namespace App\Services\Sync;

final readonly class SyncOperation
{
    public function __construct(
        public string $operationId,
        public string $organizationId,
        public string $deviceId,
        public string $userId,
        public string $entityType,
        public string $localId,
        public ?string $serverId,
        public string $operation,
        public ?int $baseServerVersion,
        public array $payload,
    ) {}
}
