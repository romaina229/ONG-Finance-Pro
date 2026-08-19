<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetLine;
use App\Models\FinancialTransaction;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $query = FinancialTransaction::with(['project', 'budgetLine', 'creator'])->where('organization_id', $organizationId)->where('is_deleted', false);
        if ($type = $request->string('type')->toString()) $query->where('type', $type);
        if ($status = $request->string('workflow_status')->toString()) $query->where('workflow_status', $status);
        return response()->json($query->latest('id')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $data = $request->validate([
            'reference' => ['nullable', 'string', 'max:100', 'unique:financial_transactions,reference'],
            'type' => ['required', 'in:expense,revenue'], 'label' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3'], 'project_id' => ['nullable', 'integer', 'exists:projects,id'], 'budget_line_id' => ['nullable', 'integer', 'exists:budget_lines,id'],
            'project_code' => ['nullable', 'string', 'max:100'], 'source' => ['nullable', 'string', 'max:255'], 'tranche' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'], 'document_url' => ['nullable', 'url', 'max:2048'],
        ]);
        if (!empty($data['project_id'])) abort_unless(Project::where('organization_id', $organizationId)->whereKey($data['project_id'])->exists(), 422, 'Projet hors organisation.');
        if (!empty($data['budget_line_id'])) abort_unless(BudgetLine::whereKey($data['budget_line_id'])->whereHas('project', fn ($q) => $q->where('organization_id', $organizationId))->exists(), 422, 'Ligne budgétaire invalide.');
        $transaction = DB::transaction(fn () => FinancialTransaction::create($data + ['organization_id' => $organizationId, 'reference' => $data['reference'] ?? ('OP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5))), 'currency' => $data['currency'] ?? 'XOF', 'created_by' => $request->user()->id, 'workflow_status' => 'draft', 'status' => 'pending', 'version' => 1]));
        return response()->json($transaction->load(['project', 'budgetLine', 'creator']), 201);
    }

    public function approve(Request $request, FinancialTransaction $transaction): JsonResponse
    {
        abort_unless($transaction->organization_id === (int) $request->attributes->get('organization_id'), 404);
        abort_unless($transaction->type === 'expense', 422, 'Seules les dépenses peuvent être approuvées dans ce workflow.');
        abort_unless($transaction->workflow_status === 'submitted', 422, 'La transaction doit être soumise avant approbation.');
        abort_unless((bool) $transaction->document_url, 422, 'Une pièce justificative est obligatoire avant approbation.');
        abort_unless((bool) $transaction->project_id, 422, 'La dépense doit être rattachée à un projet.');
        if ($transaction->budget_line_id) {
            $line = BudgetLine::lockForUpdate()->findOrFail($transaction->budget_line_id);
            $available = (float) $line->allocated_amount - (float) $line->committed_amount;
            abort_if((float) $transaction->amount > $available, 422, 'Le budget disponible est insuffisant pour cette dépense.');
            DB::transaction(function () use ($transaction, $line, $request): void {
                $line->increment('committed_amount', $transaction->amount);
                $transaction->update(['workflow_status' => 'approved', 'status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now(), 'version' => $transaction->version + 1]);
            });
        } else {
            $transaction->update(['workflow_status' => 'approved', 'status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now(), 'version' => $transaction->version + 1]);
        }
        return response()->json($transaction->fresh()->load(['project', 'budgetLine', 'creator', 'approver']));
    }

    public function reconcile(Request $request, FinancialTransaction $transaction): JsonResponse
    {
        abort_unless($transaction->organization_id === (int) $request->attributes->get('organization_id'), 404);
        abort_unless($transaction->type === 'revenue', 422, 'Seules les recettes peuvent être rapprochées.');
        abort_unless((bool) $transaction->document_url, 422, 'Une pièce justificative est obligatoire avant rapprochement.');
        $transaction->update(['workflow_status' => 'reconciled', 'status' => 'received', 'reconciled_at' => now(), 'version' => $transaction->version + 1]);
        return response()->json($transaction->fresh()->load(['project', 'creator']));
    }

    public function submit(Request $request, FinancialTransaction $transaction): JsonResponse
    {
        abort_unless($transaction->organization_id === (int) $request->attributes->get('organization_id'), 404);
        abort_unless($transaction->workflow_status === 'draft', 422, 'Transaction déjà soumise.');
        $transaction->update(['workflow_status' => 'submitted', 'status' => 'pending', 'submitted_at' => now(), 'version' => $transaction->version + 1]);
        return response()->json($transaction->fresh()->load(['project', 'budgetLine', 'creator']));
    }
}
