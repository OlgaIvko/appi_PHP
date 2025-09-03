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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nm_id');
            $table->string('warehouse');
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('in_way_to_client')->default(0);
            $table->unsignedInteger('in_way_from_client')->default(0);
            $table->timestamps();

            $table->index(['nm_id', 'warehouse']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
