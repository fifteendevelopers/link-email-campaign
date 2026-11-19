<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApiLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLogTest extends TestCase
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

    public function test_incoming_subscriber_requests_are_written_to_api_logs(): void
    {
        $this->postJson('/api/subscribers/parents?source=campaign', [
            'consent_id' => 1001,
            'email_address' => 'parent@example.com',
            'parent_name' => 'Jane Parent',
            'secret' => 'should-not-be-stored',
        ])->assertCreated();

        $log = ApiLog::query()->sole();

        $this->assertSame('POST', $log->method);
        $this->assertSame('api/subscribers/parents', $log->path);
        $this->assertSame('api.subscribers.parents.upsert', $log->route_name);
        $this->assertSame(201, $log->status_code);
        $this->assertSame('campaign', $log->query['source']);
        $this->assertSame(1001, $log->payload['consent_id']);
        $this->assertSame('parent@example.com', $log->payload['email_address']);
        $this->assertSame('[redacted]', $log->payload['secret']);
        $this->assertNotNull($log->duration_ms);
        $this->assertStringContainsString('Parent subscriber created successfully.', (string) $log->response);
        $this->assertStringNotContainsString(self::API_SECRET, (string) json_encode($log->getAttributes()));
        $this->assertStringNotContainsString('should-not-be-stored', (string) json_encode($log->getAttributes()));
    }

    public function test_rejected_subscriber_requests_are_logged(): void
    {
        $this->withBasicAuth('invalid-user', 'invalid-secret')
            ->getJson('/api/subscribers/parents')
            ->assertUnauthorized();

        $log = ApiLog::query()->sole();

        $this->assertSame('GET', $log->method);
        $this->assertSame('api/subscribers/parents', $log->path);
        $this->assertSame(401, $log->status_code);
        $this->assertStringContainsString('Unauthenticated.', (string) $log->response);
        $this->assertStringNotContainsString('invalid-secret', (string) json_encode($log->getAttributes()));
    }

    public function test_validation_failures_are_logged_with_their_response(): void
    {
        $this->postJson('/api/subscribers/parents', [])
            ->assertUnprocessable();

        $log = ApiLog::query()->sole();

        $this->assertSame(422, $log->status_code);
        $this->assertNull($log->payload);
        $this->assertStringContainsString('email_address', (string) $log->response);
    }
}
