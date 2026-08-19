<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        return response()->json(Project::with('budgetLines')->where('organization_id', $organizationId)->latest('id')->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $organizationId = (int) $request->attributes->get('organization_id');
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'donor' => ['nullable', 'string', 'max:255'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,active,suspended,closed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $project = Project::create($data + ['organization_id' => $organizationId, 'spent_amount' => 0]);
        return response()->json($project->load('budgetLines'), 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === (int) $request->attributes->get('organization_id'), 404);
        return response()->json($project->load('budgetLines', 'transactions'));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === (int) $request->attributes->get('organization_id'), 404);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'], 'donor' => ['nullable', 'string', 'max:255'],
            'budget_amount' => ['sometimes', 'numeric', 'min:0'], 'status' => ['sometimes', 'in:draft,active,suspended,closed'],
            'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date'],
        ]);
        $project->update($data);
        return response()->json($project->fresh()->load('budgetLines'));
    }
}
