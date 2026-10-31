<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_cabangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->restrictOnDelete();
            $table->foreignId('cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->unsignedInteger('stok')->default(0);
            $table->timestamps();

            $table->unique(['barang_id', 'cabang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_cabangs');
    }
};
