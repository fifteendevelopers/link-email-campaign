<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function commonRules(): array
    {
        $rules = [
            'consent_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'email_address' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'email',
                'max:255',
            ],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'resubscribe' => ['sometimes', 'boolean'],
            'restart_subscription_based_autoresponders' => ['sometimes', 'boolean'],
            'consent_to_track' => ['sometimes', Rule::in(['Yes', 'No', 'Unchanged'])],
            'consent_to_send_sms' => ['sometimes', Rule::in(['Yes', 'No', 'Unchanged'])],
        ];

        return [
            ...$rules,
            ...$this->segmentedRules([
                'segment' => ['sometimes', 'nullable', 'in:1,2,3'],
                'last_segment_used' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:3'],
                'delivery_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'delivery_status' => ['sometimes', 'nullable', 'string', 'max:100'],
                'journey_confirmation_start_date' => ['sometimes', 'nullable', 'date'],
            ]),
        ];
    }

    /**
     * @param  array<string, array<int, mixed>>  $rules
     * @return array<string, array<int, mixed>>
     */
    protected function segmentedRules(array $rules): array
    {
        $segmentedRules = [];

        foreach (range(1, 3) as $segment) {
            foreach ($rules as $field => $fieldRules) {
                $segmentedRules["{$field}_{$segment}"] = $fieldRules;
            }
        }

        return $segmentedRules;
    }

    protected function prepareForValidation(): void
    {
        $emailAddress = $this->input('email_address');

        if (! is_string($emailAddress)) {
            return;
        }

        $this->merge([
            'email_address' => strtolower(trim($emailAddress)),
        ]);
    }
}
