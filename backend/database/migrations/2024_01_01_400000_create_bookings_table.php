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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 20)->unique();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('center_id')->constrained()->onDelete('restrict');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            
            // Booking Details
            $table->date('booking_date');
            $table->time('booking_time');
            $table->enum('booking_type', ['visit', 'consultation', 'trial_day'])->default('visit');
            
            // Calendly Integration
            $table->string('calendly_event_id')->nullable();
            $table->string('calendly_event_uri', 500)->nullable();
            $table->string('calendly_cancel_url', 500)->nullable();
            $table->string('calendly_reschedule_url', 500)->nullable();
            
            // Pre-booking Questionnaire
            $table->json('questionnaire_responses')->nullable();
            
            // Status Management
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            
            // Notification Tracking
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('booking_date');
            $table->index('status');
            $table->index('calendly_event_id');
            $table->index(['user_id', 'booking_date']);
            $table->index(['center_id', 'booking_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
