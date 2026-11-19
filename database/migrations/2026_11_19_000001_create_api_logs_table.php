<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path');
            $table->string('route_name')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('query')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->longText('response')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('status_code');
            $table->index(['method', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
