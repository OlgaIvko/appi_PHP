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
        // Добавим индексы для быстрого поиска свежих данных
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['account_id', 'date']);
            $table->index(['date']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index(['account_id', 'date']);
            $table->index(['date']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->index(['account_id', 'date']);
            $table->index(['date']);
        });

        // Для других таблиц с временными метками
        Schema::table('products', function (Blueprint $table) {
            $table->index(['account_id', 'last_updated']);
            $table->index(['last_updated']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['account_id', 'last_updated']);
            $table->index(['last_updated']);
        });
    }



    public function down()
    {
        // Временно отключаем проверку внешних ключей
        Schema::disableForeignKeyConstraints();

        // Удаляем индексы
        Schema::table('orders', function (Blueprint $table) {
            // Сначала удаляем foreign key constraint, если он существует
            if (Schema::hasColumn('orders', 'account_id')) {
                $table->dropForeign(['account_id']);
            }
            $table->dropIndex('orders_account_id_date_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'account_id')) {
                $table->dropForeign(['account_id']);
            }
            $table->dropIndex('sales_account_id_date_index');
        });

        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'account_id')) {
                $table->dropForeign(['account_id']);
            }
            $table->dropIndex('sales_account_id_date_index');
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'account_id')) {
                $table->dropForeign(['account_id']);
            }
            $table->dropIndex('sales_account_id_date_index');
        });
        Schema::table('stocks', function (Blueprint $table) {
            if (Schema::hasColumn('stocks', 'account_id')) {
                $table->dropForeign(['account_id']);
            }
            $table->dropIndex('sales_account_id_date_index');
        });
        // Включаем проверку внешних ключей обратно
        Schema::enableForeignKeyConstraints();
    }
};
