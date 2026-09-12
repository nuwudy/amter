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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('order_id')->nullable()->index()->after('plan_id');
            $table->string('signature')->nullable()->after('payment_id');
            $table->string('currency', 10)->default('INR')->after('amount');
            $table->string('method', 50)->nullable()->after('currency');
            $table->string('phone', 20)->nullable()->after('method');
            $table->string('receipt')->nullable()->after('phone');
            $table->text('error_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropColumn([
                'order_id',
                'signature',
                'currency',
                'method',
                'phone',
                'receipt',
                'error_reason',
            ]);
        });
    }
};
