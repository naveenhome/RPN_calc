<?php

declare(strict_types=1);

namespace App\Domain\Rpn\Operations;

interface Operation
{
    public function token(): string;

    public function arity(): int;

    /**
     * @param array<int, int|float> $operands
     */
    public function apply(array $operands): int|float;
}
