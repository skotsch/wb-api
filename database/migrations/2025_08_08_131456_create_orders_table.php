<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('g_number', 50)->index();
            $table->dateTime('date');
            $table->date('last_change_date');
            $table->string('supplier_article', 50);
            $table->string('tech_size', 50);
            $table->bigInteger('barcode');
            $table->decimal('total_price', 12, 2);
            $table->unsignedTinyInteger('discount_percent');

            $table->string('warehouse_name', 100);
            $table->string('oblast', 100);
            $table->bigInteger('income_id');
            $table->string('odid', 50); // приходит как строка "0", лучше string
            $table->bigInteger('nm_id');

            $table->string('subject', 100);
            $table->string('category', 100);
            $table->string('brand', 100);

            $table->boolean('is_cancel');
            $table->date('cancel_dt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
