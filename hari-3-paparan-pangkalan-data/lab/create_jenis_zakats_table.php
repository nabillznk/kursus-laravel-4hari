<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migrasi: Jadual Jenis Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:migration create_jenis_zakats_table
|
| Jadual ini menyimpan senarai jenis-jenis zakat yang diuruskan
| oleh Pusat Zakat Negeri Kedah.
|
| Contoh data:
| - Zakat Fitrah (kadar tetap ~RM7.00)
| - Zakat Pendapatan (2.5%)
| - Zakat Perniagaan (2.5%)
| - Zakat Wang Simpanan (2.5%)
| - Zakat Emas dan Perak (2.5%)
| - Zakat Pertanian (5% atau 10%)
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_zakats', function (Blueprint $table) {
            $table->id();

            $table->string('nama');                      // Nama jenis zakat
            $table->decimal('kadar', 5, 2);              // Kadar/peratusan (cth: 2.50)
            $table->text('penerangan')->nullable();       // Penerangan ringkas
            $table->boolean('is_aktif')->default(true);  // Status aktif

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_zakats');
    }
};
