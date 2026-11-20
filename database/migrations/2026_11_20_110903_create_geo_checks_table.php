<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per Geo-check that reached a measurement. A passing row also holds the
     * hashed Clock token; its position is kept only until the token is spent.
     */
    public function up(): void
    {
        Schema::create('geo_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->boolean('passed');
            $table->string('failure')->nullable();
            $table->decimal('distance_m', 10, 1)->nullable();
            $table->decimal('accuracy_m', 8, 1);
            $table->unsignedSmallInteger('radius_m');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->char('token_hash', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_checks');
    }
};
