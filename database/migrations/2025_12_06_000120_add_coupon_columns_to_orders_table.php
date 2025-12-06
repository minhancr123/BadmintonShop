<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 10, 2)->default(0)->after('user_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal_amount');
            $table->decimal('shipping_amount', 10, 2)->default(0)->after('discount_amount');
            $table->foreignId('coupon_id')->nullable()->after('shipping_amount')->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['subtotal_amount', 'discount_amount', 'shipping_amount', 'coupon_id', 'coupon_code']);
        });
    }
};
