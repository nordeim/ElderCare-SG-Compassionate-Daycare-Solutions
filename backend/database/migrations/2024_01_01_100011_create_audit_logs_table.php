<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PDPA Compliance: Audit trail (7-year retention required)
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('auditable_type')->comment('Polymorphic type');
            $table->unsignedBigInteger('auditable_id')->comment('Polymorphic ID');
            $table->enum('action', ['created', 'updated', 'deleted', 'restored']);
            $table->json('old_values')->nullable()->comment('Previous state');
            $table->json('new_values')->nullable()->comment('New state');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Note: `updated_at` intentionally omitted here to preserve original migration history.
            
            // Indexes
            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
