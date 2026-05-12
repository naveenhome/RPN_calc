<?php

declare(strict_types=1);

namespace App\Application\Calculations;

use App\Models\Calculation;

final class ClearCalculationHistory
{
    public function execute(): void
    {
        Calculation::query()->delete();
    }
}
