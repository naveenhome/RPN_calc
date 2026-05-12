<?php

declare(strict_types=1);

namespace Tests\Feature\Application;

use App\Application\Calculations\ListCalculationHistory;
use App\Models\Calculation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListCalculationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_history_newest_first(): void
    {
        $oldest = $this->createCalculation('1 1 +', '2', CarbonImmutable::parse('2026-05-03 12:00:00'));
        $newest = $this->createCalculation('2 2 +', '4', CarbonImmutable::parse('2026-05-03 12:01:00'));

        $history = app(ListCalculationHistory::class)->execute();

        $this->assertSame([$newest->id, $oldest->id], $history->pluck('id')->all());
    }

    public function test_it_breaks_timestamp_ties_by_newest_id(): void
    {
        $timestamp = CarbonImmutable::parse('2026-05-03 12:00:00');
        $first = $this->createCalculation('1 1 +', '2', $timestamp);
        $second = $this->createCalculation('2 2 +', '4', $timestamp);

        $history = app(ListCalculationHistory::class)->execute();

        $this->assertSame([$second->id, $first->id], $history->pluck('id')->all());
    }

    public function test_it_uses_the_default_page_size(): void
    {
        for ($index = 1; $index <= 16; $index++) {
            $this->createCalculation(
                sprintf('%d 1 +', $index),
                (string) ($index + 1),
                CarbonImmutable::parse('2026-05-03 12:00:00')->addSeconds($index)
            );
        }

        $history = app(ListCalculationHistory::class)->execute();

        $this->assertSame(15, $history->perPage());
        $this->assertCount(15, $history->items());
        $this->assertSame(16, $history->total());
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
