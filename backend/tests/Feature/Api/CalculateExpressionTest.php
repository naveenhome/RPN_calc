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
                'status' => Calculation::STATUS_SUCCESS,
            ]);

        $this->assertDatabaseHas('calculations', [
            'expression' => '3 4 +',
            'result' => '7',
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
                'status' => Calculation::STATUS_ERROR,
            ]);

        $this->assertDatabaseHas('calculations', [
            'expression' => '5 0 /',
            'error_message' => 'Division by zero is undefined',
            'status' => Calculation::STATUS_ERROR,
        ]);
    }
}
