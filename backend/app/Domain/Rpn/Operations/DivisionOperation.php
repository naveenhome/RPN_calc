<?php

declare(strict_types=1);

namespace App\Domain\Rpn\Operations;

use App\Domain\Rpn\Exceptions\EvaluationException;

final class DivisionOperation implements Operation
{
    public function token(): string
    {
        return '/';
    }

    public function arity(): int
    {
        return 2;
    }

    public function apply(array $operands): int|float
    {
        if ((float) $operands[1] === 0.0) {
            throw new EvaluationException('Division by zero is undefined');
        }

        return $operands[0] / $operands[1];
    }
}
