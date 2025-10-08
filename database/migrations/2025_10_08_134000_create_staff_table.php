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
        Schema::create('staff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('center_id');
            $table->string('name');
            $table->string('position');
            $table->json('qualifications')->nullable();
            $table->unsignedTinyInteger('years_of_experience')->default(0);
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('center_id')->references('id')->on('centers')->cascadeOnDelete()->cascadeOnUpdate();
            $table->index('center_id');
            $table->index('status');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
