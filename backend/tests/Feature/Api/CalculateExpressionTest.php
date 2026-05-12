<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Calculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CalculateExpressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_and_persists_a_successful_expression(): void
    {
        $response = $this->postJson('/api/calculate', [
            'expression' => '3 4 +',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'expression' => '3 4 +',
                'result' => '7',
                'error_message' => null,
                'status' => Calculation::STATUS_SUCCESS,
            ])
            ->assertJsonStructure([
                'id',
                'expression',
                'result',
                'error_message',
                'status',
            ]);

        $this->assertDatabaseHas('calculations', [
            'expression' => '3 4 +',
            'result' => '7',
            'error_message' => null,
            'status' => Calculation::STATUS_SUCCESS,
        ]);
    }

    public function test_it_returns_a_validation_error_for_invalid_expressions_and_stores_the_failure(): void
    {
        $response = $this->postJson('/api/calculate', [
            'expression' => '5 0 /',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Division by zero is undefined',
                'code' => 'EVALUATION_ERROR',
                'expression' => '5 0 /',
                'result' => null,
                'error_message' => 'Division by zero is undefined',
                'status' => Calculation::STATUS_ERROR,
            ])
            ->assertJsonStructure([
                'message',
                'code',
                'id',
                'expression',
                'result',
                'error_message',
                'status',
            ]);

        $this->assertDatabaseHas('calculations', [
            'expression' => '5 0 /',
            'result' => null,
            'error_message' => 'Division by zero is undefined',
            'status' => Calculation::STATUS_ERROR,
        ]);
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function test_it_rejects_invalid_requests_without_storing_history(array $payload): void
    {
        $response = $this->postJson('/api/calculate', $payload);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['expression']);

        $this->assertDatabaseCount('calculations', 0);
    }

    /**
     * @return array<string, array{payload: array<string, mixed>}>
     */
    public static function invalidRequestProvider(): array
    {
        return [
            'missing expression' => ['payload' => []],
            'blank expression' => ['payload' => ['expression' => '   ']],
        ];
    }

    public function test_it_trims_the_expression_before_evaluation_and_persistence(): void
    {
        $response = $this->postJson('/api/calculate', [
            'expression' => '  3 4 +  ',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'expression' => '3 4 +',
                'result' => '7',
                'status' => Calculation::STATUS_SUCCESS,
            ]);

        $this->assertDatabaseHas('calculations', [
            'expression' => '3 4 +',
            'result' => '7',
            'status' => Calculation::STATUS_SUCCESS,
        ]);
    }
}
