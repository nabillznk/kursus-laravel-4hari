<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migrasi: Jadual Pembayaran Zakat
|--------------------------------------------------------------------------
| Dicipta dengan: php artisan make:migration create_pembayarans_table
|
| Jadual ini menyimpan rekod setiap pembayaran zakat.
| Setiap pembayaran merujuk kepada seorang pembayar dan satu jenis zakat.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();

            // Kunci asing (Foreign Keys)
            $table->foreignId('pembayar_id')
                  ->constrained('pembayars')
                  ->onDelete('cascade');          // Padam pembayaran jika pembayar dipadam

            $table->foreignId('jenis_zakat_id')
                  ->constrained('jenis_zakats')
                  ->onDelete('restrict');          // Jangan padam jenis zakat jika ada pembayaran

            // Maklumat pembayaran
            $table->decimal('jumlah', 12, 2);                   // Jumlah bayaran (RM)
            $table->date('tarikh_bayar');                         // Tarikh pembayaran
            $table->enum('cara_bayar', [                         // Cara pembayaran
                'tunai',
                'kad',
                'fpx',
                'online'
            ])->default('tunai');
            $table->string('no_resit')->unique();                // No. resit (unik)
            $table->enum('status', [                              // Status pembayaran
                'pending',
                'sah',
                'batal'
            ])->default('pending');
            $table->text('catatan')->nullable();                  // Catatan tambahan

            $table->timestamps();

            // Indeks
            $table->index('tarikh_bayar');
            $table->index('status');
            $table->index('no_resit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
