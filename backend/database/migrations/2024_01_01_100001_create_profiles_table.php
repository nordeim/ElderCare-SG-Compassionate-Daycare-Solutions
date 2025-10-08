<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 100)->default('Singapore');
            $table->timestamps();
            
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
