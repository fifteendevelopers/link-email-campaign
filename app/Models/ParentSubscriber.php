<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentSubscriber extends Model
{
    protected $fillable = [
        'consent_id',
        'email_address',
        'name',
        'delivery_id',
        'delivery_status',
        'parent_name',
        'child_name',
        'course_level',
        'establishment_name',
        'provider_name',
        'provider_public_email_address',
        'comms_start_date',
        'cycle_and_helment_check_comms_date',
        'pre_course_faq_comms_date',
        'instructors_delivery',
        'outcomes_journey_start_date',
        'bikeability_club_link',
        'survey_link',
        'journey_confirmation_start_date',
        'journey_confirmation_sent_at',
        'cycle_and_helment_check_sent_at',
        'pre_course_faq_sent_at',
        'outcomes_journey_sent_at',
        'outcome_survey_comms_date',
        'outcome_survey_sent_at',
        'resubscribe',
        'restart_subscription_based_autoresponders',
        'consent_to_track',
        'consent_to_send_sms',
    ];

    protected function casts(): array
    {
        return [
            'consent_id' => 'integer',
            'delivery_id' => 'integer',
            'comms_start_date' => 'date:Y-m-d',
            'cycle_and_helment_check_comms_date' => 'date:Y-m-d',
            'pre_course_faq_comms_date' => 'date:Y-m-d',
            'outcomes_journey_start_date' => 'date:Y-m-d',
            'journey_confirmation_start_date' => 'date:Y-m-d',
            'journey_confirmation_sent_at' => 'datetime',
            'cycle_and_helment_check_sent_at' => 'datetime',
            'pre_course_faq_sent_at' => 'datetime',
            'outcomes_journey_sent_at' => 'datetime',
            'outcome_survey_comms_date' => 'date:Y-m-d',
            'outcome_survey_sent_at' => 'datetime',
            'resubscribe' => 'boolean',
            'restart_subscription_based_autoresponders' => 'boolean',
        ];
    }
}
