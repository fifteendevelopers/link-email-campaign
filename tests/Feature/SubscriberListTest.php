<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriberListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_parent_and_school_pages_link_to_each_other(): void
    {
        $this->get(route('subscribers.parents'))
            ->assertOk()
            ->assertSee('Parent subscribers')
            ->assertSee('School subscribers')
            ->assertSee('Send due emails')
            ->assertSee('Search email, delivery ID, consent ID, or child name')
            ->assertSee('>All<', false)
            ->assertSee('Consent removed')
            ->assertSee(route('subscribers.schools'), false);

        $this->get(route('subscribers.schools'))
            ->assertOk()
            ->assertSee('School subscribers')
            ->assertSee('Search email, delivery ID, consent ID, or child name')
            ->assertDontSee('Consent removed')
            ->assertSee(route('subscribers.parents'), false);
    }

    public function test_rows_list_subscribers_with_send_dates_beside_sent_status(): void
    {
        ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'parent_name' => 'Jane Parent',
            'child_name' => 'Sam Child',
            'establishment_name' => 'Example School',
            'delivery_status' => 'active',
            'delivery_id' => 134772,
            'consent_id' => 486732,
            'course_level' => 'Level 2',
            'outcome_survey_course_level' => 'Level 1',
            'journey_confirmation_start_date' => '2026-10-20',
        ]);

        SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'name' => 'Alex Teacher',
            'establishment_name' => 'Moorland School',
            'delivery_status' => 'active',
        ]);

        $this->get(route('subscribers.rows', 'parents'))
            ->assertOk()
            ->assertSee('class="sticky-start"', false)
            ->assertSee('sticky-course sticky-edge', false)
            ->assertSee('parent@example.com')
            ->assertSee('Jane Parent')
            ->assertSeeInOrder([
                'Subscriber',
                'Delivery / Course',
                'Consent confirmation',
                'parent@example.com',
                'Last updated',
                'Delivery ID',
                '134772',
                'Consent ID',
                '486732',
                'Course Level',
                'Level 2',
                'Course Survey Level',
                'Level 1',
            ], false)
            ->assertSee('Consent confirmation')
            ->assertSee('Cycle and helmet check')
            ->assertSee('Pre-course FAQ')
            ->assertSee('Rider report')
            ->assertSee('Parent survey')
            ->assertSee('Send date')
            ->assertSee('value="20/10/2026"', false)
            ->assertSee('has not been sent')
            ->assertSee('Send Consent confirmation email', false)
            ->assertSee('data-sent-column="outcomes_journey_sent_at"', false)
            ->assertDontSee('data-sent-column="outcomes_journey_level_1_sent_at"', false)
            ->assertDontSee('data-sent-column="outcomes_journey_level_2_sent_at"', false)
            ->assertDontSee('>Level 1<', false);

        $this->get(route('subscribers.rows', 'schools'))
            ->assertOk()
            ->assertSee('school@example.com')
            ->assertSee('sticky-start sticky-edge', false)
            ->assertDontSee('sticky-course', false)
            ->assertSeeInOrder([
                'school@example.com',
                'Delivery ID',
                'Last updated',
            ])
            ->assertDontSee('Delivery / Course')
            ->assertDontSee('Consent ID')
            ->assertDontSee('Course Survey Level')
            ->assertSee('Booking confirmation')
            ->assertSee('Consent check')
            ->assertSee('Outstanding consent check')
            ->assertSee('Final consent reminder');
    }

    public function test_subscriber_rows_are_paginated(): void
    {
        foreach (range(1, 16) as $index) {
            ParentSubscriber::create([
                'email_address' => "parent{$index}@example.com",
            ]);
        }

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'page' => 2]))
            ->assertOk()
            ->assertSee('parent1@example.com')
            ->assertDontSee('parent16@example.com')
            ->assertSee('page=1', false);
    }

    public function test_send_dates_can_be_updated_for_both_lists(): void
    {
        $parent = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'journey_confirmation_start_date' => '2026-10-01',
        ]);

        $school = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'pre_course_consent_check_comms_date' => '2026-10-01',
        ]);

        $this->patchJson(route('subscribers.send-date', ['audience' => 'parents', 'subscriber' => $parent->id]), [
            'column' => 'journey_confirmation_start_date',
            'value' => '02/11/2026',
        ])->assertOk()->assertJsonPath('value', '2026-11-02');

        $this->patchJson(route('subscribers.send-date', ['audience' => 'schools', 'subscriber' => $school->id]), [
            'column' => 'pre_course_consent_check_comms_date',
            'value' => '',
        ])->assertOk()->assertJsonPath('value', null);

        $this->assertSame('2026-11-02', $parent->refresh()->journey_confirmation_start_date?->toDateString());
        $this->assertNull($school->refresh()->pre_course_consent_check_comms_date);

        $this->patchJson(route('subscribers.send-date', ['audience' => 'parents', 'subscriber' => $parent->id]), [
            'column' => 'email_address',
            'value' => '2026-11-03',
        ])->assertUnprocessable();
    }

    public function test_send_due_button_logs_the_email_and_marks_it_sent(): void
    {
        Carbon::setTestNow('2026-10-20 08:00:00 Europe/London');
        Mail::fake();

        $parent = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_status' => 'active',
            'parent_name' => 'Jane Parent',
            'child_name' => 'Sam Child',
            'establishment_name' => 'Example School',
            'provider_name' => 'Example Provider',
            'provider_public_email_address' => 'provider@example.com',
            'journey_confirmation_start_date' => '2026-10-20',
        ]);

        $this->postJson(route('subscribers.send-due', 'parents'))
            ->assertOk()
            ->assertJsonPath('message', 'Due parent emails have been sent.');

        $parent->refresh();
        $this->assertNotNull($parent->journey_confirmation_sent_at);

        $log = EmailLog::query()->first();
        $this->assertNotNull($log);
        $this->assertSame($parent->id, $log->parent_subscriber_id);
        $this->assertSame('journey_confirmation_sent_at', $log->sent_column);
        $this->assertSame('parent@example.com', $log->to);
        $this->assertStringContainsString('Sam Child', (string) $log->body);

        $this->get(route('subscribers.rows', 'parents'))
            ->assertOk()
            ->assertSee('View Consent confirmation email sent', false)
            ->assertSee('✓', false);

        $this->getJson(route('subscribers.email', [
            'audience' => 'parents',
            'subscriber' => $parent->id,
            'sentColumn' => 'journey_confirmation_sent_at',
        ]))
            ->assertOk()
            ->assertJsonPath('subject', "We've received Sam Child's Bikeability consent form")
            ->assertJsonPath('to', 'parent@example.com');
    }

    public function test_school_send_due_button_logs_the_email(): void
    {
        Carbon::setTestNow('2026-10-20 08:00:00 Europe/London');
        Mail::fake();

        $school = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'delivery_status' => 'active',
            'first_name' => 'Alex',
            'establishment_name' => 'Moorland School',
            'provider_name' => 'Example Provider',
            'provider_contact_email_address' => 'provider@example.com',
            'booking_link' => 'https://example.com/booking',
            'journey_confirmation_start_date' => '2026-10-20',
        ]);

        $this->postJson(route('subscribers.send-due', 'schools'))
            ->assertOk()
            ->assertJsonPath('message', 'Due school emails have been sent.');

        $this->assertNotNull($school->refresh()->journey_confirmation_sent_at);
        $this->assertDatabaseHas('email_log', [
            'to' => 'school@example.com',
            'school_subscriber_id' => $school->id,
            'sent_column' => 'journey_confirmation_sent_at',
        ]);
    }

    public function test_send_icon_sends_one_unsent_email_and_is_hidden_afterwards(): void
    {
        Mail::fake();

        $parent = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'parent_name' => 'Jane Parent',
            'child_name' => 'Sam Child',
            'establishment_name' => 'Example School',
            'provider_name' => 'Example Provider',
            'provider_public_email_address' => 'provider@example.com',
        ]);

        $school = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'first_name' => 'Alex',
            'establishment_name' => 'Moorland School',
            'provider_name' => 'Example Provider',
            'provider_contact_email_address' => 'provider@example.com',
            'booking_link' => 'https://example.com/booking',
        ]);

        $this->postJson(route('subscribers.send-one', [
            'audience' => 'parents',
            'subscriber' => $parent->id,
            'sentColumn' => 'pre_course_faq_sent_at',
        ]))->assertOk()->assertJsonPath('message', 'Pre-course FAQ email sent.');

        $this->assertNotNull($parent->refresh()->pre_course_faq_sent_at);
        $this->assertNull($parent->journey_confirmation_sent_at);
        $this->assertDatabaseHas('email_log', [
            'to' => 'parent@example.com',
            'parent_subscriber_id' => $parent->id,
            'sent_column' => 'pre_course_faq_sent_at',
            'subject' => 'Frequently Asked Questions, answered',
        ]);

        $this->postJson(route('subscribers.send-one', [
            'audience' => 'parents',
            'subscriber' => $parent->id,
            'sentColumn' => 'pre_course_faq_sent_at',
        ]))->assertUnprocessable();

        $this->get(route('subscribers.rows', 'parents'))
            ->assertOk()
            ->assertDontSee('Send Pre-course FAQ email', false)
            ->assertSee('Send Consent confirmation email', false);

        $this->postJson(route('subscribers.send-one', [
            'audience' => 'schools',
            'subscriber' => $school->id,
            'sentColumn' => 'final_consent_reminder_sent_at',
        ]))->assertOk()->assertJsonPath('message', 'Final consent reminder email sent.');

        $this->assertNotNull($school->refresh()->final_consent_reminder_sent_at);
        $this->assertDatabaseHas('email_log', [
            'to' => 'school@example.com',
            'school_subscriber_id' => $school->id,
            'sent_column' => 'final_consent_reminder_sent_at',
        ]);
    }

    public function test_lists_can_be_filtered_by_status_and_email_and_status_can_be_changed(): void
    {
        $active = ParentSubscriber::create([
            'email_address' => 'active.parent@example.com',
            'child_name' => 'Sam Child',
            'delivery_status' => 'active',
            'delivery_id' => 134772,
        ]);

        ParentSubscriber::create([
            'email_address' => 'removed.parent@example.com',
            'delivery_status' => 'consent removed',
            'delivery_id' => 77,
            'consent_id' => 88,
        ]);

        $school = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'delivery_status' => 'active',
            'delivery_id' => 15,
        ]);

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'status' => 'consent removed']))
            ->assertOk()
            ->assertSee('removed.parent@example.com')
            ->assertDontSee('Send now', false)
            ->assertSee('status-removed', false)
            ->assertSee('status-active', false)
            ->assertDontSee('subscriber-removed', false)
            ->assertSee('77')
            ->assertSee('88')
            ->assertDontSee('active.parent@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'status' => 'deleted', 'email' => 'parent@example.com']))
            ->assertOk()
            ->assertSee('active.parent@example.com')
            ->assertSee('removed.parent@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'status' => 'deleted', 'search' => '134772']))
            ->assertOk()
            ->assertSee('active.parent@example.com')
            ->assertDontSee('removed.parent@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'search' => '88']))
            ->assertOk()
            ->assertSee('removed.parent@example.com')
            ->assertDontSee('active.parent@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'parents', 'search' => 'sam child']))
            ->assertOk()
            ->assertSee('active.parent@example.com')
            ->assertDontSee('removed.parent@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'schools', 'search' => '15']))
            ->assertOk()
            ->assertSee('school@example.com');

        $this->get(route('subscribers.rows', ['audience' => 'schools', 'status' => 'active']))
            ->assertOk()
            ->assertSee('school@example.com')
            ->assertSee('15')
            ->assertDontSee('Consent removed');

        $this->patchJson(route('subscribers.status', ['audience' => 'parents', 'subscriber' => $active->id]), [
            'status' => 'deleted',
        ])->assertOk()->assertJsonPath('status', 'deleted');

        $this->assertSame('deleted', $active->refresh()->delivery_status);

        $this->patchJson(route('subscribers.status', ['audience' => 'schools', 'subscriber' => $school->id]), [
            'status' => 'consent removed',
        ])->assertUnprocessable();
    }

    public function test_combined_course_uses_the_single_rider_report(): void
    {
        ParentSubscriber::create([
            'email_address' => 'combined@example.com',
            'child_name' => 'Sam Child',
            'course_level' => 'Level 1 & 2 Combined',
            'outcome_survey_course_level' => 'Level 1 & 2 Combined',
            'delivery_status' => 'active',
            'outcomes_journey_start_date' => '2026-10-20',
            'outcome_survey_comms_date' => '2026-10-22',
        ]);

        $this->get(route('subscribers.rows', 'parents'))
            ->assertOk()
            ->assertSeeInOrder([
                'Delivery / Course',
                'Course Level',
                'Level 1 &amp; 2 Combined',
                'Course Survey Level',
                'Level 1 &amp; 2 Combined',
            ], false)
            ->assertSee('data-column="outcomes_journey_start_date"', false)
            ->assertSee('data-column="outcome_survey_comms_date"', false)
            ->assertSee('data-sent-column="outcomes_journey_sent_at"', false)
            ->assertSee('data-sent-column="outcome_survey_sent_at"', false)
            ->assertSee('Send Rider report email', false)
            ->assertSee('Send Parent survey email', false)
            ->assertDontSee('data-sent-column="outcomes_journey_level_1_sent_at"', false)
            ->assertDontSee('data-sent-column="outcomes_journey_level_2_sent_at"', false)
            ->assertDontSee('Send Rider report Level', false)
            ->assertDontSee('>Level 1<', false)
            ->assertDontSee('>Level 2<', false);
    }
}
