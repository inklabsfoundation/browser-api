<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_transaction', function (Blueprint $table) {
            $table->string('address_qtum')->index()->comment('address qtum');
            $table->string('address_hex')->nullable()->index()->comment('address hex');
            $table->string('tx_id')->index()->comment('transaction id');
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
        Schema::dropIfExists('address_transaction');
    }
}
