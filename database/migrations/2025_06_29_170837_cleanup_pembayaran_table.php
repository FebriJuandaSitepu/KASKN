<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function untuk check foreign key exists
        $checkForeignKeyExists = function ($table, $constraint) {
            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = '{$table}' 
                AND CONSTRAINT_NAME = '{$constraint}'
            ");
            return count($result) > 0;
        };

        Schema::table('pembayaran', function (Blueprint $table) use ($checkForeignKeyExists) {
            // Drop kolom yang duplikasi jika ada
            if (Schema::hasColumn('pembayaran', 'bukti_pembayaran')) {
                $table->dropColumn('bukti_pembayaran');
            }
            
            // Drop user_id dan foreign key-nya dengan safe check
            if (Schema::hasColumn('pembayaran', 'user_id')) {
                // Check dan drop foreign key jika ada
                if ($checkForeignKeyExists('pembayaran', 'pembayaran_user_id_foreign')) {
                    $table->dropForeign(['user_id']);
                }
                
                // Drop kolom user_id
                $table->dropColumn('user_id');
            }
        });

        // Pastikan struktur tabel pembayaran benar
        Schema::table('pembayaran', function (Blueprint $table) use ($checkForeignKeyExists) {
            // Pastikan konsumen_id ada
            if (!Schema::hasColumn('pembayaran', 'konsumen_id')) {
                $table->string('konsumen_id', 191)->nullable()->after('id');
            }
            
            // Pastikan foreign key ke konsumens ada (jika belum)
            if (!$checkForeignKeyExists('pembayaran', 'pembayaran_konsumen_id_foreign')) {
                $table->foreign('konsumen_id')
                    ->references('no_identitas')
                    ->on('konsumens')
                    ->onDelete('set null');
            }
            
            // Pastikan kolom status ada dengan tipe yang benar
            if (!Schema::hasColumn('pembayaran', 'status')) {
                $table->enum('status', ['pending', 'valid', 'ditolak'])->default('pending');
            }
            
            // Pastikan struktur kolom lainnya benar
            if (!Schema::hasColumn('pembayaran', 'tipe')) {
                $table->enum('tipe', ['pemasukan', 'pengeluaran'])->default('pemasukan');
            }
            
            if (!Schema::hasColumn('pembayaran', 'metode')) {
                $table->string('metode', 100)->nullable();
            }
            
            if (!Schema::hasColumn('pembayaran', 'tanggal')) {
                $table->dateTime('tanggal')->nullable();
            }
            
            if (!Schema::hasColumn('pembayaran', 'bukti')) {
                $table->string('bukti')->nullable();
            }
            
            if (!Schema::hasColumn('pembayaran', 'jumlah')) {
                $table->integer('jumlah')->default(0);
            }
        });
        
        // Clean up topups table juga
        Schema::table('topups', function (Blueprint $table) use ($checkForeignKeyExists) {
            // Pastikan konsumen_id ada
            if (!Schema::hasColumn('topups', 'konsumen_id')) {
                $table->string('konsumen_id', 191)->after('id');
            }
            
            // Drop user_id jika ada (karena topup untuk konsumen, bukan user)
            if (Schema::hasColumn('topups', 'user_id')) {
                // Check dan drop foreign key jika ada
                if ($checkForeignKeyExists('topups', 'topups_user_id_foreign')) {
                    $table->dropForeign(['user_id']);
                }
                $table->dropColumn('user_id');
            }
            
            // Pastikan foreign key ke konsumens benar
            if (!$checkForeignKeyExists('topups', 'topups_konsumen_id_foreign')) {
                $table->foreign('konsumen_id')
                    ->references('no_identitas')
                    ->on('konsumens')
                    ->onDelete('cascade');
            }
            
            // Pastikan kolom lainnya ada
            if (!Schema::hasColumn('topups', 'nominal')) {
                $table->integer('nominal')->default(0);
            }
            
            if (!Schema::hasColumn('topups', 'status')) {
                $table->enum('status', ['pending', 'diterima', 'ditolak'])->default('pending');
            }
            
            if (!Schema::hasColumn('topups', 'bukti_transfer')) {
                $table->string('bukti_transfer')->nullable();
            }
        });

        // Update data yang mungkin inconsistent
        DB::statement("UPDATE pembayaran SET konsumen_id = NULL WHERE konsumen_id = ''");
        DB::statement("UPDATE topups SET konsumen_id = NULL WHERE konsumen_id = ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Restore user_id jika diperlukan untuk rollback
            if (!Schema::hasColumn('pembayaran', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
        
        Schema::table('topups', function (Blueprint $table) {
            // Restore user_id jika diperlukan untuk rollback
            if (!Schema::hasColumn('topups', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }
};