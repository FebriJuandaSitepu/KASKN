// database/migrations/xxxx_xx_xx_xxxxxx_create_topups_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topups', function (Blueprint $table) {
    $table->id(); // auto-increment primary key
    $table->string('konsumen_id');
    $table->integer('nominal');
    $table->string('status');
    $table->string('bukti_transfer')->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};