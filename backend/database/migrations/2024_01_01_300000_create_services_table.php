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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('price_unit', ['hour', 'day', 'week', 'month'])->nullable();
            $table->string('duration', 100)->nullable();
            $table->json('features')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['center_id', 'slug']);
            $table->index('status');
            // Fulltext index is MySQL-specific; only add for mysql driver
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['name', 'description'], 'idx_search');
            }
        });

        // MySQL-only CHECK constraint
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE services ADD CONSTRAINT chk_price CHECK (price IS NULL OR price >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
