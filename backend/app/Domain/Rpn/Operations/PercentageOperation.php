<?php

declare(strict_types=1);

namespace App\Domain\Rpn\Operations;

final class PercentageOperation implements Operation
{
    public function token(): string
    {
        return '%';
    }

    public function arity(): int
    {
        return 1;
    }

    public function apply(array $operands): int|float
    {
        return $operands[0] / 100;
    }
}
