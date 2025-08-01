<?php

use App\Models\{Organization, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_id')->nullable()->index();
            $table->string('name');
            $table->string('document_number')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('address_number')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->string('card_exp_month')->nullable();
            $table->string('card_exp_year')->nullable();
            $table->string('card_country')->nullable();
            $table->timestamps();

            $table->index(['id', 'slug', 'is_active']);
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->index();
            $table->foreignIdFor(User::class)->index();
            $table->timestamps();

            $table->index(['id', 'organization_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('organization_user');
    }
};
