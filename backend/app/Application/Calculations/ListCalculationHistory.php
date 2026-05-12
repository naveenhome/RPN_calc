<?php

declare(strict_types=1);

namespace App\Application\Calculations;

use App\Models\Calculation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCalculationHistory
{
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        return Calculation::query()
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
