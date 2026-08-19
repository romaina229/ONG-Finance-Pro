<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetLine;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        return response()->json(BudgetLine::with('project')->whereHas('project', fn ($q) => $q->where('organization_id', $organizationId))->latest('id')->paginate(100));
    }

    public function store(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'code' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:255'],
            'allocated_amount' => ['required', 'numeric', 'min:0'],
        ]);
        $project = Project::where('organization_id', $organizationId)->findOrFail($data['project_id']);
        $line = BudgetLine::create($data + ['project_id' => $project->id, 'committed_amount' => 0, 'spent_amount' => 0]);
        return response()->json($line->load('project'), 201);
    }

    public function update(Request $request, BudgetLine $budgetLine): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        abort_unless($budgetLine->project()->where('organization_id', $organizationId)->exists(), 404);
        $data = $request->validate(['category' => ['sometimes', 'string', 'max:255'], 'allocated_amount' => ['sometimes', 'numeric', 'min:0']]);
        $budgetLine->update($data);
        return response()->json($budgetLine->fresh()->load('project'));
    }
}
