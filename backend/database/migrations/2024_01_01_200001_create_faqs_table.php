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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['general', 'booking', 'services', 'pricing', 'accessibility']);
            $table->string('question', 500);
            $table->text('answer');
            $table->unsignedInteger('display_order')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['category', 'display_order']);
            $table->index('status');
            // Fulltext index is MySQL-specific; only add for mysql driver
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['question', 'answer'], 'idx_search');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
