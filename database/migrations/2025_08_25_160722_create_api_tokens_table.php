<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('account_id')->constrained()->cascadeOnDelete();
            $t->foreignId('api_service_id')->constrained()->cascadeOnDelete();
            $t->foreignId('token_type_id')->constrained()->cascadeOnDelete();
            $t->text('value')->nullable();       // для bearer/api-key
            $t->string('login')->nullable();     // для login+password
            $t->string('password')->nullable();  // для login+password
            $t->timestamp('expires_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            // один токен определённого типа для сервиса у конкретного аккаунта
            $t->unique(['account_id','api_service_id','token_type_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_tokens');
    }
}
