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

    public function deliveryStatus(): ?string
    {
        $deliveryStatus = $this->validated('delivery_status');

        if (! is_scalar($deliveryStatus) || $deliveryStatus === '') {
            return null;
        }

        return (string) $deliveryStatus;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function commonRules(): array
    {
        return [
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
            'delivery_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'delivery_status' => ['sometimes', 'nullable', 'string', 'max:100'],
            'journey_confirmation_start_date' => ['sometimes', 'nullable', 'date'],
        ];
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
