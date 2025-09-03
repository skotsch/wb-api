<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Привязка к аккаунту
            $table->foreignId('account_id')
                  ->constrained('accounts')
                  ->cascadeOnDelete();

            $table->date('date');
            $table->date('last_change_date')->nullable();

            $table->string('supplier_article', 50)->nullable();
            $table->string('tech_size', 50)->nullable();
            $table->bigInteger('barcode');

            $table->integer('quantity');
            $table->integer('quantity_full')->nullable();
            $table->boolean('is_supply')->nullable();
            $table->boolean('is_realization')->nullable();

            $table->string('warehouse_name', 100);
            $table->integer('in_way_to_client')->nullable();
            $table->integer('in_way_from_client')->nullable();

            $table->bigInteger('nm_id');
            $table->string('subject', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('brand', 100)->nullable();

            $table->bigInteger('sc_code')->nullable();
            $table->integer('price')->nullable();
            $table->unsignedTinyInteger('discount')->nullable();

            $table->unique(
                ['account_id','date','nm_id','tech_size','warehouse_name','sc_code','is_supply','is_realization','quantity_full'],
                'stocks_acc_date_nmid_size_wh_sc_flags_qfull_unique'
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
        Schema::dropIfExists('stocks');
    }
}
