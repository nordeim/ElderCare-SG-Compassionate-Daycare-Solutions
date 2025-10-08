<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PDPA Compliance: Consent tracking with versioning
     */
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('consent_type', [
                'account',
                'marketing_email',
                'marketing_sms',
                'analytics_cookies',
                'functional_cookies'
            ]);
            $table->boolean('consent_given');
            $table->text('consent_text')->comment('Snapshot of consent text');
            $table->string('consent_version', 10)->comment('Privacy policy version');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'consent_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
