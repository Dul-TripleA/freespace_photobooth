<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Mengecek dan menambahkan kolom jika belum ada
            if (!Schema::hasColumn('vouchers', 'code')) {
                $table->string('code')->unique()->after('id');
            }
            if (!Schema::hasColumn('vouchers', 'event_name')) {
                $table->string('event_name')->nullable()->after('code');
            }
            if (!Schema::hasColumn('vouchers', 'quota')) {
                $table->integer('quota')->nullable()->after('event_name');
            }
            if (!Schema::hasColumn('vouchers', 'used_count')) {
                $table->integer('used_count')->default(0)->after('quota');
            }
            if (!Schema::hasColumn('vouchers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('used_count');
            }
            if (!Schema::hasColumn('vouchers', 'expires_at')) {
                $table->dateTime('expires_at')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['code', 'event_name', 'quota', 'used_count', 'is_active', 'expires_at']);
        });
    }
};