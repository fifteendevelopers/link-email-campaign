<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_subscribers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consent_id')->nullable();
            $table->string('email_address')->index();
            $table->string('name')->nullable();

            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->string('delivery_status')->default('active');
            $table->string('parent_name')->nullable();
            $table->string('child_name')->nullable();
            $table->string('establishment_name')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_public_email_address')->nullable();
            $table->text('instructors_delivery')->nullable();

            $table->date('journey_confirmation_start_date')->nullable()->index('parent_journey_date_idx'); // Send Consent Confirmation Email
            $table->timestamp('journey_confirmation_sent_at')->nullable();

            $table->date('comms_start_date')->nullable();
            $table->date('cycle_and_helmet_check_comms_date')->nullable()->index('parent_cycle_check_date_idx'); // Send 'Pre Course - how_to_get_ready_for_bikeability' Email
            $table->timestamp('cycle_and_helmet_check_sent_at')->nullable();
            $table->date('pre_course_faq_comms_date')->nullable()->index('parent_faq_date_idx'); // 5 days after Cycle and Helmet Check Email - Send 'Pre Course - FAQ' Email
            $table->timestamp('pre_course_faq_sent_at')->nullable();

            $table->string('course_level')->nullable();
            $table->date('outcomes_journey_start_date')->nullable()->index('parent_outcome_date_idx'); // Send Outcome Email - 'Rider Report' Email
            $table->timestamp('outcomes_journey_sent_at')->nullable();

            $table->string('outcome_survey_course_level')->nullable();
            $table->date('outcome_survey_comms_date')->nullable()->index('parent_survey_date_idx'); // 2 weeks after Completion of Delivery - Send Parent Survey Email
            $table->timestamp('outcome_survey_sent_at')->nullable();
            $table->string('bikeability_club_link')->nullable();
            $table->string('survey_link')->nullable();

            $table->boolean('resubscribe')->default(true);
            $table->boolean('restart_subscription_based_autoresponders')->default(true);
            $table->string('consent_to_track')->default('Yes');
            $table->string('consent_to_send_sms')->default('Yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_subscribers');
    }
};