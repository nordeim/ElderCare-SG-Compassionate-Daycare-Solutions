<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('booking_number', 20)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('center_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->enum('booking_type', ['visit', 'consultation', 'trial_day'])->default('visit');
            $table->string('calendly_event_id')->nullable();
            $table->string('calendly_event_uri', 500)->nullable();
            $table->string('calendly_cancel_url', 500)->nullable();
            $table->string('calendly_reschedule_url', 500)->nullable();
            $table->json('questionnaire_responses')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('center_id')->references('id')->on('centers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete()->cascadeOnUpdate();

            $table->index('booking_number');
            $table->index('user_id');
            $table->index('center_id');
            $table->index('service_id');
            $table->index('booking_date');
            $table->index('status');
            $table->index('calendly_event_id');
            $table->index('created_at');
            $table->index(['user_id', 'booking_date']);
            $table->index(['center_id', 'booking_date', 'status']);
        });

        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_sms_sent CHECK (sms_sent IN (0, 1))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
