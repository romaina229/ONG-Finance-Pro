<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->integer('organization_id');
        abort_unless($organizationId > 0, 422, 'organization_id is required');

        $query = FinancialTransaction::query()
            ->where('organization_id', $organizationId)
            ->where('is_deleted', false);

        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        return response()->json($query->latest('id')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'local_id' => ['nullable', 'uuid'],
            'reference' => ['required', 'string', 'max:100', 'unique:financial_transactions,reference'],
            'type' => ['required', 'in:expense,revenue'],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'project_code' => ['nullable', 'string', 'max:100'],
        ]);

        $transaction = DB::transaction(fn () => FinancialTransaction::create($data + ['version' => 1]));

        return response()->json($transaction, 201);
    }
}
