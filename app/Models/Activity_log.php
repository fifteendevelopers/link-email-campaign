<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity_log extends Model
{
    public const PROCESS_ERROR = 'process_error';

    public const PROCESS_INFO = 'process_info';

    protected $table = 'activity_logs';

    protected $fillable = [
        'ip',
        'type',
        'source',
        'message',
    ];

    public static function log(?string $ip, string $type, string $source, string $message): self
    {
        return self::create([
            'ip' => $ip,
            'type' => $type,
            'source' => $source,
            'message' => $message,
        ]);
    }
}
