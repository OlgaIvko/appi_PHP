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
        Schema::table('tokens', function (Blueprint $table) {
            // Удаляем старый столбец token_type
            $table->dropColumn('token_type');

            // Добавляем новый столбец token_type_id
            $table->foreignId('token_type_id')->constrained()->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('tokens', function (Blueprint $table) {
            // Откат изменений
            $table->string('token_type')->default('bearer');
            $table->dropConstrainedForeignId('token_type_id');
        });
    }
};
