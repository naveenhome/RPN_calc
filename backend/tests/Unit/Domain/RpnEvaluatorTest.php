<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Rpn\Exceptions\EvaluationException;
use App\Domain\Rpn\RpnEvaluator;
use PHPUnit\Framework\TestCase;

final class RpnEvaluatorTest extends TestCase
{
    public function test_it_evaluates_basic_arithmetic(): void
    {
        $result = (new RpnEvaluator())->evaluate('3 4 + 2 *');

        $this->assertSame(14, $result);
    }

    public function test_it_supports_percentage_and_factorial_operations(): void
    {
        $evaluator = new RpnEvaluator();

        $this->assertSame(0.75, $evaluator->evaluate('75 %'));
        $this->assertSame(120, $evaluator->evaluate('5 !'));
    }

    public function test_it_rejects_division_by_zero(): void
    {
        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessage('Division by zero is undefined');

        (new RpnEvaluator())->evaluate('5 0 /');
    }

    public function test_it_rejects_unknown_tokens(): void
    {
        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessage('Unknown token `$`');

        (new RpnEvaluator())->evaluate('3 4 $');
    }

    public function test_it_rejects_too_many_operands(): void
    {
        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessage('Too many operands - expression is incomplete');

        (new RpnEvaluator())->evaluate('3 4');
    }
}
