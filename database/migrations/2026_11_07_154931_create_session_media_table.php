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
        Schema::create('session_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booth_session_id')->constrained('booth_sessions')->cascadeOnDelete();
            $table->string('type')->index(); // raw_photo, composite_video, gif
            $table->integer('pose_number')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_media');
    }
};
