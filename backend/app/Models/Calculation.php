<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Calculation extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    public $timestamps = false;

    protected $fillable = [
        'expression',
        'result',
        'error_message',
        'status',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];
}
