<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_trackings', function (Blueprint $table) {
            $table->id();
            $table->uuid('tracking_id')->unique();
            $table->unsignedBigInteger('parent_subscriber_id')->nullable();
            $table->unsignedBigInteger('school_subscriber_id')->nullable();
            $table->string('recipient_email')->nullable()->index();
            $table->unsignedBigInteger('message_id')->nullable()->index();
            $table->string('subject')->nullable();

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            //OPENING
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);

            //CLICKING
            $table->timestamp('first_clicked_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->text('last_clicked_url')->nullable();

            $table->timestamp('temporary_failed_at')->nullable();
            $table->timestamp('permanent_failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('complained_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->string('last_event')->nullable();
            $table->timestamp('last_event_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_trackings');
    }
};
