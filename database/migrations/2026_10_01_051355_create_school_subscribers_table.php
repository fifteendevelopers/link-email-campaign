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
            $table->unsignedBigInteger('consent_id')->nullable();
            $table->string('email_address')->index();
            $table->string('name')->nullable();

            $addSegmentedColumns = static function (callable $addColumn): void {
                foreach (range(1, 3) as $segment) {
                    $addColumn($segment);
                }
            };

            $addSegmentedColumns(fn (int $segment) => $table->unsignedBigInteger("segment_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->unsignedBigInteger("last_segment_used_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->unsignedBigInteger("delivery_id_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("delivery_status_{$segment}")->default('active'));
            $addSegmentedColumns(fn (int $segment) => $table->date("delivery_start_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("journey_confirmation_start_date_{$segment}")->nullable()->index("school_journey_date_{$segment}_idx")); // Send booking_confirmation email template
            $addSegmentedColumns(fn (int $segment) => $table->timestamp("journey_confirmation_sent_at_{$segment}")->nullable());

            $addSegmentedColumns(fn (int $segment) => $table->date("course_comms_start_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("pre_course_consent_check_comms_date_{$segment}")->nullable()->index("school_consent_date_{$segment}_idx")); // Course communications start date; send consent_check email template
            $addSegmentedColumns(fn (int $segment) => $table->timestamp("pre_course_consent_check_sent_at_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("outstanding_consent_check_comms_date_{$segment}")->nullable()->index("school_outstanding_date_{$segment}_idx")); // Seven days after course communications start; send consent_check_outstanding email template
            $addSegmentedColumns(fn (int $segment) => $table->timestamp("outstanding_consent_check_sent_at_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("final_consent_reminder_comms_date_{$segment}")->nullable()->index("school_final_date_{$segment}_idx")); // Four days after the outstanding check; send consent_check_final_reminder email template
            $addSegmentedColumns(fn (int $segment) => $table->timestamp("final_consent_reminder_sent_at_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("first_name_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("provider_name_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("provider_contact_email_address_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("booking_link_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("establishment_name_{$segment}")->nullable());

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
