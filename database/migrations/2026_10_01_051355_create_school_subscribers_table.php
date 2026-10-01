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
            $addSegmentedColumns(fn (int $segment) => $table->date("course_comms_start_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("pre_course_consent_check_comms_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("outstanding_consent_check_comms_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("final_consent_reminder_comms_date_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("first_name_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("provider_name_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("provider_contact_email_address_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("booking_link_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->string("establishment_name_{$segment}")->nullable());
            $addSegmentedColumns(fn (int $segment) => $table->date("journey_confirmation_start_date_{$segment}")->nullable());

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
