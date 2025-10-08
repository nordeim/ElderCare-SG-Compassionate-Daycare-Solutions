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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('center_id');
            $table->string('title');
            $table->text('content');
            $table->unsignedTinyInteger('rating');
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->text('moderation_notes')->nullable();
            $table->unsignedBigInteger('moderated_by')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('center_id')->references('id')->on('centers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('moderated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

            $table->index('user_id');
            $table->index('center_id');
            $table->index('status');
            $table->index('rating');
            $table->index('created_at');
            $table->index('moderated_by');
            $table->index('deleted_at');
            $table->index(['center_id', 'rating', 'status']);
        });

        DB::statement("ALTER TABLE testimonials ADD CONSTRAINT chk_testimonials_rating CHECK (rating >= 1 AND rating <= 5)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
