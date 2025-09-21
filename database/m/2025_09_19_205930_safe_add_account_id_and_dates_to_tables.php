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
        $tables = ['products', 'orders', 'sales', 'stocks', 'incomes'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Добавляем account_id если его нет
                if (!Schema::hasColumn($table, 'account_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->foreignId('account_id')->nullable()->constrained()->onDelete('cascade');
                    });
                }

                // Для orders и sales добавляем поле date если его нет
                if (($table === 'orders' || $table === 'sales') && !Schema::hasColumn($table, 'date')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->date('date')->nullable()->after('account_id');
                    });
                }

                // Добавляем/проверяем поля временных меток если их нет
                if (!Schema::hasColumn($table, 'created_at')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->timestamps();
                    });
                }
            }
        }
    }

    public function down()
    {
        // В обратной миграции удаляем добавленные поля
        $tables = ['products', 'orders', 'sales', 'stocks', 'incomes'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (Schema::hasColumn($table, 'account_id')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->dropForeign(['account_id']);
                        $table->dropColumn('account_id');
                    });
                }

                if (Schema::hasColumn($table, 'date')) {
                    Schema::table($table, function (Blueprint $table) {
                        $table->dropColumn('date');
                    });
                }

                // Не удаляем timestamps, так как они могут быть нужны для других целей
            }
        }
    }
};
