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
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('faqs', function (Blueprint $table) use ($driver) {
            $table->bigIncrements('id');
            $table->enum('category', ['general', 'booking', 'services', 'pricing', 'accessibility']);
            $table->string('question', 500);
            $table->text('answer');
            $table->unsignedInteger('display_order')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['category', 'display_order'], 'faqs_category_display_order_index');
            $table->index('status');

            if ($driver === 'mysql') {
                $table->fullText(['question', 'answer']);
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
