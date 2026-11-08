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
        Schema::create('frames', function (Blueprint $table) {
            // Menggunakan default gen_random_uuid() agar aman di PostgreSQL
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('category')->index(); // pesta, corporate, formal
            $table->string('overlay_path'); // Path file PNG
            $table->integer('total_poses')->default(3);
            
            // Tambahkan kolom ini karena dipanggil di FrameSeeder
            $table->integer('canvas_width')->default(1200);
            $table->integer('canvas_height')->default(1800);
            
            $table->jsonb('slots_config'); // Koordinat grid foto
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frames');
    }
};
