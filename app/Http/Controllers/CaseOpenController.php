<?php

namespace App\Http\Controllers;

use App\Models\GameCase;
use App\Services\CaseOpenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseOpenController extends Controller
{
    public function __construct(private CaseOpenService $caseOpenService) {}

    public function open(Request $request, int $id): JsonResponse
    {
        $gameCase = GameCase::with('skins')->findOrFail($id);

        try {
            $result = $this->caseOpenService->open($request->user(), $gameCase);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}
