<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->enum('consent_type', ['account', 'marketing_email', 'marketing_sms', 'analytics_cookies', 'functional_cookies']);
            $table->boolean('consent_given');
            $table->text('consent_text');
            $table->string('consent_version', 10);
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('user_id');
            $table->index(['user_id', 'consent_type']);
            $table->index('consent_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
