<?php

declare(strict_types=1);

namespace App\Domain\Rpn\Operations;

final class PowerOperation implements Operation
{
    public function token(): string
    {
        return '^';
    }

    public function arity(): int
    {
        return 2;
    }

    public function apply(array $operands): int|float
    {
        return $operands[0] ** $operands[1];
    }
}
