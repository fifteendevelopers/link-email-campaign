<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\SubscriberCommunications;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryStatusRequest extends FormRequest
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
        $audience = (string) $this->route('audience');

        return [
            'status' => ['required', 'string', Rule::in(array_keys(SubscriberCommunications::statuses($audience)))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'That delivery status cannot be selected.',
        ];
    }
}
