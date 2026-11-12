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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode voucher, misal: ANDIAYU2026
            $table->string('event_name')->nullable(); // Misal: Wedding Andi & Ayu
            $table->integer('quota')->nullable(); // null = unlimited (tanpa batas pakai)
            $table->integer('used_count')->default(0); // Jumlah yang sudah terpakai
            $table->boolean('is_active')->default(true);
            $table->dateTime('expires_at')->nullable(); // Batas waktu voucher (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
