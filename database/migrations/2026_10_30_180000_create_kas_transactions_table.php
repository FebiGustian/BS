<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->string('jenis', 30);
            $table->date('tanggal');
            $table->string('deskripsi');
            $table->decimal('nominal', 15, 2);

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('cash_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('jurnal_id')
                ->nullable()
                ->unique()
                ->constrained('jurnals')
                ->restrictOnDelete();

            $table->foreignId('cabang_id')
                ->constrained('cabangs')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('bukti_path')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'tanggal']);
            $table->index(['cabang_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transactions');
    }
};
