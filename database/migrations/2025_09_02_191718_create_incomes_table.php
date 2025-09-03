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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('income_id')->unique();
            $table->string('number');
            $table->date('date');
            $table->dateTime('last_change_date');
            $table->string('supplier_article');
            $table->string('tech_size')->nullable();
            $table->string('barcode')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('total_price', 10, 2);
            $table->date('date_close')->nullable();
            $table->string('warehouse_name');
            $table->unsignedBigInteger('nm_id');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
