<?php

declare(strict_types=1);

namespace Tests\Feature\Application;

use App\Application\Calculations\ClearCalculationHistory;
use App\Models\Calculation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClearCalculationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_successful_and_failed_calculations(): void
    {
        Calculation::query()->create([
            'expression' => '3 4 +',
            'result' => '7',
            'error_message' => null,
            'status' => Calculation::STATUS_SUCCESS,
            'evaluated_at' => CarbonImmutable::now(),
        ]);

        Calculation::query()->create([
            'expression' => '5 0 /',
            'result' => null,
            'error_message' => 'Division by zero is undefined',
            'status' => Calculation::STATUS_ERROR,
            'evaluated_at' => CarbonImmutable::now(),
        ]);

        app(ClearCalculationHistory::class)->execute();

        $this->assertDatabaseCount('calculations', 0);
    }

    public function test_it_is_safe_when_history_is_empty(): void
    {
        app(ClearCalculationHistory::class)->execute();

        $this->assertDatabaseCount('calculations', 0);
    }
}
