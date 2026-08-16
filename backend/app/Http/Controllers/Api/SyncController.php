<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncEngine;
use App\Services\Sync\SyncOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SyncController extends Controller
{
    public function __construct(private readonly SyncEngine $engine) {}

    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'operations' => ['required','array','min:1','max:100'],
            'operations.*.operation_id' => ['required','uuid'],
            'operations.*.organization_id' => ['required','uuid'],
            'operations.*.device_id' => ['required','uuid'],
            'operations.*.user_id' => ['required','uuid'],
            'operations.*.entity_type' => ['required','string','max:50'],
            'operations.*.local_id' => ['required','uuid'],
            'operations.*.server_id' => ['nullable','uuid'],
            'operations.*.operation' => ['required','in:insert,update,delete'],
            'operations.*.base_server_version' => ['nullable','integer','min:0'],
            'operations.*.payload' => ['nullable','array'],
        ]);

        $results = [];
        foreach ($data['operations'] as $item) {
            $results[] = $this->engine->push(new SyncOperation(
                operationId: $item['operation_id'],
                organizationId: $item['organization_id'],
                deviceId: $item['device_id'],
                userId: $item['user_id'],
                entityType: $item['entity_type'],
                localId: $item['local_id'],
                operation: $item['operation'],
                baseServerVersion: $item['base_server_version'] ?? null,
                payload: $item['payload'] ?? [],
                serverId: $item['server_id'] ?? null,
            ));
        }

        return response()->json(['data' => $results]);
    }

    public function pull(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organization_id' => ['required','uuid'],
            'device_id' => ['required','uuid'],
            'cursor' => ['nullable','integer','min:0'],
            'limit' => ['nullable','integer','min:1','max:500'],
        ]);

        $cursor = (int) ($data['cursor'] ?? 0);
        $limit = (int) ($data['limit'] ?? 200);
        $changes = DB::table('sync_changes')
            ->where('organization_id', $data['organization_id'])
            ->where('sequence', '>', $cursor)
            ->orderBy('sequence')
            ->limit($limit)
            ->get();

        $nextCursor = $changes->isEmpty() ? $cursor : (int) $changes->last()->sequence;

        return response()->json([
            'data' => $changes,
            'cursor' => $nextCursor,
            'has_more' => $changes->count() === $limit,
        ]);
    }
}
