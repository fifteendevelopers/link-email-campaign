<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ParentSubscriber;
use App\Models\SchoolSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_SECRET = 'test-api-secret';

    private const API_USERNAME = 'link-email-campaign-user';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.link_email_campaign.username' => self::API_USERNAME,
            'services.link_email_campaign.secret' => self::API_SECRET,
        ]);

        $this->withBasicAuth(self::API_USERNAME, self::API_SECRET);
    }

    public function test_subscriber_api_rejects_invalid_credentials(): void
    {
        $this->withBasicAuth('invalid-user', 'invalid-secret')
            ->getJson('/api/subscribers/parents')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic realm="Campaign API"')
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_parent_subscriber_can_be_created_and_updated_by_consent_id(): void
    {
        $payload = [
            'consent_id' => 1001,
            'email_address' => ' Parent@Example.com ',
            'delivery_id' => 1,
            'parent_name' => 'Jane Parent',
            'child_name' => 'Sam Child',
            'comms_start_date' => '2026-10-02',
            'resubscribe' => true,
        ];

        $this->postJson('/api/subscribers/parents', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'parent@example.com')
            ->assertJsonPath('data.comms_start_date', '2026-10-02')
            ->assertJsonPath('data.cycle_and_helmet_check_comms_date', '2026-10-02')
            ->assertJsonPath('data.pre_course_faq_comms_date', '2026-10-07');

        $this->postJson('/api/subscribers/parents', [
            ...$payload,
            'parent_name' => 'Janet Parent',
        ])
            ->assertOk()
            ->assertJsonPath('data.parent_name', 'Janet Parent');

        $this->assertDatabaseCount('parent_subscribers', 1);

        $this->postJson('/api/subscribers/parents', [
            ...$payload,
            'consent_id' => 1002,
            'child_name' => 'Alex Child',
        ])->assertCreated();

        $this->postJson('/api/subscribers/parents', [
            ...$payload,
            'email_address' => 'second-parent@example.com',
            'parent_name' => 'Jon Parent',
        ])->assertCreated();

        $this->assertDatabaseCount('parent_subscribers', 3);
        $this->assertDatabaseHas('parent_subscribers', [
            'consent_id' => 1001,
            'email_address' => 'parent@example.com',
            'parent_name' => 'Janet Parent',
        ]);
        $this->assertDatabaseHas('parent_subscribers', [
            'consent_id' => 1002,
            'email_address' => 'parent@example.com',
            'child_name' => 'Alex Child',
        ]);
        $this->assertDatabaseHas('parent_subscribers', [
            'consent_id' => 1001,
            'email_address' => 'second-parent@example.com',
            'parent_name' => 'Jon Parent',
        ]);
    }

    public function test_campaign_monitor_parent_payload_is_normalized_and_stored(): void
    {
        $this->postJson('/api/subscribers/parents', [
            'EmailAddress' => ' Parent@Example.com ',
            'Name' => 'Jane Parent',
            'CustomFields' => [
                ['Key' => 'DeliveryId', 'Value' => 3],
                ['Key' => 'DeliveryStatus', 'Value' => 'active'],
                ['Key' => 'ParentName', 'Value' => 'Jane Parent'],
                ['Key' => 'ChildName', 'Value' => 'Sam Child'],
                ['Key' => 'CourseLevel', 'Value' => ''],
                ['Key' => 'EstablishmentName', 'Value' => 'Example School'],
                ['Key' => 'ProviderName', 'Value' => 'Example Provider'],
                ['Key' => 'ProviderPublicEmailAddress', 'Value' => 'provider@example.com'],
                ['Key' => 'CommsStartDate', 'Value' => '2026-10-02'],
                ['Key' => 'JourneyConfirmationStartDate', 'Value' => '2026-10-01'],
                ['Key' => 'InstructorsDelivery', 'Value' => 'Simon, Tommy'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'parent@example.com')
            ->assertJsonPath('data.course_level', null);

        $this->assertDatabaseHas('parent_subscribers', [
            'email_address' => 'parent@example.com',
            'delivery_id' => 3,
            'delivery_status' => 'active',
            'instructors_delivery' => 'Simon, Tommy',
        ]);
    }

    public function test_campaign_monitor_parent_payload_accepts_an_empty_custom_field_value(): void
    {
        $this->postJson('/api/subscribers/parents', [
            'EmailAddress' => 'freya.walsh.parent@example.com',
            'Name' => 'Morgan Walsh',
            'CustomFields' => [
                ['Key' => 'DeliveryId', 'Value' => 127110],
                ['Key' => 'DeliveryStatus', 'Value' => 'active'],
                ['Key' => 'ParentName', 'Value' => 'Morgan Walsh'],
                ['Key' => 'ChildName', 'Value' => 'Freya'],
                ['Key' => 'CourseLevel', 'Value' => ''],
                ['Key' => 'EstablishmentName', 'Value' => 'Ingleton CofE Primary School'],
                ['Key' => 'ProviderName', 'Value' => 'Durham County Council (TP)'],
                ['Key' => 'ProviderPublicEmailAddress', 'Value' => 'colin.young@Durham.gov.uk'],
                ['Key' => 'CommsStartDate', 'Value' => '2026-11-09T10:03:35.000000Z'],
                ['Key' => 'JourneyConfirmationStartDate', 'Value' => '2026-10-29'],
                ['Key' => 'InstructorsDelivery', 'Value' => 'Test List : Simon Howarth, Tommy Tucker, Jimmy Ridehard'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'freya.walsh.parent@example.com')
            ->assertJsonPath('data.name', 'Morgan Walsh')
            ->assertJsonPath('data.delivery_id', 127110)
            ->assertJsonPath('data.course_level', null)
            ->assertJsonPath('data.comms_start_date', '2026-11-09')
            ->assertJsonPath('data.journey_confirmation_start_date', '2026-10-29')
            ->assertJsonPath(
                'data.instructors_delivery',
                'Test List : Simon Howarth, Tommy Tucker, Jimmy Ridehard',
            );
    }

    public function test_parent_object_custom_fields_are_stored(): void
    {
        $payload = [
            'name' => 'Robin Bennett',
            'email' => 'isla.bennett.parent@example.com',
            'phoneNumber' => '07700900201',
            'childName' => 'Isla',
            'customFields' => [
                'deliveryId' => 127110,
                'parentName' => 'Robin Bennett',
                'childName' => 'Isla',
                'establishmentName' => 'Ingleton CofE Primary School',
                'deliveryDate' => '2026-11-23',
                'commsStartDate' => '2026-11-09T09:32:30.000000Z',
                'courseLevel' => '',
                'providerName' => 'Durham County Council (TP)',
                'providerPublicEmailAddress' => 'colin.young@Durham.gov.uk',
            ],
            'JourneyConfirmationStartDate' => '2026-10-29',
        ];

        $this->postJson('/api/subscribers/parents', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'isla.bennett.parent@example.com')
            ->assertJsonPath('data.name', 'Robin Bennett')
            ->assertJsonPath('data.delivery_id', 127110)
            ->assertJsonPath('data.parent_name', 'Robin Bennett')
            ->assertJsonPath('data.child_name', 'Isla')
            ->assertJsonPath('data.establishment_name', 'Ingleton CofE Primary School')
            ->assertJsonPath('data.course_level', null)
            ->assertJsonPath('data.provider_name', 'Durham County Council (TP)')
            ->assertJsonPath('data.provider_public_email_address', 'colin.young@Durham.gov.uk')
            ->assertJsonPath('data.comms_start_date', '2026-11-09')
            ->assertJsonPath('data.cycle_and_helmet_check_comms_date', '2026-11-09')
            ->assertJsonPath('data.pre_course_faq_comms_date', '2026-11-14')
            ->assertJsonPath('data.journey_confirmation_start_date', '2026-10-29');

        $this->assertDatabaseHas('parent_subscribers', [
            'email_address' => 'isla.bennett.parent@example.com',
            'delivery_id' => 127110,
            'journey_confirmation_start_date' => '2026-10-29',
            'comms_start_date' => '2026-11-09',
        ]);

        $this->postJson('/api/subscribers/parents', [
            'email' => 'isla.bennett.parent@example.com',
            'customFields' => [
                'childName' => 'Isla Bennett',
                'parentName' => 'Robin Bennett',
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.child_name', 'Isla Bennett')
            ->assertJsonPath('data.delivery_id', 127110);

        $this->postJson('/api/subscribers/parents', [
            'email' => 'another-parent@example.com',
            'customFields' => [
                'parentName' => 'Robin Bennett',
            ],
        ])->assertCreated()
            ->assertJsonPath('data.parent_name', 'Robin Bennett');
    }

    public function test_course_completion_payload_updates_the_subscriber(): void
    {
        ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_id' => 3,
        ]);

        $this->postJson('/api/emails/parent/course-completion', [
            'EmailAddress' => 'parent@example.com',
            'Name' => 'Jane Parent',
            'CustomFields' => [
                ['Key' => 'OutcomesJourneyStartDate', 'Value' => '2026-10-20'],
                ['Key' => 'BikeabilityClubLink', 'Value' => 'https://club.example.com'],
                ['Key' => 'SurveyLink', 'Value' => 'https://survey.example.com'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertOk()
            ->assertJsonPath('data.outcomes_journey_start_date', '2026-10-20')
            ->assertJsonPath('data.outcome_survey_comms_date', '2026-10-22')
            ->assertJsonPath('data.bikeability_club_link', 'https://club.example.com');

        $this->assertDatabaseCount('parent_subscribers', 1);
        $this->assertDatabaseHas('parent_subscribers', [
            'delivery_id' => 3,
            'survey_link' => 'https://survey.example.com',
            'outcome_survey_comms_date' => '2026-10-22',
        ]);
    }

    public function test_delivery_status_payload_updates_the_subscriber(): void
    {
        ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_id' => 1,
        ]);

        $this->postJson('/api/emails/parent/delivery-deleted', [
            'EmailAddress' => 'parent@example.com',
            'CustomFields' => [
                ['Key' => 'deliveryStatus', 'Value' => 'deleted'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1);

        $this->assertDatabaseCount('parent_subscribers', 1);
        $this->assertDatabaseHas('parent_subscribers', [
            'email_address' => 'parent@example.com',
            'delivery_status' => 'deleted',
        ]);
    }

    public function test_changed_parent_communication_dates_reset_sent_markers(): void
    {
        $subscriber = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'comms_start_date' => '2026-10-02',
            'cycle_and_helmet_check_comms_date' => '2026-10-02',
            'pre_course_faq_comms_date' => '2026-10-07',
            'cycle_and_helmet_check_sent_at' => '2026-10-02 08:00:00',
            'pre_course_faq_sent_at' => '2026-10-07 08:00:00',
        ]);

        $this->patchJson("/api/subscribers/parents/{$subscriber->id}", [
            'comms_start_date' => '2026-10-02',
        ])->assertOk();

        $subscriber->refresh();
        $this->assertNotNull($subscriber->cycle_and_helmet_check_sent_at);
        $this->assertNotNull($subscriber->pre_course_faq_sent_at);

        $this->patchJson("/api/subscribers/parents/{$subscriber->id}", [
            'comms_start_date' => '2026-11-01',
        ])
            ->assertOk()
            ->assertJsonPath('data.cycle_and_helmet_check_comms_date', '2026-11-01')
            ->assertJsonPath('data.pre_course_faq_comms_date', '2026-11-06');

        $subscriber->refresh();
        $this->assertNull($subscriber->cycle_and_helmet_check_sent_at);
        $this->assertNull($subscriber->pre_course_faq_sent_at);
    }

    public function test_parent_subscribers_can_be_filtered_updated_and_deleted(): void
    {
        $subscriber = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_id' => 1,
            'parent_name' => 'Jane Parent',
        ]);

        ParentSubscriber::create([
            'email_address' => 'other@example.com',
            'delivery_id' => 2,
        ]);

        $this->getJson('/api/subscribers/parents?email=parent@example.com')
            ->assertOk()
            ->assertJsonPath('id', $subscriber->id);

        $this->patchJson("/api/subscribers/parents/{$subscriber->id}", [
            'delivery_status' => 'completed',
        ])
            ->assertOk()
            ->assertJsonPath('data.delivery_status', 'completed');

        $this->deleteJson('/api/subscribers/parents?email=parent@example.com&delivery_id=1')
            ->assertOk()
            ->assertJsonPath('deleted_count', 1);

        $this->assertDatabaseMissing('parent_subscribers', ['id' => $subscriber->id]);
        $this->assertDatabaseCount('parent_subscribers', 1);
    }

    public function test_school_subscriber_crud_endpoints(): void
    {
        $response = $this->postJson('/api/subscribers/schools', [
            'email_address' => 'school@example.com',
            'delivery_id' => 10,
            'first_name' => 'Alex',
            'establishment_name' => 'Example School',
            'booking_link' => 'https://example.com/bookings/10',
            'delivery_start_date' => '2026-11-10',
        ])
            ->assertCreated()
            ->assertJsonPath('data.delivery_start_date', '2026-11-10');

        $subscriberId = $response->json('data.id');

        $this->getJson('/api/subscribers/schools?email=school@example.com')
            ->assertOk()
            ->assertJsonPath('establishment_name', 'Example School');

        $this->patchJson("/api/subscribers/schools/{$subscriberId}", [
            'first_name' => 'Alexa',
        ])
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Alexa');

        $this->deleteJson("/api/subscribers/schools/{$subscriberId}")
            ->assertNoContent();

        $this->assertDatabaseCount('school_subscribers', 0);
    }

    public function test_school_campaign_payload_is_inserted_and_delivery_can_be_deleted(): void
    {
        $payload = [
            'EmailAddress' => ' School@Example.com ',
            'Name' => 'Example School',
            'CustomFields' => [
                ['Key' => 'DeliveryId', 'Value' => 10],
                ['Key' => 'DeliveryStatus', 'Value' => 'active'],
                ['Key' => 'DeliveryStartDate', 'Value' => '2026-11-10'],
                ['Key' => 'CourseCommsStartDate', 'Value' => '2026-10-20'],
                ['Key' => 'FirstName', 'Value' => 'Alex'],
                ['Key' => 'ProviderName', 'Value' => 'Cycle Provider'],
                ['Key' => 'ProviderContactEmailAddress', 'Value' => 'provider@example.com'],
                ['Key' => 'BookingLink', 'Value' => 'https://example.com/bookings/10'],
                ['Key' => 'EstablishmentName', 'Value' => 'Example School'],
                ['Key' => 'JourneyConfirmationStartDate', 'Value' => '2026-10-15'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ];

        $this->postJson('/api/emails/school/booking-completed', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'school@example.com')
            ->assertJsonPath('data.delivery_id', 10)
            ->assertJsonPath('data.delivery_start_date', '2026-11-10')
            ->assertJsonPath('data.journey_confirmation_start_date', '2026-10-15')
            ->assertJsonPath('data.pre_course_consent_check_comms_date', '2026-10-20')
            ->assertJsonPath('data.outstanding_consent_check_comms_date', '2026-10-27')
            ->assertJsonPath('data.final_consent_reminder_comms_date', '2026-10-31');

        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'delivery_id' => 10,
            'first_name' => 'Alex',
            'provider_contact_email_address' => 'provider@example.com',
            'delivery_status' => 'active',
        ]);

        $this->postJson('/api/emails/school/delivery-deleted', [
            'EmailAddress' => 'school@example.com',
            'CustomFields' => [
                ['Key' => 'deliveryStatus', 'Value' => 'deleted'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1);

        $this->assertSame(
            'deleted',
            SchoolSubscriber::query()->where('email_address', 'school@example.com')->value('delivery_status'),
        );
    }

    public function test_school_subscriber_is_matched_by_email_and_delivery_id(): void
    {
        $payload = [
            'EmailAddress' => 'school@example.com',
            'Name' => 'Example School',
            'CustomFields' => [
                ['Key' => 'DeliveryId', 'Value' => 10],
                ['Key' => 'DeliveryStatus', 'Value' => 'active'],
                ['Key' => 'FirstName', 'Value' => 'Alex'],
                ['Key' => 'EstablishmentName', 'Value' => 'Example School'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ];

        $this->postJson('/api/subscribers/schools', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'school@example.com')
            ->assertJsonPath('data.delivery_id', 10)
            ->assertJsonPath('data.first_name', 'Alex');

        $payload['CustomFields'][2]['Value'] = 'Alexa';

        $this->postJson('/api/subscribers/schools', $payload)
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Alexa')
            ->assertJsonPath('data.delivery_id', 10);

        $payload['CustomFields'][0]['Value'] = 11;
        $payload['CustomFields'][2]['Value'] = 'Sam';

        $this->postJson('/api/subscribers/schools', $payload)
            ->assertCreated()
            ->assertJsonPath('data.delivery_id', 11)
            ->assertJsonPath('data.first_name', 'Sam');

        $this->assertDatabaseCount('school_subscribers', 2);
        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'delivery_id' => 10,
            'first_name' => 'Alexa',
        ]);
        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'delivery_id' => 11,
            'first_name' => 'Sam',
        ]);

        $this->getJson('/api/subscribers/schools?email=school@example.com&delivery_id=11')
            ->assertOk()
            ->assertJsonPath('delivery_id', 11)
            ->assertJsonPath('first_name', 'Sam');

        $this->postJson('/api/emails/school/delivery-deleted', [
            'EmailAddress' => 'school@example.com',
            'delivery_id' => 11,
            'CustomFields' => [
                ['Key' => 'deliveryStatus', 'Value' => 'deleted'],
                ['Key' => 'DeliveryId', 'Value' => 11],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1);

        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'delivery_id' => 10,
            'delivery_status' => 'active',
        ]);
        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'delivery_id' => 11,
            'delivery_status' => 'deleted',
        ]);
    }

    public function test_subscriber_payload_is_validated(): void
    {
        $this->postJson('/api/subscribers/parents', [
            'email_address' => 'not-an-email',
            'resubscribe' => 'invalid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email_address', 'resubscribe']);

        $this->postJson('/api/subscribers/schools', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email_address']);
    }
}
