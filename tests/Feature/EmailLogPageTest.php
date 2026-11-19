<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
use App\Support\SubscriberCommunications;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_logs_page_links_back_to_subscribers_and_searches_rows(): void
    {
        $parent = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'consent_id' => 486732,
            'delivery_id' => 134772,
            'course_level' => 'Level 2',
            'outcome_survey_course_level' => 'Level 1',
        ]);

        $school = SchoolSubscriber::create([
            'email_address' => 'school@example.com',
            'delivery_id' => 15,
        ]);

        $parentLog = EmailLog::create([
            'date' => '2026-10-20 09:30:00',
            'from' => 'campaign@example.com',
            'to' => 'parent@example.com',
            'subject' => 'Consent received',
            'body' => '<p>Stored consent email</p>',
            'parent_subscriber_id' => $parent->id,
            'sent_column' => 'journey_confirmation_sent_at',
        ]);

        EmailLog::create([
            'date' => '2026-10-21 09:30:00',
            'from' => 'campaign@example.com',
            'to' => 'school@example.com',
            'subject' => 'Booking confirmed',
            'school_subscriber_id' => $school->id,
            'sent_column' => 'journey_confirmation_sent_at',
        ]);

        $this->get(route('email-logs.index'))
            ->assertOk()
            ->assertSee('Email logs')
            ->assertSee('Search email, delivery ID, consent ID, or course level')
            ->assertSee('Subscriber Lists')
            ->assertSee(route('subscribers.parents'), false);

        $sentAt = SubscriberCommunications::formatSentAt($parentLog->date);

        $this->get(route('email-logs.rows'))
            ->assertOk()
            ->assertSeeInOrder([
                'Subscriber',
                'Date',
                'From',
                'To',
                'Subject',
                'Consent ID',
                '486732',
                'Delivery ID',
                '134772',
                'Course Level',
                'Level 2',
                'Course Survey Level',
                'Level 1',
            ], false)
            ->assertSee('486732')
            ->assertSee('Level 2')
            ->assertSee('Level 1')
            ->assertSee($sentAt)
            ->assertSee('Consent received')
            ->assertSee('school@example.com')
            ->assertSee('target="_blank"', false)
            ->assertSee(route('email-logs.show', $parentLog), false);

        $this->get(route('email-logs.show', $parentLog))
            ->assertOk()
            ->assertSee('Stored consent email', false);

        $this->get(route('email-logs.rows', ['search' => 'PARENT@EXAMPLE.COM']))
            ->assertOk()
            ->assertSee('parent@example.com')
            ->assertSee('134772')
            ->assertDontSee('school@example.com');

        $this->get(route('email-logs.rows', ['search' => '486732']))
            ->assertOk()
            ->assertSee('parent@example.com')
            ->assertDontSee('school@example.com');

        $this->get(route('email-logs.rows', ['search' => '15']))
            ->assertOk()
            ->assertSee('school@example.com')
            ->assertSee('Booking confirmed')
            ->assertDontSee('parent@example.com');

        $this->get(route('email-logs.rows', ['search' => 'Level 2']))
            ->assertOk()
            ->assertSee('parent@example.com')
            ->assertDontSee('school@example.com');

        $this->get(route('email-logs.rows', ['search' => 'Level 1']))
            ->assertOk()
            ->assertSee('parent@example.com')
            ->assertSee('Course Survey Level')
            ->assertDontSee('school@example.com');
    }

    public function test_email_logs_are_paginated_ten_per_page(): void
    {
        foreach (range(1, 11) as $index) {
            EmailLog::create([
                'date' => Carbon::parse('2026-01-01')->addDays($index),
                'from' => 'campaign@example.com',
                'to' => "person{$index}@example.com",
                'subject' => "Subject {$index}",
            ]);
        }

        $this->get(route('email-logs.rows'))
            ->assertOk()
            ->assertSee('person11@example.com')
            ->assertSee('person2@example.com')
            ->assertDontSee('person1@example.com')
            ->assertSee('page=2', false);

        $this->get(route('email-logs.rows', ['page' => 2]))
            ->assertOk()
            ->assertSee('person1@example.com')
            ->assertDontSee('person2@example.com');
    }
}
