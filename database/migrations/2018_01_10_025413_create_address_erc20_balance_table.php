<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressErc20BalanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_erc20_balance', function (Blueprint $table) {
            $table->string('address_qtum')->index()->comment('address qtum');
            $table->string('address_hex')->index()->comment('address hex');
            $table->unsignedInteger('erc20_id')->index()->comment('erc20 token id');
            $table->string('erc20_symbol')->index()->comment('erc20 symbol');
            $table->string('erc20_balance', 64)->comment('erc20 balance hex');
            $table->unsignedInteger('updated_at_block_height')->index()->comment('updated at block height');
            $table->string('updated_at_block_hash')->index()->comment('updated at block hash');

            $table->unique(['address_qtum', 'erc20_id']);
            $table->unique(['address_hex', 'erc20_id']);

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
        Schema::dropIfExists('address_erc20_balance');
    }
}
