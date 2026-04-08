<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayar_id')->constrained('pembayars')->cascadeOnDelete();
            $table->foreignId('jenis_zakat_id')->constrained('jenis_zakats')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->date('tarikh_bayar');
            $table->enum('cara_bayar', ['tunai', 'kad', 'fpx', 'online']);
            $table->string('no_resit')->unique();
            $table->enum('status', ['pending', 'sah', 'batal'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
