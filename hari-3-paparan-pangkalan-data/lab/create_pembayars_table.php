<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migrasi: Jadual Pembayar Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:migration create_pembayars_table
|
| Jadual ini menyimpan maklumat semua pembayar zakat yang berdaftar
| dengan Pusat Zakat Negeri Kedah.
|
*/

return new class extends Migration
{
    /**
     * Cipta jadual 'pembayars'.
     */
    public function up(): void
    {
        Schema::create('pembayars', function (Blueprint $table) {
            // Kunci utama (auto-increment)
            $table->id();

            // Maklumat peribadi
            $table->string('nama');                              // Nama penuh
            $table->string('no_ic', 12)->unique();               // No. KP (tanpa sengkang) — unik
            $table->text('alamat');                               // Alamat penuh
            $table->string('no_tel', 15);                        // No. telefon
            $table->string('email')->nullable();                 // E-mel (pilihan)

            // Maklumat pekerjaan
            $table->string('pekerjaan')->nullable();             // Jenis pekerjaan
            $table->decimal('pendapatan_bulanan', 10, 2)         // Pendapatan sebulan (RM)
                  ->nullable()
                  ->default(0);

            // Cap masa
            $table->timestamps();                                // created_at, updated_at

            // Indeks untuk carian pantas
            $table->index('nama');
            $table->index('no_tel');
        });
    }

    /**
     * Padam jadual (untuk rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayars');
    }
};
