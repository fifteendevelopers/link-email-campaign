<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'method',
        'path',
        'route_name',
        'ip',
        'user_agent',
        'query',
        'payload',
        'status_code',
        'response',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'query' => 'array',
            'payload' => 'array',
            'status_code' => 'integer',
            'duration_ms' => 'integer',
        ];
    }
}
