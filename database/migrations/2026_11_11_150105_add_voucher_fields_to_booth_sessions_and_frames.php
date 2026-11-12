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
        // Tambahkan field penanda agar frame private tidak muncul di Kiosk reguler
        Schema::table('frames', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('category');
        });

        // Catat voucher mana yang dipakai oleh sebuah sesi
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booth_sessions_and_frames', function (Blueprint $table) {
            //
        });
    }
};
