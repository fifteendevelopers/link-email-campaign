<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\SubscriberCommunications;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSendDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('value') === '') {
            $this->merge(['value' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $audience = (string) $this->route('audience');

        return [
            'column' => ['required', 'string', Rule::in(SubscriberCommunications::dateColumns($audience))],
            'value' => ['nullable', 'date_format:d/m/Y'],
        ];
    }

    protected function passedValidation(): void
    {
        $value = $this->input('value');

        if (! is_string($value) || $value === '') {
            return;
        }

        $this->merge([
            'value' => Carbon::createFromFormat('!d/m/Y', $value)->format('Y-m-d'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'column.in' => 'That send date cannot be edited.',
            'value.date_format' => 'Enter the send date as dd/mm/yyyy.',
        ];
    }
}
