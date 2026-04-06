<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk jadual pembayars
 * Menyimpan maklumat pembayar zakat
 */
return new class extends Migration
{
    /**
     * Jalankan migrasi — cipta jadual pembayars.
     */
    public function up(): void
    {
        Schema::create('pembayars', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_ic', 12)->unique();
            $table->text('alamat');
            $table->string('no_tel', 15);
            $table->string('email')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->decimal('pendapatan_bulanan', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Undur migrasi — padam jadual pembayars.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayars');
    }
};
