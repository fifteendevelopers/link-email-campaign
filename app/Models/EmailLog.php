<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    public $timestamps = false;

    protected $table = 'email_log';

    protected $fillable = [
        'date',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'headers',
        'attachments',
        'parent_subscriber_id',
        'school_subscriber_id',
        'sent_column',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'parent_subscriber_id' => 'integer',
            'school_subscriber_id' => 'integer',
        ];
    }
}
