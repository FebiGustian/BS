<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_logins', function (Blueprint $table) {
            $table->string('device_hash', 64)->nullable()->index();
            $table->string('jenis_perangkat', 20)->nullable();
            $table->boolean('perangkat_baru')->default(false);
        });

        Schema::create('perangkats', function (Blueprint $table) {
            $table->id();
            $table->string('device_hash', 64)->unique();
            $table->string('nama')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkats');

        Schema::table('log_logins', function (Blueprint $table) {
            $table->dropIndex(['device_hash']);
            $table->dropColumn(['device_hash', 'jenis_perangkat', 'perangkat_baru']);
        });
    }
};
