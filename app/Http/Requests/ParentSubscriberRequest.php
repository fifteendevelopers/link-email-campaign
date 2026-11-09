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

        foreach ([
            'journey_confirmation_start_date',
            'comms_start_date',
            'cycle_and_helment_check_comms_date',
            'pre_course_faq_comms_date',
            'outcomes_journey_start_date',
            'outcome_survey_comms_date',
        ] as $dateField) {
            if (array_key_exists($dateField, $attributes)) {
                $attributes[$dateField] = $this->dateValue($attributes[$dateField])?->toDateString();
            }
        }

        if (array_key_exists('comms_start_date', $attributes)) {
            $date = $this->dateValue($attributes['comms_start_date']);
            $attributes['cycle_and_helment_check_comms_date'] = $date?->toDateString();
            $attributes['pre_course_faq_comms_date'] = $date?->addDays(5)->toDateString();
        }

        if (array_key_exists('outcomes_journey_start_date', $attributes)) {
            $date = $this->dateValue($attributes['outcomes_journey_start_date']);
            $attributes['outcome_survey_comms_date'] = $date?->addDays(2)->toDateString();
        }

        return $attributes;
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
        $attributes = $this->baseAttributes();
        $this->campaignCustomFields = $this->indexedCustomFields();

        foreach ($this->mappedCustomFields() as $column => $value) {
            $attributes[$column] = $value;
        }

        return $this->applyRootFieldAttributes($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseAttributes(): array
    {
        return array_filter([
            'email_address' => $this->firstInput('email_address', 'email', 'EmailAddress'),
            'name' => $this->firstInput('name', 'Name'),
            'resubscribe' => $this->firstInput('resubscribe', 'Resubscribe'),
            'restart_subscription_based_autoresponders' => $this->firstInput(
                'restart_subscription_based_autoresponders',
                'RestartSubscriptionBasedAutoresponders',
            ),
            'consent_to_track' => $this->firstInput('consent_to_track', 'ConsentToTrack'),
            'consent_to_send_sms' => $this->firstInput('consent_to_send_sms', 'ConsentToSendSms'),
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function mappedCustomFields(): array
    {
        $attributes = [];

        foreach ($this->campaignCustomFields as $key => $value) {
            if (! is_string($key) || $value === null || $value === '' || is_array($value)) {
                continue;
            }

            $normalized = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $key));
            $databaseKey = $this->fieldMap()[$normalized] ?? null;

            if ($databaseKey === null) {
                continue;
            }

            $attributes[$databaseKey] = $value;
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    private function fieldMap(): array
    {
        return [
            'deliveryid' => 'delivery_id',
            'deliverystatus' => 'delivery_status',
            'parentname' => 'parent_name',
            'childname' => 'child_name',
            'courselevel' => 'course_level',
            'establishmentname' => 'establishment_name',
            'providername' => 'provider_name',
            'providerpublicemailaddress' => 'provider_public_email_address',
            'commsstartdate' => 'comms_start_date',
            'cycleandhelmentcheckcommsdate' => 'cycle_and_helment_check_comms_date',
            'precoursefaqcommsdate' => 'pre_course_faq_comms_date',
            'journeyconfirmationstartdate' => 'journey_confirmation_start_date',
            'instructorsdelivery' => 'instructors_delivery',
            'outcomesjourneystartdate' => 'outcomes_journey_start_date',
            'bikeabilityclublink' => 'bikeability_club_link',
            'surveylink' => 'survey_link',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function applyRootFieldAttributes(array $attributes): array
    {
        $rootFields = [
            'child_name' => ['childName', 'ChildName'],
            'journey_confirmation_start_date' => ['JourneyConfirmationStartDate', 'journeyConfirmationStartDate'],
        ];

        foreach ($rootFields as $column => $keys) {
            if (array_key_exists($column, $attributes)) {
                continue;
            }

            $value = $this->firstInput(...$keys);

            if (is_scalar($value) && $value !== '') {
                $attributes[$column] = $value;
            }
        }

        return $attributes;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function rawCustomFields(): array
    {
        $customFields = $this->firstInput('customFields', 'CustomFields');

        return is_array($customFields) ? $customFields : [];
    }

    /**
     * @param  array<int|string, mixed>  $customFields
     */
    private function isKeyValueList(array $customFields): bool
    {
        if ($customFields === [] || ! array_is_list($customFields)) {
            return false;
        }

        $first = $customFields[0] ?? null;

        return is_array($first) && (array_key_exists('Key', $first) || array_key_exists('key', $first));
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedCustomFields(): array
    {
        $customFields = $this->rawCustomFields();

        if ($customFields === []) {
            return [];
        }

        if ($this->isKeyValueList($customFields)) {
            $indexedFields = [];

            foreach ($customFields as $customField) {
                if (! is_array($customField)) {
                    continue;
                }

                $key = $customField['Key'] ?? $customField['key'] ?? null;
                $hasValue = array_key_exists('Value', $customField) || array_key_exists('value', $customField);

                if (! is_string($key) || ! $hasValue) {
                    continue;
                }

                $indexedFields[$key] = array_key_exists('Value', $customField)
                    ? $customField['Value']
                    : $customField['value'];
            }

            return $indexedFields;
        }

        $indexedFields = [];

        foreach ($customFields as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $indexedFields[$key] = $value;
        }

        return $indexedFields;
    }

    private function firstInput(string ...$keys): mixed
    {
        $payload = $this->all();

        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null) {
                return $payload[$key];
            }
        }

        foreach ($payload as $payloadKey => $value) {
            if (! is_string($payloadKey) || $value === null) {
                continue;
            }

            foreach ($keys as $key) {
                if (strcasecmp($payloadKey, $key) === 0) {
                    return $value;
                }
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
