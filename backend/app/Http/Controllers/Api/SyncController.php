<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'operations' => ['required', 'array', 'min:1'],
            'operations.*.operation_id' => ['required', 'uuid'],
            'operations.*.organization_id' => ['required', 'uuid'],
            'operations.*.entity_type' => ['required', 'string', 'max:100'],
            'operations.*.local_id' => ['required', 'uuid'],
            'operations.*.server_id' => ['nullable', 'uuid'],
            'operations.*.action' => ['required', 'in:create,update,delete'],
            'operations.*.base_version' => ['nullable', 'integer', 'min:0'],
            'operations.*.payload' => ['required', 'array'],
        ]);

        $accepted = [];

        DB::transaction(function () use ($data, &$accepted) {
            foreach ($data['operations'] as $item) {
                $operation = SyncOperation::firstOrCreate(
                    ['operation_id' => $item['operation_id']],
                    array_merge($item, ['status' => 'pending'])
                );

                if ($operation->status === 'pending') {
                    $operation->update([
                        'status' => 'accepted',
                        'processed_at' => now(),
                    ]);
                }

                $accepted[] = [
                    'operation_id' => $operation->operation_id,
                    'status' => $operation->status,
                    'local_id' => $operation->local_id,
                    'server_id' => $operation->server_id,
                ];
            }
        });

        return response()->json(['data' => $accepted]);
    }

    public function pull(Request $request): JsonResponse
    {
        $organizationId = $request->validate([
            'organization_id' => ['required', 'uuid'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $query = SyncOperation::query()
            ->where('organization_id', $organizationId['organization_id'])
            ->where('status', 'accepted')
            ->orderBy('id');

        if (!empty($organizationId['after_id'])) {
            $query->where('id', '>', $organizationId['after_id']);
        }

        return response()->json(['data' => $query->limit(100)->get()]);
    }
}
