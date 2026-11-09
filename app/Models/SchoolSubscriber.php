<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSubscriber extends Model
{
    protected $fillable = [
        'email_address',
        'name',
        'delivery_id',
        'delivery_status',
        'delivery_start_date',
        'journey_confirmation_start_date',
        'journey_confirmation_sent_at',
        'course_comms_start_date',
        'pre_course_consent_check_comms_date',
        'pre_course_consent_check_sent_at',
        'outstanding_consent_check_comms_date',
        'outstanding_consent_check_sent_at',
        'final_consent_reminder_comms_date',
        'final_consent_reminder_sent_at',
        'first_name',
        'provider_name',
        'provider_contact_email_address',
        'booking_link',
        'establishment_name',
        'resubscribe',
        'restart_subscription_based_autoresponders',
        'consent_to_track',
        'consent_to_send_sms',
    ];

    protected function casts(): array
    {
        return [
            'delivery_id' => 'integer',
            'delivery_start_date' => 'date:Y-m-d',
            'course_comms_start_date' => 'date:Y-m-d',
            'pre_course_consent_check_comms_date' => 'date:Y-m-d',
            'outstanding_consent_check_comms_date' => 'date:Y-m-d',
            'final_consent_reminder_comms_date' => 'date:Y-m-d',
            'journey_confirmation_start_date' => 'date:Y-m-d',
            'journey_confirmation_sent_at' => 'datetime',
            'pre_course_consent_check_sent_at' => 'datetime',
            'outstanding_consent_check_sent_at' => 'datetime',
            'final_consent_reminder_sent_at' => 'datetime',
            'resubscribe' => 'boolean',
            'restart_subscription_based_autoresponders' => 'boolean',
        ];
    }
}
