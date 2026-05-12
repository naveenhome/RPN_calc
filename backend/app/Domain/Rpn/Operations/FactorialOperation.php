<?php

declare(strict_types=1);

namespace App\Domain\Rpn\Operations;

use App\Domain\Rpn\Exceptions\EvaluationException;

final class FactorialOperation implements Operation
{
    public function token(): string
    {
        return '!';
    }

    public function arity(): int
    {
        return 1;
    }

    public function apply(array $operands): int|float
    {
        $value = $operands[0];

        if ($value < 0) {
            throw new EvaluationException('Factorial is undefined for negative numbers');
        }

        if (floor((float) $value) !== (float) $value) {
            throw new EvaluationException('Factorial requires a non-negative integer');
        }

        $factorial = 1;
        $limit = (int) $value;

        for ($number = 2; $number <= $limit; $number++) {
            $factorial *= $number;
        }

        return $factorial;
    }
}
