<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('odid')->unique();
            $table->dateTime('date');
            $table->dateTime('last_change_date');
            $table->string('warehouse_name');
            $table->string('country_name');
            $table->string('oblast_okrug_name');
            $table->string('region_name');
            $table->string('supplier_article');
            $table->unsignedBigInteger('nm_id');
            $table->string('barcode');
            $table->string('category');
            $table->string('subject');
            $table->string('brand');
            $table->string('tech_size');
            $table->unsignedBigInteger('income_id');
            $table->boolean('is_supply')->default(false);
            $table->boolean('is_realization')->default(false);
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_percent', 5, 2);
            $table->decimal('spp', 10, 2);
            $table->decimal('finished_price', 10, 2);
            $table->decimal('price_with_disc', 10, 2);
            $table->boolean('is_cancel')->default(false);
            $table->dateTime('cancel_date')->nullable();
            $table->string('order_type');
            $table->string('sticker');
            $table->string('g_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
