<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToStocksTable extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $t) {
            // 1) FK на аккаунт (nullable, чтобы миграция прошла на уже существующих данных)
            $t->foreignId('account_id')
              ->nullable()
              ->after('id')
              ->constrained('accounts')
              ->cascadeOnDelete();

            // 2) Индексы для инкрементального обновления по датам
            $t->index('date', 'stocks_date_idx');
            $t->index('last_change_date', 'stocks_last_change_date_idx');

            // 3) Составной уникальный ключ строки остатков внутри аккаунта и даты
            // Учитываем конкретный товар/вариант/штрихкод и склад
            $t->unique(
                ['account_id', 'date', 'nm_id', 'barcode', 'warehouse_name', 'tech_size'],
                'stocks_account_d_nm_bc_wh_ts_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $t) {
            $t->dropUnique('stocks_account_d_nm_bc_wh_ts_unique');
            $t->dropIndex('stocks_date_idx');
            $t->dropIndex('stocks_last_change_date_idx');
            $t->dropConstrainedForeignId('account_id');
        });
    }
}
