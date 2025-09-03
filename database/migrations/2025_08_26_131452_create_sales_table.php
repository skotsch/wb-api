<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Привязка к аккаунту
            $table->foreignId('account_id')
                  ->constrained('accounts')
                  ->cascadeOnDelete();

            // Основные данные
            $table->string('g_number', 50);               
            $table->date('date');
            $table->date('last_change_date');
            $table->string('supplier_article', 50);
            $table->string('tech_size', 50);
            $table->bigInteger('barcode');
            $table->decimal('total_price', 12, 2);
            $table->unsignedTinyInteger('discount_percent'); 
            $table->boolean('is_supply');
            $table->boolean('is_realization');
            $table->unsignedInteger('promo_code_discount')->nullable();

            // Локация
            $table->string('warehouse_name', 100);
            $table->string('country_name', 50);
            $table->string('oblast_okrug_name', 100);
            $table->string('region_name', 100);
            
            // Идентификаторы
            $table->unsignedBigInteger('income_id');
            $table->string('sale_id', 50);
            $table->string('odid', 50)->nullable();
            $table->unsignedTinyInteger('spp');         

            // Финансы
            $table->decimal('for_pay', 12, 2);
            $table->integer('finished_price');
            $table->integer('price_with_disc');
                        
            // Товар
            $table->BigInteger('nm_id');
            $table->string('subject', 50);
            $table->string('category', 50);
            $table->string('brand', 50);
            $table->boolean('is_storno')->nullable();

            $table->unique(['account_id', 'sale_id'], 'sales_account_saleid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
