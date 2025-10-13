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
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->enum('locale', ['en', 'zh', 'ms', 'ta']);
            $table->string('field', 100);
            $table->text('value');
            $table->enum('translation_status', ['draft', 'translated', 'reviewed', 'published'])->default('draft');
            $table->foreignId('translated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'idx_translation_unique');
            $table->index('locale');
            $table->index('translation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
