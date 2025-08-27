<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToIncomesTable extends Migration
{
    public function up()
    {
        Schema::table('incomes', function (Blueprint $t) {
            // FK на аккаунт (nullable для мягкого прогона миграции на существующих данных)
            $t->foreignId('account_id')
              ->nullable()
              ->after('id')
              ->constrained('accounts')
              ->cascadeOnDelete();

            // Индексы под инкрементальные выборки/обновления
            $t->index('date', 'incomes_date_idx');
            $t->index('last_change_date', 'incomes_last_change_date_idx');
            $t->index('date_close', 'incomes_date_close_idx');

            // Уникальность строки прихода в разрезе аккаунта и документа
            // (account_id, income_id, nm_id, barcode, tech_size)
            $t->unique(
                ['account_id', 'income_id', 'nm_id', 'barcode', 'tech_size'],
                'incomes_acc_income_nm_bc_ts_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('incomes', function (Blueprint $t) {
            $t->dropUnique('incomes_acc_income_nm_bc_ts_unique');
            $t->dropIndex('incomes_date_idx');
            $t->dropIndex('incomes_last_change_date_idx');
            $t->dropIndex('incomes_date_close_idx');
            $t->dropConstrainedForeignId('account_id');
        });
    }
}
