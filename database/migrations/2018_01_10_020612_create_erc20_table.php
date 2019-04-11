<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErc20Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erc20', function (Blueprint $table) {
            $table->increments('erc20_id')->comment('erc20 token id');
            $table->string('erc20_address_qtum')->unique()->comment('address qtum');
            $table->string('erc20_address_hex')->unique()->comment('address hex');
            $table->string('creator_address_qtum')->index()->comment('creator address qtum');
            $table->string('creator_address_hex')->index()->comment('creator address hex');
            $table->string('tx_id')->index()->comment('transaction id');
            $table->string('block_hash')->index()->comment('block hash');
            $table->string('erc20_name')->index()->comment('erc20 token name');
            $table->string('erc20_symbol')->index()->comment('erc20 symbol');
            $table->string('erc20_total_supply', 64)->comment('erc20 total supply hex');
            $table->unsignedInteger('erc20_decimal')->comment('erc20 decimal');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erc20');
    }
}
