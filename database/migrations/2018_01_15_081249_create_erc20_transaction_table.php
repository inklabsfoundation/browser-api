<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErc20TransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erc20_transaction', function (Blueprint $table) {
            $table->string('tx_id')->index()->comment('transaction id');
            $table->string('block_hash')->index()->comment('block hash');
            $table->unsignedInteger('erc20_id')->index()->comment('erc20 token id');
            $table->string('erc20_symbol')->index()->comment('erc20 symbol');
            $table->string('sender_address_hex')->index()->comment('sender address hex');
            $table->string('sender_address_qtum')->index()->comment('sender address qtum');
            $table->string('receiver_address_hex')->index()->comment('receiver address hex');
            $table->string('receiver_address_qtum')->index()->comment('receiver address qtum');
            $table->string('erc20_value')->comment('transfer value hex');
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
        Schema::dropIfExists('erc20_transaction');
    }
}
