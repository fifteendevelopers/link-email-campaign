<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendDueSchoolCommunications;
use App\Mail\CampaignMail;
use App\Models\SchoolSubscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDueSchoolCommunicationsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_job_sends_each_due_school_communication_once_for_active_deliveries(): void
    {
        Carbon::setTestNow('2026-10-20 08:00:00 Europe/London');
        Mail::fake();

        $subscriber = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'delivery_status_1' => 'active',
            'first_name_1' => 'Alex',
            'establishment_name_1' => 'Example School',
            'provider_name_1' => 'Example Provider',
            'provider_contact_email_address_1' => 'provider@example.com',
            'booking_link_1' => 'https://example.com/bookings/10',
            'journey_confirmation_start_date_1' => '2026-10-20',
            'pre_course_consent_check_comms_date_1' => '2026-10-20',
            'outstanding_consent_check_comms_date_1' => '2026-10-20',
            'final_consent_reminder_comms_date_1' => '2026-10-20',
        ]);

        SchoolSubscriber::create([
            'email_address' => 'inactive-school@example.com',
            'delivery_status_1' => 'deleted',
            'journey_confirmation_start_date_1' => '2026-10-20',
        ]);

        SendDueSchoolCommunications::dispatchSync();

        Mail::assertSent(CampaignMail::class, 4);
        Mail::assertNotSent(
            CampaignMail::class,
            static fn (CampaignMail $mail): bool => $mail->hasTo('inactive-school@example.com'),
        );

        foreach ([
            'emails.school_comms.booking_confirmation.booking_confirmation',
            'emails.school_comms.pre_course.consent_check',
            'emails.school_comms.pre_course.consent_check_outstanding',
            'emails.school_comms.pre_course.consent_check_final_reminder',
        ] as $template) {
            Mail::assertSent(
                CampaignMail::class,
                static fn (CampaignMail $mail): bool => $mail->data['template'] === $template,
            );
        }

        $subscriber->refresh();

        $this->assertNotNull($subscriber->journey_confirmation_sent_at_1);
        $this->assertNotNull($subscriber->pre_course_consent_check_sent_at_1);
        $this->assertNotNull($subscriber->outstanding_consent_check_sent_at_1);
        $this->assertNotNull($subscriber->final_consent_reminder_sent_at_1);
        $this->assertDatabaseHas('email_trackings', [
            'school_subscriber_id' => $subscriber->id,
        ]);
        $this->assertDatabaseCount('email_trackings', 4);

        SendDueSchoolCommunications::dispatchSync();

        Mail::assertSent(CampaignMail::class, 4);
        $this->assertDatabaseCount('email_trackings', 4);
    }
}
