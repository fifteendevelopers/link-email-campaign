<?php

declare(strict_types=1);

namespace App\Http\Requests;

class SendSchoolBookingEmailRequest extends SendSchoolEmailRequest
{
    /**
     * The booking confirmation emails address the recipient directly
     * ("Dear {firstName}"), so the name is mandatory here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'first_name' => ['required', 'string', 'max:255'],
        ]);
    }
}
