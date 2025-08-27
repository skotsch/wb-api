<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $t) {
            // 1) account_id как FK (nullable, чтобы миграция прошла на существующих данных)
            $t->foreignId('account_id')
              ->nullable()
              ->after('id')
              ->constrained('accounts')
              ->cascadeOnDelete();

            // 2) Составной уникальный индекс на устойчивую «линию заказа» в рамках аккаунта:
            // (account_id, g_number, nm_id, barcode, tech_size)
            $t->unique(
                ['account_id', 'g_number', 'nm_id', 'barcode', 'tech_size'],
                'orders_account_gnum_nmid_bc_ts_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropIndex('orders_date_idx');
            $t->dropIndex('orders_last_change_date_idx');
            $t->dropUnique('orders_account_gnum_nmid_bc_ts_unique');
            $t->dropConstrainedForeignId('account_id');
        });
    }
}
