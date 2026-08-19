<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $base = FinancialTransaction::query()->where('organization_id', $organizationId)->where('is_deleted', false);
        $income = (clone $base)->where('type', 'revenue')->sum('amount');
        $expenses = (clone $base)->where('type', 'expense')->sum('amount');
        $budget = Project::where('organization_id', $organizationId)->sum('budget_amount');
        $periodExpression = FinancialTransaction::getConnection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        $monthly = (clone $base)->selectRaw("{$periodExpression} as period, type, SUM(amount) as total")->groupBy('period', 'type')->orderBy('period')->get();
        return response()->json([
            'kpis' => ['income' => (float) $income, 'expenses' => (float) $expenses, 'balance' => (float) $income - (float) $expenses, 'budget' => (float) $budget, 'execution_rate' => $budget > 0 ? round(((float) $expenses / (float) $budget) * 100, 2) : 0, 'pending' => (clone $base)->whereIn('workflow_status', ['draft', 'submitted', 'pending'])->count()],
            'monthly' => $monthly,
            'projects' => Project::where('organization_id', $organizationId)->orderBy('name')->limit(8)->get(),
            'recent_transactions' => (clone $base)->latest()->limit(8)->get(),
        ]);
    }
}
