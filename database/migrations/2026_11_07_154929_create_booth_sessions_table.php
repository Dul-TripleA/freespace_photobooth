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
        Schema::create('booth_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_code')->unique();
            $table->string('gallery_token', 64)->unique();
            $table->foreignUuid('frame_id')->nullable()->constrained('frames')->nullOnDelete();
            $table->enum('payment_method', ['cashless', 'voucher', 'online']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            
            // Tambahkan baris ini:
            $table->string('selected_filter')->default('normal');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booth_sessions');
    }
};
