<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function financial(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $transactions = FinancialTransaction::where('organization_id', $organizationId)->where('is_deleted', false);
        $income = (clone $transactions)->where('type', 'revenue')->sum('amount');
        $expenses = (clone $transactions)->where('type', 'expense')->sum('amount');
        $budget = Project::where('organization_id', $organizationId)->sum('budget_amount');

        $byProject = Project::where('organization_id', $organizationId)->get()->map(fn (Project $project) => [
            'code' => $project->code,
            'name' => $project->name,
            'budget' => (float) $project->budget_amount,
            'spent' => (float) $project->spent_amount,
            'remaining' => max(0, (float) $project->budget_amount - (float) $project->spent_amount),
            'execution_rate' => $project->budget_amount > 0 ? round(((float) $project->spent_amount / (float) $project->budget_amount) * 100, 2) : 0,
        ]);

        return response()->json([
            'period' => ['from' => $request->query('from'), 'to' => $request->query('to')],
            'summary' => ['income' => (float) $income, 'expenses' => (float) $expenses, 'balance' => (float) $income - (float) $expenses, 'budget' => (float) $budget],
            'projects' => $byProject,
            'ledger' => (clone $transactions)->latest()->paginate(100),
        ]);
    }
}
