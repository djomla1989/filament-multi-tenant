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
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'address_number')) {
                $table->string('address_number')->nullable()->after('address');
            }
            if (!Schema::hasColumn('organizations', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('organizations', 'zip_code')) {
                $table->string('zip_code')->nullable()->after('city');
            }
            if (!Schema::hasColumn('organizations', 'country')) {
                $table->string('country')->nullable()->after('zip_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'address_number')) {
                $table->dropColumn('address_number');
            }
            if (Schema::hasColumn('organizations', 'zip_code')) {
                $table->dropColumn('zip_code');
            }
            if (Schema::hasColumn('organizations', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
