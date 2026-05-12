<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Calculation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CalculationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_history_in_reverse_chronological_order(): void
    {
        Calculation::query()->create([
            'expression' => '1 1 +',
            'result' => '2',
            'status' => Calculation::STATUS_SUCCESS,
            'evaluated_at' => CarbonImmutable::parse('2026-05-03 12:00:00'),
        ]);

        Calculation::query()->create([
            'expression' => '2 2 +',
            'result' => '4',
            'status' => Calculation::STATUS_SUCCESS,
            'evaluated_at' => CarbonImmutable::parse('2026-05-03 12:01:00'),
        ]);

        $response = $this->getJson('/api/history');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.expression', '2 2 +')
            ->assertJsonPath('data.1.expression', '1 1 +')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('current_page', 1);
    }

    public function test_it_clears_history(): void
    {
        Calculation::query()->create([
            'expression' => '1 1 +',
            'result' => '2',
            'status' => Calculation::STATUS_SUCCESS,
            'evaluated_at' => CarbonImmutable::now(),
        ]);

        $response = $this->deleteJson('/api/history');

        $response->assertNoContent();
        $this->assertDatabaseCount('calculations', 0);
    }
}
