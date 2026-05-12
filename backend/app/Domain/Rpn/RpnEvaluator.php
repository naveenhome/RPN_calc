<?php

declare(strict_types=1);

namespace App\Domain\Rpn;

use App\Domain\Rpn\Exceptions\EvaluationException;
use App\Domain\Rpn\Operations\AdditionOperation;
use App\Domain\Rpn\Operations\DivisionOperation;
use App\Domain\Rpn\Operations\FactorialOperation;
use App\Domain\Rpn\Operations\MultiplicationOperation;
use App\Domain\Rpn\Operations\Operation;
use App\Domain\Rpn\Operations\PercentageOperation;
use App\Domain\Rpn\Operations\PowerOperation;
use App\Domain\Rpn\Operations\SubtractionOperation;

final class RpnEvaluator
{
    /**
     * @var array<string, Operation>
     */
    private array $operations;

    /**
     * @param iterable<int, Operation> $operations
     */
    public function __construct(iterable $operations = [])
    {
        $resolvedOperations = $operations === []
            ? [
                new AdditionOperation(),
                new SubtractionOperation(),
                new MultiplicationOperation(),
                new DivisionOperation(),
                new PowerOperation(),
                new PercentageOperation(),
                new FactorialOperation(),
            ]
            : $operations;

        $this->operations = [];

        foreach ($resolvedOperations as $operation) {
            $this->operations[$operation->token()] = $operation;
        }
    }

    public function evaluate(string $expression): int|float
    {
        $trimmedExpression = trim($expression);

        if ($trimmedExpression === '') {
            throw new EvaluationException('Expression is required');
        }

        $tokens = preg_split('/\s+/', $trimmedExpression);
        $stack = [];

        foreach ($tokens as $token) {
            if ($token === false || $token === '') {
                continue;
            }

            if (is_numeric($token)) {
                $stack[] = str_contains($token, '.') ? (float) $token : (int) $token;
                continue;
            }

            $operation = $this->operations[$token] ?? null;

            if ($operation === null) {
                throw new EvaluationException(sprintf('Unknown token `%s`', $token));
            }

            if (count($stack) < $operation->arity()) {
                throw new EvaluationException(sprintf('Not enough operands for operator `%s`', $token));
            }

            $operands = array_splice($stack, $operation->arity() * -1);
            $stack[] = $operation->apply($operands);
        }

        if (count($stack) !== 1) {
            throw new EvaluationException('Too many operands - expression is incomplete');
        }

        return $stack[0];
    }
}
