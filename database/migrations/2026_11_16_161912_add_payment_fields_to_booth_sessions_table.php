<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_sessions', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->unique()->after('session_code');
            }
            if (!Schema::hasColumn('booth_sessions', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('payment_method');
            }
            if (!Schema::hasColumn('booth_sessions', 'amount')) {
                $table->integer('amount')->default(0)->after('payment_status');
            }
            if (!Schema::hasColumn('booth_sessions', 'qr_string')) {
                $table->text('qr_string')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('booth_sessions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('qr_string');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_status', 'amount', 'qr_string', 'paid_at']);
        });
    }
};