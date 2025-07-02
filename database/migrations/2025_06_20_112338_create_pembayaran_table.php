<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 2025_06_24_000002_create_pembayarans_table.php

Schema::create('pembayaran', function (Blueprint $table) {
    $table->id();
    $table->string('user_id'); // berisi no_identitas konsumen
    $table->string('tipe'); // pemasukan/pengeluaran
    $table->integer('jumlah');
    $table->string('metode')->nullable();
    $table->dateTime('tanggal')->nullable();
    $table->string('status')->default('pending');
    $table->string('bukti')->nullable();
    $table->unsignedBigInteger('pemesanan_id')->nullable();
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('pembayaran');
    }
};
