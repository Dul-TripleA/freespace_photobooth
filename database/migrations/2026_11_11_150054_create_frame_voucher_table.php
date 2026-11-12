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
        Schema::create('frame_voucher', function (Blueprint $table) {
            $table->id();
            // Ubah foreignId menjadi foreignUuid khusus untuk frame_id
            $table->foreignUuid('frame_id')->constrained()->cascadeOnDelete();

            // voucher_id tetap menggunakan foreignId karena tabel vouchers menggunakan $table->id() (bigint)
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frame_voucher');
    }
};
