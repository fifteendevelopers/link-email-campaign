<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\CarbonImmutable;

class ParentSubscriberRequest extends SubscriberRequest
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
                'parent_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'child_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'course_level' => ['sometimes', 'nullable', 'string', 'max:255'],
                'establishment_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'provider_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'provider_public_email_address' => ['sometimes', 'nullable', 'email', 'max:255'],
                'comms_start_date' => ['sometimes', 'nullable', 'date'],
                'cycle_and_helment_check_comms_date' => ['sometimes', 'nullable', 'date'],
                'pre_course_faq_comms_date' => ['sometimes', 'nullable', 'date'],
                'instructors_delivery' => ['sometimes', 'nullable', 'string'],
                'outcomes_journey_start_date' => ['sometimes', 'nullable', 'date'],
                'outcome_survey_comms_date' => ['sometimes', 'nullable', 'date'],
                'bikeability_club_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
                'survey_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
            ]),
        ];
    }

    /**
     * Return validated subscriber data with communication dates derived from
     * their campaign journey start dates.
     *
     * @return array<string, mixed>
     */
    public function subscriberAttributes(): array
    {
        $attributes = $this->validated();

        foreach (range(1, 3) as $segment) {
            foreach ([
                'journey_confirmation_start_date',
                'comms_start_date',
                'cycle_and_helment_check_comms_date',
                'pre_course_faq_comms_date',
                'outcomes_journey_start_date',
                'outcome_survey_comms_date',
            ] as $dateField) {
                $dateColumn = "{$dateField}_{$segment}";

                if (array_key_exists($dateColumn, $attributes)) {
                    $attributes[$dateColumn] = $this->dateValue($attributes[$dateColumn])?->toDateString();
                }
            }

            $commsStartDate = "comms_start_date_{$segment}";

            if (array_key_exists($commsStartDate, $attributes)) {
                $date = $this->dateValue($attributes[$commsStartDate]);
                $attributes["cycle_and_helment_check_comms_date_{$segment}"] = $date?->toDateString();
                $attributes["pre_course_faq_comms_date_{$segment}"] = $date?->addDays(5)->toDateString();
            }

            $outcomesStartDate = "outcomes_journey_start_date_{$segment}";

            if (array_key_exists($outcomesStartDate, $attributes)) {
                $date = $this->dateValue($attributes[$outcomesStartDate]);
                $attributes["outcome_survey_comms_date_{$segment}"] = $date?->addDays(2)->toDateString();
            }
        }

        return $attributes;
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
            'ParentName' => 'parent_name',
            'ChildName' => 'child_name',
            'CourseLevel' => 'course_level',
            'EstablishmentName' => 'establishment_name',
            'ProviderName' => 'provider_name',
            'ProviderPublicEmailAddress' => 'provider_public_email_address',
            'CommsStartDate' => 'comms_start_date',
            'CycleAndHelmentCheckCommsDate' => 'cycle_and_helment_check_comms_date',
            'PreCourseFaqCommsDate' => 'pre_course_faq_comms_date',
            'JourneyConfirmationStartDate' => 'journey_confirmation_start_date',
            'InstructorsDelivery' => 'instructors_delivery',
            'OutcomesJourneyStartDate' => 'outcomes_journey_start_date',
            'BikeabilityClubLink' => 'bikeability_club_link',
            'SurveyLink' => 'survey_link',
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

    private function dateValue(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse((string) $value);
    }
}
