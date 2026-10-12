<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendDueParentCommunications;
use App\Mail\CampaignMail;
use App\Models\ParentSubscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDueParentCommunicationsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_job_sends_each_due_parent_communication_once_for_active_deliveries(): void
    {
        Carbon::setTestNow('2026-10-20 08:00:00 Europe/London');
        Mail::fake();

        $subscriber = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_status_1' => 'active',
            'parent_name_1' => 'Jane Parent',
            'child_name_1' => 'Sam Child',
            'establishment_name_1' => 'Example School',
            'provider_name_1' => 'Example Provider',
            'provider_public_email_address_1' => 'provider@example.com',
            'journey_confirmation_start_date_1' => '2026-10-20',
            'cycle_and_helment_check_comms_date_1' => '2026-10-20',
            'pre_course_faq_comms_date_1' => '2026-10-20',
            'outcomes_journey_start_date_1' => '2026-10-20',
            'outcome_survey_comms_date_1' => '2026-10-20',
            'bikeability_club_link_1' => 'https://club.example.com/report',
            'survey_link_1' => 'https://survey.example.com/parent',
        ]);

        ParentSubscriber::create([
            'email_address' => 'inactive@example.com',
            'delivery_status_1' => 'deleted',
            'journey_confirmation_start_date_1' => '2026-10-20',
        ]);

        SendDueParentCommunications::dispatchSync();

        Mail::assertSent(CampaignMail::class, 5);
        Mail::assertNotSent(
            CampaignMail::class,
            static fn (CampaignMail $mail): bool => $mail->hasTo('inactive@example.com'),
        );

        foreach ([
            'emails.parent_coms.consent_confirmation.confirmation',
            'emails.parent_coms.pre_course.how_to_get_ready_for_bikeability',
            'emails.parent_coms.pre_course.faq',
            'emails.parent_coms.post_course.rider_report',
            'emails.parent_coms.post_course.parent_survey',
        ] as $template) {
            Mail::assertSent(
                CampaignMail::class,
                static fn (CampaignMail $mail): bool => $mail->data['template'] === $template,
            );
        }

        $subscriber->refresh();

        $this->assertNotNull($subscriber->journey_confirmation_sent_at_1);
        $this->assertNotNull($subscriber->cycle_and_helment_check_sent_at_1);
        $this->assertNotNull($subscriber->pre_course_faq_sent_at_1);
        $this->assertNotNull($subscriber->outcomes_journey_sent_at_1);
        $this->assertNotNull($subscriber->outcome_survey_sent_at_1);
        $this->assertDatabaseCount('email_trackings', 5);

        SendDueParentCommunications::dispatchSync();

        Mail::assertSent(CampaignMail::class, 5);
        $this->assertDatabaseCount('email_trackings', 5);
    }
}
