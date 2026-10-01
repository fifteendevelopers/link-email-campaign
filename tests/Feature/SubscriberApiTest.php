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
            'delivery_id_1' => 'delivery-1',
            'parent_name_1' => 'Jane Parent',
            'child_name_1' => 'Sam Child',
            'comms_start_date_1' => '2026-10-02',
            'resubscribe' => true,
        ];

        $this->postJson('/api/subscribers/parents', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'parent@example.com')
            ->assertJsonPath('data.comms_start_date_1', '2026-10-02');

        $this->postJson('/api/subscribers/parents', [
            ...$payload,
            'email_address' => 'new-parent@example.com',
            'parent_name_1' => 'Janet Parent',
        ])
            ->assertOk()
            ->assertJsonPath('data.parent_name_1', 'Janet Parent');

        $this->assertDatabaseCount('parent_subscribers', 1);
        $this->assertDatabaseHas('parent_subscribers', [
            'consent_id' => 1001,
            'email_address' => 'new-parent@example.com',
            'parent_name_1' => 'Janet Parent',
        ]);
    }

    public function test_campaign_monitor_parent_payload_is_normalized_and_stored(): void
    {
        $this->postJson('/api/subscribers/parents', [
            'EmailAddress' => ' Parent@Example.com ',
            'Name' => 'Jane Parent',
            'CustomFields' => [
                ['Key' => 'Segment3', 'Value' => 'Test Segment'],
                ['Key' => 'DeliveryId3', 'Value' => 'delivery-3'],
                ['Key' => 'DeliveryStatus3', 'Value' => 'active'],
                ['Key' => 'ParentName3', 'Value' => 'Jane Parent'],
                ['Key' => 'ChildName3', 'Value' => 'Sam Child'],
                ['Key' => 'CourseLevel3', 'Value' => 'Level 2'],
                ['Key' => 'EstablishmentName3', 'Value' => 'Example School'],
                ['Key' => 'ProviderName3', 'Value' => 'Example Provider'],
                ['Key' => 'ProviderPublicEmailAddress3', 'Value' => 'provider@example.com'],
                ['Key' => 'CommsStartDate3', 'Value' => '2026-10-02'],
                ['Key' => 'Last Segment Used', 'Value' => 3],
                ['Key' => 'JourneyConfirmationStartDate3', 'Value' => '2026-10-01'],
                ['Key' => 'InstructorsDelivery3', 'Value' => 'Simon, Tommy'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'parent@example.com')
            ->assertJsonPath('data.last_segment_used_3', 3)
            ->assertJsonPath('data.segment_3', 'Test Segment');

        $this->assertDatabaseHas('parent_subscribers', [
            'email_address' => 'parent@example.com',
            'segment_3' => 'Test Segment',
            'last_segment_used_3' => 3,
            'delivery_id_3' => 'delivery-3',
            'delivery_status_3' => 'active',
            'instructors_delivery_3' => 'Simon, Tommy',
        ]);
    }

    public function test_course_completion_payload_updates_the_matching_segment(): void
    {
        ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'segment_3' => 'Test Segment',
            'delivery_id_3' => 'delivery-3',
        ]);

        $this->postJson('/api/emails/parent/course-completion', [
            'EmailAddress' => 'parent@example.com',
            'Name' => 'Jane Parent',
            'CustomFields' => [
                ['Key' => 'OutcomesJourneyStartDate3', 'Value' => '2026-10-20'],
                ['Key' => 'BikeabilityClubLink3', 'Value' => 'https://club.example.com'],
                ['Key' => 'SurveyLink3', 'Value' => 'https://survey.example.com'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ])
            ->assertOk()
            ->assertJsonPath('data.outcomes_journey_start_date_3', '2026-10-20')
            ->assertJsonPath('data.bikeability_club_link_3', 'https://club.example.com');

        $this->assertDatabaseCount('parent_subscribers', 1);
        $this->assertDatabaseHas('parent_subscribers', [
            'segment_3' => 'Test Segment',
            'survey_link_3' => 'https://survey.example.com',
        ]);
    }

    public function test_delivery_status_payload_updates_every_supplied_segment(): void
    {
        ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'segment_1' => 'First segment',
            'segment_2' => 'Second segment',
            'delivery_id_1' => 'delivery-1',
            'delivery_id_2' => 'delivery-2',
        ]);

        $this->postJson('/api/emails/parent/delivery-deleted', [
            'EmailAddress' => 'parent@example.com',
            'CustomFields' => [
                ['Key' => 'deliveryStatus1', 'Value' => 'deleted'],
                ['Key' => 'deliveryStatus2', 'Value' => 'deleted'],
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
            'delivery_status_1' => 'deleted',
            'delivery_status_2' => 'deleted',
        ]);
    }

    public function test_parent_subscribers_can_be_filtered_updated_and_deleted(): void
    {
        $subscriber = ParentSubscriber::create([
            'email_address' => 'parent@example.com',
            'delivery_id_1' => 'delivery-1',
            'parent_name_1' => 'Jane Parent',
        ]);

        ParentSubscriber::create([
            'email_address' => 'other@example.com',
            'delivery_id_2' => 'delivery-2',
        ]);

        $this->getJson('/api/subscribers/parents?email=parent@example.com')
            ->assertOk()
            ->assertJsonPath('id', $subscriber->id);

        $this->patchJson("/api/subscribers/parents/{$subscriber->id}", [
            'delivery_status_1' => 'completed',
        ])
            ->assertOk()
            ->assertJsonPath('data.delivery_status_1', 'completed');

        $this->deleteJson('/api/subscribers/parents?email=parent@example.com&delivery_id=delivery-1')
            ->assertOk()
            ->assertJsonPath('deleted_count', 1);

        $this->assertDatabaseMissing('parent_subscribers', ['id' => $subscriber->id]);
        $this->assertDatabaseCount('parent_subscribers', 1);
    }

    public function test_school_subscriber_crud_endpoints(): void
    {
        $response = $this->postJson('/api/subscribers/schools', [
            'consent_id' => 2001,
            'email_address' => 'school@example.com',
            'delivery_id_1' => 'delivery-10',
            'first_name_1' => 'Alex',
            'establishment_name_1' => 'Example School',
            'booking_link_1' => 'https://example.com/bookings/10',
            'delivery_start_date_1' => '2026-11-10',
        ])
            ->assertCreated()
            ->assertJsonPath('data.delivery_start_date_1', '2026-11-10');

        $subscriberId = $response->json('data.id');

        $this->getJson("/api/subscribers/schools/{$subscriberId}")
            ->assertOk()
            ->assertJsonPath('data.establishment_name_1', 'Example School');

        $this->patchJson("/api/subscribers/schools/{$subscriberId}", [
            'first_name_1' => 'Alexa',
        ])
            ->assertOk()
            ->assertJsonPath('data.first_name_1', 'Alexa');

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
                ['Key' => 'Segment2', 'Value' => 'School segment'],
                ['Key' => 'Last Segment Used', 'Value' => 2],
                ['Key' => 'DeliveryId2', 'Value' => 'delivery-10'],
                ['Key' => 'DeliveryStatus2', 'Value' => 'active'],
                ['Key' => 'DeliveryStartDate2', 'Value' => '2026-11-10'],
                ['Key' => 'CourseCommsStartDate2', 'Value' => '2026-10-20'],
                ['Key' => 'FirstName2', 'Value' => 'Alex'],
                ['Key' => 'ProviderName2', 'Value' => 'Cycle Provider'],
                ['Key' => 'ProviderContactEmailAddress2', 'Value' => 'provider@example.com'],
                ['Key' => 'BookingLink2', 'Value' => 'https://example.com/bookings/10'],
                ['Key' => 'EstablishmentName2', 'Value' => 'Example School'],
                ['Key' => 'JourneyConfirmationStartDate2', 'Value' => '2026-10-15'],
            ],
            'Resubscribe' => true,
            'RestartSubscriptionBasedAutoresponders' => true,
            'ConsentToTrack' => 'Yes',
            'ConsentToSendSms' => 'Yes',
        ];

        $this->postJson('/api/emails/school/booking-completed', $payload)
            ->assertCreated()
            ->assertJsonPath('data.email_address', 'school@example.com')
            ->assertJsonPath('data.last_segment_used_2', 2)
            ->assertJsonPath('data.segment_2', 'School segment')
            ->assertJsonPath('data.delivery_id_2', 'delivery-10')
            ->assertJsonPath('data.delivery_start_date_2', '2026-11-10')
            ->assertJsonPath('data.journey_confirmation_start_date_2', '2026-10-15');

        $this->assertDatabaseHas('school_subscribers', [
            'email_address' => 'school@example.com',
            'segment_2' => 'School segment',
            'last_segment_used_2' => 2,
            'delivery_id_2' => 'delivery-10',
            'first_name_2' => 'Alex',
            'provider_contact_email_address_2' => 'provider@example.com',
            'delivery_status_2' => 'active',
        ]);

        $this->postJson('/api/emails/school/delivery-deleted', [
            'EmailAddress' => 'school@example.com',
            'CustomFields' => [
                ['Key' => 'deliveryStatus2', 'Value' => 'deleted'],
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
            SchoolSubscriber::query()->where('email_address', 'school@example.com')->value('delivery_status_2'),
        );
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
