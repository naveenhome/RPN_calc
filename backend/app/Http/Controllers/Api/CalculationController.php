<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Calculations\CalculateExpression;
use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluateExpressionRequest;
use App\Models\Calculation;
use Illuminate\Http\JsonResponse;

final class CalculationController extends Controller
{
    public function __invoke(
        EvaluateExpressionRequest $request,
        CalculateExpression $calculateExpression
    ): JsonResponse {
        $calculation = $calculateExpression->execute($request->string('expression')->trim()->value());

        $payload = [
            'id' => $calculation->id,
            'expression' => $calculation->expression,
            'result' => $calculation->result,
            'error_message' => $calculation->error_message,
            'status' => $calculation->status,
        ];

        return $calculation->status === Calculation::STATUS_SUCCESS
            ? response()->json($payload)
            : response()->json(['message' => $calculation->error_message, 'code' => 'EVALUATION_ERROR'] + $payload, 422);
    }
}
