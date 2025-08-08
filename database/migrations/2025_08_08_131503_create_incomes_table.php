<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('income_id')->index(); // может повторяться
            $table->string('number', 50)->nullable(); // часто пустой
            $table->date('date');
            $table->date('last_change_date');

            $table->string('supplier_article', 50)->nullable();
            $table->string('tech_size', 50)->nullable();
            $table->bigInteger('barcode');
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->date('date_close');
            $table->string('warehouse_name', 100);
            $table->bigInteger('nm_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incomes');
    }
}
