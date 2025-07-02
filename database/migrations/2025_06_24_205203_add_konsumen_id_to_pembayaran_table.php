<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKonsumenIdToPembayaranTable extends Migration
{
    public function up()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Tambahkan kolom konsumen_id jika belum ada
            if (!Schema::hasColumn('pembayaran', 'konsumen_id')) {
                $table->string('konsumen_id', 191)->nullable()->after('user_id');
            }

            // Tambahkan foreign key hanya jika belum ada
            $table->foreign('konsumen_id')
                ->references('no_identitas')
                ->on('konsumens')
                ->onDelete('set null'); // atau 'cascade' jika wajib
        });
    }

    public function down()
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['konsumen_id']);
            $table->dropColumn('konsumen_id');
        });
    }
}
