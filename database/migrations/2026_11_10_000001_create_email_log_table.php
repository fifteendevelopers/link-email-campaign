<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_log', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->longText('headers')->nullable();
            $table->text('attachments')->nullable();
            $table->unsignedBigInteger('parent_subscriber_id')->nullable();
            $table->unsignedBigInteger('school_subscriber_id')->nullable();
            $table->string('sent_column', 64)->nullable();

            $table->index(['parent_subscriber_id', 'sent_column'], 'email_log_parent_sent_idx');
            $table->index(['school_subscriber_id', 'sent_column'], 'email_log_school_sent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_log');
    }
};
