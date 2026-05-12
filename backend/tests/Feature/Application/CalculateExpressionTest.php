<?php

declare(strict_types=1);

namespace Tests\Feature\Application;

use App\Application\Calculations\CalculateExpression;
use App\Models\Calculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CalculateExpressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_successful_calculation(): void
    {
        $calculation = app(CalculateExpression::class)->execute('3 4 +');

        $this->assertSame('3 4 +', $calculation->expression);
        $this->assertSame('7', $calculation->result);
        $this->assertNull($calculation->error_message);
        $this->assertSame(Calculation::STATUS_SUCCESS, $calculation->status);
        $this->assertNotNull($calculation->evaluated_at);

        $this->assertDatabaseHas('calculations', [
            'expression' => '3 4 +',
            'result' => '7',
            'error_message' => null,
            'status' => Calculation::STATUS_SUCCESS,
        ]);
    }

    public function test_it_stores_a_failed_evaluation(): void
    {
        $calculation = app(CalculateExpression::class)->execute('5 0 /');

        $this->assertSame('5 0 /', $calculation->expression);
        $this->assertNull($calculation->result);
        $this->assertSame('Division by zero is undefined', $calculation->error_message);
        $this->assertSame(Calculation::STATUS_ERROR, $calculation->status);

        $this->assertDatabaseHas('calculations', [
            'expression' => '5 0 /',
            'result' => null,
            'error_message' => 'Division by zero is undefined',
            'status' => Calculation::STATUS_ERROR,
        ]);
    }

    /**
     * @dataProvider resultFormattingProvider
     */
    public function test_it_formats_successful_results_for_storage(string $expression, string $expectedResult): void
    {
        $calculation = app(CalculateExpression::class)->execute($expression);

        $this->assertSame($expectedResult, $calculation->result);
    }

    /**
     * @return array<string, array{expression: string, expectedResult: string}>
     */
    public static function resultFormattingProvider(): array
    {
        return [
            'trims trailing zeros' => ['expression' => '3.1400 2 *', 'expectedResult' => '6.28'],
            'normalizes negative zero' => ['expression' => '0.0 -1 *', 'expectedResult' => '0'],
            'preserves large integer' => ['expression' => '20 !', 'expectedResult' => '2432902008176640000'],
        ];
    }
}
