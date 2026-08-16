<?php

declare(strict_types=1);

namespace App\Services\Sync;

use Illuminate\Database\ConnectionInterface;

final class SyncEngine
{
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly SyncEntityHandler $handler,
    ) {}

    /**
     * Pushes one mutation transactionally. The operation id is the idempotency key.
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

            // PostgreSQL transaction advisory lock prevents two devices from
            // concurrently creating/updating the same synchronization version.
            $entityKey = $operation->serverId ?? $operation->localId;
            $this->db->selectOne(
                'SELECT pg_advisory_xact_lock(hashtextextended(?, 0))',
                [$operation->organizationId . ':' . $operation->entityType . ':' . $entityKey]
            );

            $versionRow = $this->db->table('sync_versions')
                ->where('organization_id', $operation->organizationId)
                ->where('entity_type', $operation->entityType)
                ->where('entity_id', $entityKey)
                ->lockForUpdate()
                ->first();

            $currentVersion = $versionRow?->version;
            $baseVersion = $operation->baseServerVersion;

            if ($operation->operation !== 'insert' && $currentVersion === null) {
                return $this->recordRejected($operation, 'entity_version_not_found');
            }

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

                $this->db->table('sync_conflicts')->insert([
                    'organization_id' => $operation->organizationId,
                    'entity_type' => $operation->entityType,
                    'entity_id' => $entityKey,
                    'local_version' => json_encode($operation->payload, JSON_THROW_ON_ERROR),
                    'server_version' => json_encode(['version' => $currentVersion], JSON_THROW_ON_ERROR),
                    'device_id' => $operation->deviceId,
                    'user_id' => $operation->userId,
                    'detected_at' => now(),
                ]);

                return $response;
            }

            $result = $this->handler->apply($operation);
            $serverId = $result['server_id'] ?? $operation->serverId ?? $operation->localId;
            $serverVersion = ($currentVersion ?? 0) + 1;

            $this->db->table('sync_versions')->updateOrInsert(
                [
                    'entity_type' => $operation->entityType,
                    'entity_id' => $serverId,
                ],
                [
                    'organization_id' => $operation->organizationId,
                    'version' => $serverVersion,
                    'updated_at' => now(),
                ]
            );

            $response = [
                'status' => 'accepted',
                'operation_id' => $operation->operationId,
                'server_id' => $serverId,
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
                'server_id' => $serverId,
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
                'entity_id' => $serverId,
                'operation_id' => $operation->operationId,
                'operation' => $operation->operation,
                'server_version' => $serverVersion,
                'payload' => json_encode($result['entity'] ?? null, JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);

            if ($operation->operation === 'delete') {
                $this->db->table('sync_tombstones')->updateOrInsert(
                    [
                        'organization_id' => $operation->organizationId,
                        'entity_type' => $operation->entityType,
                        'entity_id' => $serverId,
                    ],
                    [
                        'server_version' => $serverVersion,
                        'deleted_at' => now(),
                    ]
                );
            }

            return $response;
        });
    }

    private function recordRejected(SyncOperation $operation, string $reason): array
    {
        $response = [
            'status' => 'rejected',
            'operation_id' => $operation->operationId,
            'reason' => $reason,
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
            'base_server_version' => $operation->baseServerVersion,
            'payload' => json_encode($operation->payload, JSON_THROW_ON_ERROR),
            'status' => 'rejected',
            'response' => json_encode($response, JSON_THROW_ON_ERROR),
            'processed_at' => now(),
        ]);

        return $response;
    }
}
