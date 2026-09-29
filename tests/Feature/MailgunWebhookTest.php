<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EmailTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MailgunWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const string SIGNING_KEY = 'test-signing-key';
    private const string TOKEN = 'test-token';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.mailgun.webhook_signing_key' => self::SIGNING_KEY]);
    }

    public function test_rejects_invalid_signature(): void
    {
        $response = $this->postJson('/mailgun/webhook', [
            'signature' => [
                'timestamp' => (string) time(),
                'token' => self::TOKEN,
                'signature' => 'not-a-valid-signature',
            ],
            'event-data' => ['event' => 'delivered'],
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseHas('activity_logs', [
            'source' => 'MailgunWebhookController->webhook',
        ]);
    }

    public function test_rejects_stale_timestamp(): void
    {
        $staleTimestamp = (string) (time() - 600);

        $response = $this->postJson('/mailgun/webhook', [
            'signature' => $this->buildSignature($staleTimestamp),
            'event-data' => ['event' => 'delivered'],
        ]);

        $response->assertStatus(401);
    }

    public function test_ignores_events_without_tracking_id(): void
    {
        $response = $this->postJson('/mailgun/webhook', $this->buildPayload('delivered', null));

        $response->assertOk()->assertJson(['message' => 'Ignored']);
    }

    public function test_handles_all_event_types(): void
    {
        $eventAssertions = [
            'accepted' => 'accepted_at',
            'rejected' => 'rejected_at',
            'delivered' => 'delivered_at',
            'opened' => 'first_opened_at',
            'clicked' => 'first_clicked_at',
            'temporary_fail' => 'temporary_failed_at',
            'permanent_fail' => 'permanent_failed_at',
            'complained' => 'complained_at',
            'unsubscribed' => 'unsubscribed_at',
        ];

        foreach ($eventAssertions as $event => $timestampColumn) {
            $tracking = EmailTracking::create(['tracking_id' => (string) Str::uuid()]);

            $response = $this->postJson('/mailgun/webhook', $this->buildPayload($event, $tracking->tracking_id));

            $response->assertOk()->assertJson(['message' => 'Ok']);

            $tracking->refresh();
            $this->assertNotNull($tracking->{$timestampColumn}, "Expected {$timestampColumn} to be set for '{$event}' event");
            $this->assertSame($event, $tracking->last_event);
        }
    }

    public function test_handles_camel_case_event_data_key(): void
    {
        $tracking = EmailTracking::create(['tracking_id' => (string) Str::uuid()]);

        $response = $this->postJson('/mailgun/webhook', [
            'signature' => $this->buildSignature((string) time()),
            'eventData' => [
                'event' => 'accepted',
                'timestamp' => microtime(true),
                'user-variables' => ['tracking_id' => $tracking->tracking_id],
            ],
        ]);

        $response->assertOk()->assertJson(['message' => 'Ok']);

        $tracking->refresh();
        $this->assertNotNull($tracking->accepted_at);
        $this->assertSame('accepted', $tracking->last_event);
    }

    public function test_increments_open_and_click_counts(): void
    {
        $tracking = EmailTracking::create(['tracking_id' => (string) Str::uuid()]);

        $this->postJson('/mailgun/webhook', $this->buildPayload('opened', $tracking->tracking_id))->assertOk();
        $this->postJson('/mailgun/webhook', $this->buildPayload('opened', $tracking->tracking_id))->assertOk();
        $this->postJson('/mailgun/webhook', $this->buildPayload('clicked', $tracking->tracking_id))->assertOk();

        $tracking->refresh();
        $this->assertSame(2, $tracking->open_count);
        $this->assertSame(1, $tracking->click_count);
    }

    private function buildPayload(string $event, ?string $trackingId): array
    {
        $eventData = [
            'event' => $event,
            'timestamp' => time(),
        ];

        if ($trackingId !== null) {
            $eventData['user-variables'] = ['tracking_id' => $trackingId];
        }

        return [
            'signature' => $this->buildSignature((string) time()),
            'event-data' => $eventData,
        ];
    }

    private function buildSignature(string $timestamp): array
    {
        return [
            'timestamp' => $timestamp,
            'token' => self::TOKEN,
            'signature' => hash_hmac('sha256', $timestamp.self::TOKEN, self::SIGNING_KEY),
        ];
    }
}
