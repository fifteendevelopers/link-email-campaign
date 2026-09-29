<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendParentEmailRequest extends FormRequest
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
            'child_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'establishment_name' => ['required', 'string', 'max:255'],
            'provider_name' => ['required', 'string', 'max:255'],
            'provider_public_email_address' => ['required', 'email'],
            'outcomes_url' => ['sometimes', 'url'],
            'survey_link_url' => ['sometimes', 'url'],
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
            'childName' => $validated['child_name'],
            'parentName' => $validated['parent_name'],
            'establishmentName' => $validated['establishment_name'],
            'providerName' => $validated['provider_name'],
            'providerPublicEmailAddress' => $validated['provider_public_email_address'],
        ];

        if (isset($validated['outcomes_url'])) {
            $data['outcomesURL'] = $validated['outcomes_url'];
        }

        if (isset($validated['survey_link_url'])) {
            $data['surveyLinkUrl'] = $validated['survey_link_url'];
        }

        return $data;
    }
}
