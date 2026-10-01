<?php

declare(strict_types=1);

namespace App\Http\Requests;

class SchoolSubscriberRequest extends SubscriberRequest
{
    /**
     * @var array<string, mixed>
     */
    private array $campaignCustomFields = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            ...$this->commonRules(),
            ...$this->segmentedRules([
                'delivery_start_date' => ['sometimes', 'nullable', 'date'],
                'course_comms_start_date' => ['sometimes', 'nullable', 'date'],
                'pre_course_consent_check_comms_date' => ['sometimes', 'nullable', 'date'],
                'outstanding_consent_check_comms_date' => ['sometimes', 'nullable', 'date'],
                'final_consent_reminder_comms_date' => ['sometimes', 'nullable', 'date'],
                'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'provider_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'provider_contact_email_address' => ['sometimes', 'nullable', 'email', 'max:255'],
                'booking_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
                'establishment_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            ]),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function deliveryStatusUpdates(): array
    {
        $updates = [];

        foreach (range(1, 3) as $segment) {
            $value = $this->customField("DeliveryStatus{$segment}");

            if ($value !== null) {
                $updates[$segment] = (string) $value;
            }
        }

        return $updates;
    }

    protected function prepareForValidation(): void
    {
        $campaignAttributes = $this->campaignAttributes();

        if ($campaignAttributes !== []) {
            $this->merge($campaignAttributes);
        }

        parent::prepareForValidation();
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignAttributes(): array
    {
        $attributes = array_filter([
            'email_address' => $this->input('EmailAddress'),
            'name' => $this->input('Name'),
            'resubscribe' => $this->input('Resubscribe'),
            'restart_subscription_based_autoresponders' => $this->input('RestartSubscriptionBasedAutoresponders'),
            'consent_to_track' => $this->input('ConsentToTrack'),
            'consent_to_send_sms' => $this->input('ConsentToSendSms'),
        ], static fn (mixed $value): bool => $value !== null);

        $this->campaignCustomFields = $this->campaignCustomFields();

        if ($this->campaignCustomFields === []) {
            return $attributes;
        }

        $lastSegmentUsed = $this->customField('Last Segment Used');

        if ($lastSegmentUsed !== null) {
            $lastSegmentNumber = filter_var(
                $lastSegmentUsed,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 3]],
            );

            if ($lastSegmentNumber !== false) {
                $attributes["last_segment_used_{$lastSegmentNumber}"] = $lastSegmentUsed;
            }
        }

        $fieldMap = [
            'Segment' => 'segment',
            'DeliveryId' => 'delivery_id',
            'DeliveryStatus' => 'delivery_status',
            'DeliveryStartDate' => 'delivery_start_date',
            'CourseCommsStartDate' => 'course_comms_start_date',
            'PreCourseConsentCheckCommsDate' => 'pre_course_consent_check_comms_date',
            'OutstandingConsentCheckCommsDate' => 'outstanding_consent_check_comms_date',
            'FinalConsentReminderCommsDate' => 'final_consent_reminder_comms_date',
            'FirstName' => 'first_name',
            'ProviderName' => 'provider_name',
            'ProviderContactEmailAddress' => 'provider_contact_email_address',
            'BookingLink' => 'booking_link',
            'EstablishmentName' => 'establishment_name',
            'JourneyConfirmationStartDate' => 'journey_confirmation_start_date',
        ];

        foreach (range(1, 3) as $segment) {
            $numberedLastSegmentUsed = $this->customField("Last Segment Used{$segment}")
                ?? $this->customField("LastSegmentUsed{$segment}");

            if ($numberedLastSegmentUsed !== null) {
                $attributes["last_segment_used_{$segment}"] = $numberedLastSegmentUsed;
            }

            foreach ($fieldMap as $campaignKey => $databaseKey) {
                $value = $this->customField($campaignKey.$segment);

                if ($value !== null) {
                    $attributes["{$databaseKey}_{$segment}"] = $value;
                }
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignCustomFields(): array
    {
        $customFields = $this->input('CustomFields', []);

        if (! is_array($customFields)) {
            return [];
        }

        $indexedFields = [];

        foreach ($customFields as $customField) {
            if (! is_array($customField)) {
                continue;
            }

            $key = $customField['Key'] ?? null;

            if (! is_string($key) || ! array_key_exists('Value', $customField)) {
                continue;
            }

            $indexedFields[$key] = $customField['Value'];
        }

        return $indexedFields;
    }

    private function customField(string $expectedKey): mixed
    {
        foreach ($this->campaignCustomFields as $key => $value) {
            if (strcasecmp($key, $expectedKey) === 0) {
                return $value;
            }
        }

        return null;
    }
}
