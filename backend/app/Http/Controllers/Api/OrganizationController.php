<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        return response()->json([
            'organization_id' => $request->attributes->get('organization_id'),
        ]);
    }
}
