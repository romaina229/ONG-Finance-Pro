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
        $organizationId = (int) $request->attributes->get('organization_id');
        $data = $request->validate([
            'operations' => ['required', 'array', 'min:1', 'max:100'],
            'operations.*.operation_id' => ['required', 'uuid'],
            'operations.*.organization_id' => ['required', 'integer'],
            'operations.*.entity_type' => ['required', 'in:financial_transaction'],
            'operations.*.local_id' => ['required', 'uuid'],
            'operations.*.server_id' => ['nullable', 'integer'],
            'operations.*.action' => ['required', 'in:create,update,delete'],
            'operations.*.base_version' => ['nullable', 'integer', 'min:0'],
            'operations.*.payload' => ['required', 'array'],
        ]);

        foreach ($data['operations'] as $item) {
            abort_unless((int) $item['organization_id'] === $organizationId, 403, 'Organization mismatch.');
        }

        $results = [];
        DB::transaction(function () use ($data, &$results): void {
            foreach ($data['operations'] as $item) {
                $existing = SyncOperation::where('operation_id', $item['operation_id'])->first();
                if ($existing) {
                    $results[] = ['operation_id' => $existing->operation_id, 'status' => $existing->status, 'server_id' => $existing->server_id];
                    continue;
                }
                $operation = SyncOperation::create($item + ['status' => 'pending']);
                $results[] = $this->applyOperation($operation);
            }
        });
        return response()->json(['data' => $results]);
    }

    private function applyOperation(SyncOperation $operation): array
    {
        $payload = $operation->payload;
        if ($operation->action === 'create') {
            $transaction = FinancialTransaction::firstOrCreate(['local_id' => $operation->local_id], $this->transactionPayload($operation));
            $operation->update(['server_id' => $transaction->id, 'status' => 'accepted', 'processed_at' => now()]);
            return ['operation_id' => $operation->operation_id, 'status' => 'accepted', 'server_id' => $transaction->id];
        }

        $transaction = FinancialTransaction::where('organization_id', $operation->organization_id)->lockForUpdate()->find($operation->server_id);
        if (!$transaction) {
            $operation->update(['status' => 'conflict', 'error_message' => 'Server record not found', 'processed_at' => now()]);
            return ['operation_id' => $operation->operation_id, 'status' => 'conflict', 'server_id' => null];
        }
        if ($operation->base_version !== null && $transaction->version !== $operation->base_version) {
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
        $allowed = ['organization_id', 'local_id', 'reference', 'type', 'label', 'amount', 'currency', 'project_code', 'source', 'tranche', 'description', 'document_url', 'status', 'workflow_status', 'is_deleted'];
        return array_intersect_key($operation->payload, array_flip($allowed)) + ['organization_id' => $operation->organization_id, 'local_id' => $operation->local_id, 'version' => 1];
    }

    public function pull(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $afterId = (int) $request->query('after_id', 0);
        $query = SyncOperation::query()->where('organization_id', $organizationId)->where('status', 'accepted')->where('id', '>', $afterId)->orderBy('id');
        return response()->json(['data' => $query->limit(100)->get()]);
    }
}
