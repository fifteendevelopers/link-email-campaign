<?php

use App\Jobs\SendDueParentCommunications;
use App\Jobs\SendDueSchoolCommunications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendDueParentCommunications)
    ->dailyAt('08:00')
    ->timezone((string) config('services.link_email_campaign.comms_timezone'))
    ->withoutOverlapping();

Schedule::job(new SendDueSchoolCommunications)
    ->dailyAt('08:00')
    ->timezone((string) config('services.link_email_campaign.comms_timezone'))
    ->withoutOverlapping();
