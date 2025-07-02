<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 2025_06_24_000000_create_konsumens_table.php

Schema::create('konsumens', function (Blueprint $table) {
    $table->string('no_identitas')->primary();
    $table->string('nama');
    $table->string('email')->unique();
    $table->string('no_telepon')->nullable();
    $table->integer('saldo')->default(0);
    $table->string('password');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsumens');
    }
};