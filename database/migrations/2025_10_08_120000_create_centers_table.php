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
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('centers', function (Blueprint $table) use ($driver) {
            $table->bigIncrements('id');

            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description');

            // Contact Information
            $table->string('address', 500);
            $table->string('city', 100);
            $table->string('postal_code', 10);
            $table->string('phone', 20);
            $table->string('email');
            $table->string('website')->nullable();

            // MOH Regulatory Compliance
            $table->string('moh_license_number', 50)->unique();
            $table->date('license_expiry_date');
            $table->enum('accreditation_status', ['pending', 'accredited', 'not_accredited', 'expired'])->default('pending');

            // Operational Details
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('current_occupancy')->default(0);
            $table->unsignedInteger('staff_count')->default(0);
            $table->decimal('staff_patient_ratio', 3, 1)->nullable();

            // Flexible JSON Fields
            $table->json('operating_hours')->nullable();
            $table->json('medical_facilities')->nullable();
            $table->json('amenities')->nullable();
            $table->json('transport_info')->nullable();
            $table->json('languages_supported')->nullable();
            $table->json('government_subsidies')->nullable();

            // Geolocation
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Status & Publishing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // SEO Fields
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('moh_license_number');
            $table->index('status');
            $table->index('city');
            $table->index('deleted_at');
            $table->index('created_at');

            if ($driver === 'mysql') {
                $table->fullText(['name', 'short_description', 'description']);
            }
        });

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE centers ADD CONSTRAINT chk_centers_capacity CHECK (capacity > 0)");
            DB::statement("ALTER TABLE centers ADD CONSTRAINT chk_centers_occupancy CHECK (current_occupancy >= 0 AND current_occupancy <= capacity)");
            DB::statement("ALTER TABLE centers ADD CONSTRAINT chk_centers_staff_count CHECK (staff_count >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
