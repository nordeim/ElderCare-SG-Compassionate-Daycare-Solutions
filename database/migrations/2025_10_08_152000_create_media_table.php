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
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->enum('type', ['image', 'video', 'document']);
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('duration')->nullable();
            $table->string('caption', 500)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('cloudflare_stream_id')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
            $table->index('type');
            $table->index('display_order');
            $table->index('cloudflare_stream_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
