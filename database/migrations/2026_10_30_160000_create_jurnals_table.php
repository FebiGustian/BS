<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_jurnal')->nullable()->unique();
            $table->date('tanggal');
            $table->string('deskripsi');
            $table->foreignId('cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('posted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
