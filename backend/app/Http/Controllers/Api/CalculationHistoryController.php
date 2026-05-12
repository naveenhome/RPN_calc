<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Calculations\ClearCalculationHistory;
use App\Application\Calculations\ListCalculationHistory;
use App\Http\Controllers\Controller;
use App\Models\Calculation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CalculationHistoryController extends Controller
{
    public function index(Request $request, ListCalculationHistory $listCalculationHistory): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));
        $history = $listCalculationHistory->execute($perPage);

        return response()->json([
            'data' => array_map(
                static fn (Calculation $calculation): array => [
                    'id' => $calculation->id,
                    'expression' => $calculation->expression,
                    'result' => $calculation->result,
                    'error_message' => $calculation->error_message,
                    'status' => $calculation->status,
                    'evaluated_at' => $calculation->evaluated_at?->toISOString(),
                ],
                $history->items()
            ),
            'total' => $history->total(),
            'per_page' => $history->perPage(),
            'current_page' => $history->currentPage(),
        ]);
    }

    public function destroy(ClearCalculationHistory $clearCalculationHistory): Response
    {
        $clearCalculationHistory->execute();

        return response()->noContent();
    }
}
