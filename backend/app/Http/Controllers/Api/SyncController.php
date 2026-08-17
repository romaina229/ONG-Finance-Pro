<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
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
            'operations.*.organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'operations.*.entity_type' => ['required', 'in:financial_transaction'],
            'operations.*.local_id' => ['required', 'uuid'],
            'operations.*.server_id' => ['nullable', 'integer', 'exists:financial_transactions,id'],
            'operations.*.action' => ['required', 'in:create,update,delete'],
            'operations.*.base_version' => ['nullable', 'integer', 'min:0'],
            'operations.*.payload' => ['required', 'array'],
        ]);

        $results = [];

        DB::transaction(function () use ($data, &$results): void {
            foreach ($data['operations'] as $item) {
                $existing = SyncOperation::where('operation_id', $item['operation_id'])->first();
                if ($existing) {
                    $results[] = [
                        'operation_id' => $existing->operation_id,
                        'status' => $existing->status,
                        'server_id' => $existing->server_id,
                    ];
                    continue;
                }

                $operation = SyncOperation::create($item + ['status' => 'pending']);
                $result = $this->applyOperation($operation);
                $results[] = $result;
            }
        });

        return response()->json(['data' => $results]);
    }

    private function applyOperation(SyncOperation $operation): array
    {
        $payload = $operation->payload;
        $serverId = $operation->server_id;
        $baseVersion = $operation->base_version;

        if ($operation->action === 'create') {
            $transaction = FinancialTransaction::firstOrCreate(
                ['local_id' => $operation->local_id],
                $this->transactionPayload($operation)
            );
            $operation->update([
                'server_id' => $transaction->id,
                'status' => 'accepted',
                'processed_at' => now(),
            ]);
            return ['operation_id' => $operation->operation_id, 'status' => 'accepted', 'server_id' => $transaction->id];
        }

        $transaction = FinancialTransaction::lockForUpdate()->find($serverId);
        if (!$transaction) {
            $operation->update(['status' => 'conflict', 'error_message' => 'Server record not found', 'processed_at' => now()]);
            return ['operation_id' => $operation->operation_id, 'status' => 'conflict', 'server_id' => null];
        }

        if ($baseVersion !== null && $transaction->version !== $baseVersion) {
            $operation->update(['status' => 'conflict', 'error_message' => 'Version conflict', 'processed_at' => now()]);
            return ['operation_id' => $operation->operation_id, 'status' => 'conflict', 'server_id' => $transaction->id, 'server_version' => $transaction->version];
        }

        if ($operation->action === 'delete') {
            $transaction->update(['is_deleted' => true, 'version' => $transaction->version + 1]);
        } else {
            $transaction->fill($this->transactionPayload($operation));
            $transaction->version++;
            $transaction->save();
        }

        $operation->update(['status' => 'accepted', 'processed_at' => now()]);
        return ['operation_id' => $operation->operation_id, 'status' => 'accepted', 'server_id' => $transaction->id, 'server_version' => $transaction->version];
    }

    private function transactionPayload(SyncOperation $operation): array
    {
        $allowed = ['organization_id', 'local_id', 'reference', 'type', 'label', 'amount', 'currency', 'project_code', 'status', 'is_deleted'];
        return array_intersect_key($operation->payload, array_flip($allowed)) + [
            'organization_id' => $operation->organization_id,
            'local_id' => $operation->local_id,
            'version' => 1,
        ];
    }

    public function pull(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $query = SyncOperation::query()
            ->where('organization_id', $data['organization_id'])
            ->where('status', 'accepted')
            ->orderBy('id');

        if (!empty($data['after_id'])) {
            $query->where('id', '>', $data['after_id']);
        }

        return response()->json(['data' => $query->limit(100)->get()]);
    }
}
