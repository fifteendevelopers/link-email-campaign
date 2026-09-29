<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SendsCampaignEmail;
use App\Http\Requests\SendSchoolBookingEmailRequest;
use App\Http\Requests\SendSchoolEmailRequest;
use Illuminate\Http\JsonResponse;

class SchoolEmailController extends Controller
{
    use SendsCampaignEmail;

    public function sendConsentCheck(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Upcoming Bikeability - action required! Complete consent checks and finalise numbers for {$data['establishmentName']}",
            template: 'emails.school_coms.pre_course.consent_check',
            data: $data,
        );
    }

    public function sendConsentCheckReminder(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: complete consent checks and finalise numbers for Bikeability at {$data['establishmentName']}",
            template: 'emails.school_coms.pre_course.consent_check_reminder',
            data: $data,
        );
    }

    public function sendConsentCheckOutstanding(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Check your outstanding Bikeability consents for {$data['establishmentName']}",
            template: 'emails.school_coms.pre_course.consent_check_outstanding',
            data: $data,
        );
    }

    public function sendConsentCheckFinalReminder(SendSchoolEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Final reminder: Are you ready for Bikeability at {$data['establishmentName']}?",
            template: 'emails.school_coms.pre_course.consent_check_final_reminder',
            data: $data,
        );
    }

    public function sendBookingConfirmation(SendSchoolBookingEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Booking confirmation: Bikeability cycle training at {$data['establishmentName']}",
            template: 'emails.school_coms.post_course.booking_confirmation',
            data: $data,
        );
    }

    public function sendBookingConfirmationReminder(SendSchoolBookingEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: Booking confirmation for Bikeability cycle training at {$data['establishmentName']}",
            template: 'emails.school_coms.post_course.booking_confirmation_reminder',
            data: $data,
        );
    }
}
