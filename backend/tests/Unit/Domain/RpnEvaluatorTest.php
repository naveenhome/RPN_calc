<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Rpn\Exceptions\EvaluationException;
use App\Domain\Rpn\RpnEvaluator;
use PHPUnit\Framework\TestCase;

final class RpnEvaluatorTest extends TestCase
{
    /**
     * @dataProvider validExpressionProvider
     */
    public function test_it_evaluates_valid_expressions(string $expression, int|float $expectedResult): void
    {
        $result = (new RpnEvaluator())->evaluate($expression);

        if (is_float($expectedResult)) {
            $this->assertEqualsWithDelta($expectedResult, $result, 0.0000000001);

            return;
        }

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array<string, array{expression: string, expectedResult: int|float}>
     */
    public static function validExpressionProvider(): array
    {
        return [
            'addition' => ['expression' => '3 4 +', 'expectedResult' => 7],
            'subtraction' => ['expression' => '10 4 -', 'expectedResult' => 6],
            'multiplication' => ['expression' => '3 4 *', 'expectedResult' => 12],
            'division' => ['expression' => '20 4 /', 'expectedResult' => 5],
            'chained expression' => ['expression' => '3 4 + 2 *', 'expectedResult' => 14],
            'floating point operands' => ['expression' => '3.14 2 *', 'expectedResult' => 6.28],
            'floating point addition' => ['expression' => '3.5 2 +', 'expectedResult' => 5.5],
            'exponentiation' => ['expression' => '2 10 ^', 'expectedResult' => 1024],
            'fractional exponentiation' => ['expression' => '2.0 0.5 ^', 'expectedResult' => 1.4142135623730951],
            'zero percentage' => ['expression' => '0 %', 'expectedResult' => 0],
            'percentage' => ['expression' => '75 %', 'expectedResult' => 0.75],
            'percentage above one hundred' => ['expression' => '200 %', 'expectedResult' => 2],
            'zero factorial' => ['expression' => '0 !', 'expectedResult' => 1],
            'positive factorial' => ['expression' => '5 !', 'expectedResult' => 120],
            'large supported factorial' => ['expression' => '20 !', 'expectedResult' => 2432902008176640000],
            'inverse-style expression' => ['expression' => '3 4 + 4 -', 'expectedResult' => 3],
            'division multiplication inverse' => ['expression' => '8 2 / 2 *', 'expectedResult' => 8],
            'percentage multiplication inverse' => ['expression' => '75 % 100 *', 'expectedResult' => 75.0],
            'extra whitespace' => ['expression' => "  3   4\n+\t2 *  ", 'expectedResult' => 14],
        ];
    }

    /**
     * @dataProvider invalidExpressionProvider
     */
    public function test_it_rejects_invalid_expressions(string $expression, string $expectedMessage): void
    {
        $this->expectException(EvaluationException::class);
        $this->expectExceptionMessage($expectedMessage);

        (new RpnEvaluator())->evaluate($expression);
    }

    /**
     * @return array<string, array{expression: string, expectedMessage: string}>
     */
    public static function invalidExpressionProvider(): array
    {
        return [
            'empty expression' => [
                'expression' => '',
                'expectedMessage' => 'Expression is required',
            ],
            'whitespace-only expression' => [
                'expression' => '   ',
                'expectedMessage' => 'Expression is required',
            ],
            'stack underflow' => [
                'expression' => '+ 3',
                'expectedMessage' => 'Not enough operands for operator `+`',
            ],
            'division by zero' => [
                'expression' => '5 0 /',
                'expectedMessage' => 'Division by zero is undefined',
            ],
            'negative factorial' => [
                'expression' => '-3 !',
                'expectedMessage' => 'Factorial is undefined for negative numbers',
            ],
            'non-integer factorial' => [
                'expression' => '3.5 !',
                'expectedMessage' => 'Factorial requires a non-negative integer',
            ],
            'unknown token' => [
                'expression' => '3 4 $',
                'expectedMessage' => 'Unknown token `$`',
            ],
            'too many operands' => [
                'expression' => '3 4',
                'expectedMessage' => 'Too many operands - expression is incomplete',
            ],
        ];
    }
}
