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
            $table->bigIncrements('id');
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->enum('locale', ['en', 'zh', 'ms', 'ta']);
            $table->string('field', 100);
            $table->text('value');
            $table->enum('translation_status', ['draft', 'translated', 'reviewed', 'published'])->default('draft');
            $table->unsignedBigInteger('translated_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('translated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'content_translations_unique');
            $table->index('locale');
            $table->index('translation_status');
            $table->index('translated_by');
            $table->index('reviewed_by');
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
