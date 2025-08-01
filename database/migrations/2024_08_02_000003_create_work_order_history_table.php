<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('work_category_statuses');
            $table->text('notes')->nullable();
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Add index for common queries
            $table->index(['work_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_history');
    }
};
