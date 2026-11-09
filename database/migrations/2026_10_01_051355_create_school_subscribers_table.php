<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email_address')->index();
            $table->string('name')->nullable();

            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->string('delivery_status')->default('active');
            $table->date('delivery_start_date')->nullable();
            $table->date('journey_confirmation_start_date')->nullable()->index('school_journey_date_idx'); // Send booking_confirmation email template
            $table->timestamp('journey_confirmation_sent_at')->nullable();

            $table->date('course_comms_start_date')->nullable();
            $table->date('pre_course_consent_check_comms_date')->nullable()->index('school_consent_date_idx'); // Course communications start date; send consent_check email template
            $table->timestamp('pre_course_consent_check_sent_at')->nullable();
            $table->date('outstanding_consent_check_comms_date')->nullable()->index('school_outstanding_date_idx'); // Seven days after course communications start; send consent_check_outstanding email template
            $table->timestamp('outstanding_consent_check_sent_at')->nullable();
            $table->date('final_consent_reminder_comms_date')->nullable()->index('school_final_date_idx'); // Four days after the outstanding check; send consent_check_final_reminder email template
            $table->timestamp('final_consent_reminder_sent_at')->nullable();
            $table->string('first_name')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_contact_email_address')->nullable();
            $table->string('booking_link')->nullable();
            $table->string('establishment_name')->nullable();

            $table->boolean('resubscribe')->default(true);
            $table->boolean('restart_subscription_based_autoresponders')->default(true);
            $table->string('consent_to_track')->default('Yes');
            $table->string('consent_to_send_sms')->default('Yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_subscribers');
    }
};
