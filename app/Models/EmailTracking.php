<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmailTracking extends Model
{
    protected $table = 'email_trackings';

    protected $fillable = [
        'tracking_id',
        'recipient_email',
        'message_id',
        'subject',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'delivered_at',
        'first_opened_at',
        'last_opened_at',
        'open_count',
        'first_clicked_at',
        'last_clicked_at',
        'click_count',
        'last_clicked_url',
        'temporary_failed_at',
        'permanent_failed_at',
        'failure_reason',
        'complained_at',
        'unsubscribed_at',
        'last_event',
        'last_event_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'delivered_at' => 'datetime',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'first_clicked_at' => 'datetime',
            'last_clicked_at' => 'datetime',
            'temporary_failed_at' => 'datetime',
            'permanent_failed_at' => 'datetime',
            'complained_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'last_event_at' => 'datetime',
            'open_count' => 'integer',
            'click_count' => 'integer',
        ];
    }

    public function recordAccepted(Carbon $eventTime): void
    {
        $this->accepted_at ??= $eventTime;
        $this->touchLastEvent('accepted', $eventTime);
    }

    public function recordRejected(Carbon $eventTime, ?string $reason = null): void
    {
        $this->rejected_at = $eventTime;
        $this->rejection_reason = $reason;
        $this->touchLastEvent('rejected', $eventTime);
    }

    public function recordDelivered(Carbon $eventTime): void
    {
        $this->delivered_at ??= $eventTime;
        $this->touchLastEvent('delivered', $eventTime);
    }

    public function recordOpen(Carbon $eventTime): void
    {
        $this->first_opened_at ??= $eventTime;
        $this->last_opened_at = $eventTime;
        $this->open_count = ($this->open_count ?? 0) + 1;
        $this->touchLastEvent('opened', $eventTime);
    }

    public function recordClick(Carbon $eventTime, ?string $url = null): void
    {
        $this->first_clicked_at ??= $eventTime;
        $this->last_clicked_at = $eventTime;
        $this->click_count = ($this->click_count ?? 0) + 1;
        $this->last_clicked_url = $url;
        $this->touchLastEvent('clicked', $eventTime);
    }

    public function recordTemporaryFailure(Carbon $eventTime, ?string $reason = null): void
    {
        $this->temporary_failed_at = $eventTime;
        $this->failure_reason = $reason;
        $this->touchLastEvent('temporary_fail', $eventTime);
    }

    public function recordPermanentFailure(Carbon $eventTime, ?string $reason = null): void
    {
        $this->permanent_failed_at = $eventTime;
        $this->failure_reason = $reason;
        $this->touchLastEvent('permanent_fail', $eventTime);
    }

    public function recordComplaint(Carbon $eventTime): void
    {
        $this->complained_at = $eventTime;
        $this->touchLastEvent('complained', $eventTime);
    }

    public function recordUnsubscribe(Carbon $eventTime): void
    {
        $this->unsubscribed_at = $eventTime;
        $this->touchLastEvent('unsubscribed', $eventTime);
    }

    private function touchLastEvent(string $event, Carbon $eventTime): void
    {
        $this->last_event = $event;
        $this->last_event_at = $eventTime;
        $this->save();
    }
}
