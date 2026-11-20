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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->string('staff_no')->nullable()->unique()->after('email');
            $table->foreignId('site_id')->nullable()->after('staff_no')->constrained()->restrictOnDelete();
            $table->string('status')->default('invited')->after('site_id');
            $table->string('locale', 5)->default('en')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['organization_id', 'staff_no', 'status', 'locale']);
        });
    }
};
