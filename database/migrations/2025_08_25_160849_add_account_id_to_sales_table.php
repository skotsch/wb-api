<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $t) {
            $t->foreignId('account_id')
              ->nullable()
              ->after('id')
              ->constrained('accounts')
              ->cascadeOnDelete();

            $t->dropUnique('sales_g_number_unique');
            $t->unique(['account_id', 'g_number'], 'sales_account_gnumber_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $t) {
            $t->dropUnique('sales_account_gnumber_unique');
            $t->unique('g_number', 'sales_g_number_unique');
            $t->dropConstrainedForeignId('account_id');
        });
    }
}
