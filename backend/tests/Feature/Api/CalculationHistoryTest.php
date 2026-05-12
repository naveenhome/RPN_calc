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
        $this->createCalculation('1 1 +', '2', CarbonImmutable::parse('2026-05-03 12:00:00'));
        $this->createCalculation('2 2 +', '4', CarbonImmutable::parse('2026-05-03 12:01:00'));

        $response = $this->getJson('/api/history');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'expression',
                        'result',
                        'error_message',
                        'status',
                        'evaluated_at',
                    ],
                ],
                'total',
                'per_page',
                'current_page',
            ])
            ->assertJsonPath('data.0.expression', '2 2 +')
            ->assertJsonPath('data.1.expression', '1 1 +')
            ->assertJsonPath('total', 2)
            ->assertJsonPath('per_page', 15)
            ->assertJsonPath('current_page', 1);
    }

    public function test_it_lists_failed_calculations_in_history(): void
    {
        Calculation::query()->create([
            'expression' => '5 0 /',
            'result' => null,
            'error_message' => 'Division by zero is undefined',
            'status' => Calculation::STATUS_ERROR,
            'evaluated_at' => CarbonImmutable::parse('2026-05-03 12:00:00'),
        ]);

        $response = $this->getJson('/api/history');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.expression', '5 0 /')
            ->assertJsonPath('data.0.result', null)
            ->assertJsonPath('data.0.error_message', 'Division by zero is undefined')
            ->assertJsonPath('data.0.status', Calculation::STATUS_ERROR);
    }

    public function test_it_breaks_timestamp_ties_by_newest_id(): void
    {
        $timestamp = CarbonImmutable::parse('2026-05-03 12:00:00');
        $first = $this->createCalculation('1 1 +', '2', $timestamp);
        $second = $this->createCalculation('2 2 +', '4', $timestamp);

        $response = $this->getJson('/api/history');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.1.id', $first->id);
    }

    /**
     * @dataProvider perPageProvider
     */
    public function test_it_clamps_per_page(int $requestedPerPage, int $expectedPerPage, int $expectedCount): void
    {
        for ($index = 1; $index <= 101; $index++) {
            $this->createCalculation(
                sprintf('%d 1 +', $index),
                (string) ($index + 1),
                CarbonImmutable::parse('2026-05-03 12:00:00')->addSeconds($index)
            );
        }

        $response = $this->getJson(sprintf('/api/history?per_page=%d', $requestedPerPage));

        $response
            ->assertOk()
            ->assertJsonPath('per_page', $expectedPerPage)
            ->assertJsonCount($expectedCount, 'data')
            ->assertJsonPath('total', 101);
    }

    /**
     * @return array<string, array{requestedPerPage: int, expectedPerPage: int, expectedCount: int}>
     */
    public static function perPageProvider(): array
    {
        return [
            'minimum page size' => [
                'requestedPerPage' => 0,
                'expectedPerPage' => 1,
                'expectedCount' => 1,
            ],
            'maximum page size' => [
                'requestedPerPage' => 150,
                'expectedPerPage' => 100,
                'expectedCount' => 100,
            ],
        ];
    }

    public function test_it_clears_history(): void
    {
        $this->createCalculation('1 1 +', '2', CarbonImmutable::now());

        $response = $this->deleteJson('/api/history');

        $response->assertNoContent();
        $this->assertDatabaseCount('calculations', 0);

        $this->getJson('/api/history')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    private function createCalculation(string $expression, string $result, CarbonImmutable $evaluatedAt): Calculation
    {
        return Calculation::query()->create([
            'expression' => $expression,
            'result' => $result,
            'error_message' => null,
            'status' => Calculation::STATUS_SUCCESS,
            'evaluated_at' => $evaluatedAt,
        ]);
    }
}
