<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SendsCampaignEmail;
use App\Http\Requests\SendParentEmailRequest;
use Illuminate\Http\JsonResponse;

class ParentEmailController extends Controller
{
    use SendsCampaignEmail;

    public function sendConsentConfirmation(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "We've received {$data['childName']}'s Bikeability consent form",
            template: 'emails.parent_coms.consent_confirmation.confirmation',
            data: $data,
        );
    }

    public function sendPreCourseHowToGetReady(SendParentEmailRequest $request): JsonResponse
    {
        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'How to get ready for Bikeability',
            template: 'emails.parent_coms.pre_course.how_to_get_ready_for_bikeability',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseFaq(SendParentEmailRequest $request): JsonResponse
    {
        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'Frequently Asked Questions, answered',
            template: 'emails.parent_coms.pre_course.faq',
            data: $request->templateData(),
        );
    }

    public function sendPreCourseReminder(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "Reminder: {$data['childName']} is booked for Bikeability cycle training soon",
            template: 'emails.parent_coms.pre_course.reminder',
            data: $data,
        );
    }

    public function sendPostCourseRiderReport(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();
        $data['outcomesURL'] ??= config('link.club.base_url', 'https://club.bikeability.org.uk');

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: "{$data['childName']} did it!",
            template: 'emails.parent_coms.post_course.rider_report',
            data: $data,
        );
    }

    public function sendPostCourseParentSurvey(SendParentEmailRequest $request): JsonResponse
    {
        $data = $request->templateData();
        $data['surveyLinkUrl'] ??= config('ridersurvey.base_url', '#');

        return $this->send(
            recipient: $request->validated('recipient'),
            subject: 'Please share your views',
            template: 'emails.parent_coms.post_course.parent_survey',
            data: $data,
        );
    }
}
