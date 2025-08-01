<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_status_id')->constrained('work_category_statuses');
            $table->string('tracking_token', 64)->unique();
            $table->enum('notification_channel', ['email', 'sms', 'whatsapp', 'viber'])->default('email');
            $table->timestamp('estimated_completion_date')->nullable();
            $table->foreignId('created_by_id')->constrained('users');
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['organization_id', 'customer_id']);
            $table->index(['organization_id', 'work_category_id']);
            $table->index('tracking_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
