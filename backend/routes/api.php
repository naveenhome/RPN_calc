<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\CalculationHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn (): array => ['status' => 'ok']);
Route::post('/calculate', CalculationController::class);
Route::get('/history', [CalculationHistoryController::class, 'index']);
Route::delete('/history', [CalculationHistoryController::class, 'destroy']);
