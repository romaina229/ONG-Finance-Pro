<?php

declare(strict_types=1);

namespace App\Services\Sync;

use Illuminate\Database\ConnectionInterface;
use RuntimeException;

final class SyncEngine
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly SyncEntityHandler $handler,
    ) {}

    /**
     * Pushes one mutation. The operation id is the idempotency key.
     * Entity persistence is delegated to a whitelisted business handler.
     */
    public function push(SyncOperation $operation): array
    {
        return $this->db->transaction(function () use ($operation): array {
            $existing = $this->db->table('sync_operations')
                ->where('operation_id', $operation->operationId)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return json_decode($existing->response ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            }

            $currentVersion = $this->handler->currentVersion($operation);
            $baseVersion = $operation->baseServerVersion;

            if ($currentVersion !== null && $baseVersion !== $currentVersion) {
                $response = [
                    'status' => 'conflict',
                    'operation_id' => $operation->operationId,
                    'current_server_version' => $currentVersion,
                ];

                $this->db->table('sync_operations')->insert([
                    'operation_id' => $operation->operationId,
                    'organization_id' => $operation->organizationId,
                    'device_id' => $operation->deviceId,
                    'user_id' => $operation->userId,
                    'entity_type' => $operation->entityType,
                    'local_id' => $operation->localId,
                    'server_id' => $operation->serverId,
                    'operation' => $operation->operation,
                    'base_server_version' => $baseVersion,
                    'payload' => json_encode($operation->payload, JSON_THROW_ON_ERROR),
                    'status' => 'conflict',
                    'response' => json_encode($response, JSON_THROW_ON_ERROR),
                    'processed_at' => now(),
                ]);

                return $response;
            }

            $result = $this->handler->apply($operation);
            $serverVersion = ($currentVersion ?? 0) + 1;

            $response = [
                'status' => 'accepted',
                'operation_id' => $operation->operationId,
                'server_id' => $result['server_id'] ?? $operation->serverId,
                'server_version' => $serverVersion,
                'entity' => $result['entity'] ?? null,
            ];

            $this->db->table('sync_operations')->insert([
                'operation_id' => $operation->operationId,
                'organization_id' => $operation->organizationId,
                'device_id' => $operation->deviceId,
                'user_id' => $operation->userId,
                'entity_type' => $operation->entityType,
                'local_id' => $operation->localId,
                'server_id' => $response['server_id'],
                'operation' => $operation->operation,
                'base_server_version' => $baseVersion,
                'payload' => json_encode($operation->payload, JSON_THROW_ON_ERROR),
                'status' => 'accepted',
                'response' => json_encode($response, JSON_THROW_ON_ERROR),
                'processed_at' => now(),
            ]);

            $this->db->table('sync_changes')->insert([
                'organization_id' => $operation->organizationId,
                'entity_type' => $operation->entityType,
                'entity_id' => $response['server_id'],
                'operation_id' => $operation->operationId,
                'operation' => $operation->operation,
                'server_version' => $serverVersion,
                'payload' => json_encode($result['entity'] ?? null, JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);

            return $response;
        });
    }
}
