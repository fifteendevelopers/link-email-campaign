<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSchoolEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'email'],
            'establishment_name' => ['required', 'string', 'max:255'],
            'provider_name' => ['required', 'string', 'max:255'],
            'provider_contact_email' => ['required', 'email'],
            'booking_link' => ['required', 'url'],
            'first_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Map the validated snake_case payload to the camelCase keys
     * the email templates expect.
     *
     * @return array<string, string>
     */
    public function templateData(): array
    {
        $validated = $this->validated();

        $data = [
            'establishmentName' => $validated['establishment_name'],
            'providerName' => $validated['provider_name'],
            'providerContactEmail' => $validated['provider_contact_email'],
            'bookingLink' => $validated['booking_link'],
        ];

        if (isset($validated['first_name'])) {
            $data['firstName'] = $validated['first_name'];
        }

        return $data;
    }
}
