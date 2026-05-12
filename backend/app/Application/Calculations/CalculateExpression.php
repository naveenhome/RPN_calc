<?php

declare(strict_types=1);

namespace App\Application\Calculations;

use App\Domain\Rpn\Exceptions\EvaluationException;
use App\Domain\Rpn\RpnEvaluator;
use App\Models\Calculation;
use Carbon\CarbonImmutable;

final class CalculateExpression
{
    public function __construct(
        private readonly RpnEvaluator $evaluator
    ) {
    }

    public function execute(string $expression): Calculation
    {
        try {
            $result = $this->evaluator->evaluate($expression);

            return Calculation::query()->create([
                'expression' => $expression,
                'result' => $this->formatNumericValue($result),
                'error_message' => null,
                'status' => Calculation::STATUS_SUCCESS,
                'evaluated_at' => CarbonImmutable::now(),
            ]);
        } catch (EvaluationException $exception) {
            return Calculation::query()->create([
                'expression' => $expression,
                'result' => null,
                'error_message' => $exception->getMessage(),
                'status' => Calculation::STATUS_ERROR,
                'evaluated_at' => CarbonImmutable::now(),
            ]);
        }
    }

    private function formatNumericValue(int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        $normalizedValue = rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');

        return $normalizedValue === '-0' ? '0' : $normalizedValue;
    }
}
