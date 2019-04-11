<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionVinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_vin', function (Blueprint $table) {
            $table->string('tx_id')->index()->comment('transaction id');
            $table->string('prev_tx_id')->nullable()->index()->comment('previous transaction id');
            $table->unsignedInteger('prev_vout')->nullable()->index()->comment('previous transaction vout n');
            $table->string('prev_vout_address_qtum')->nullable()->index()->comment('previous transaction vout n address qtum');
            $table->string('prev_vout_address_hex')->nullable()->index()->comment('previous transaction vout n address hex');
            $table->string('prev_vout_value', 64)->nullable()->comment('previous transaction vout value hex');
            $table->longText('script_sig')->default('')->comment('script sig object json');
            $table->string('coinbase')->nullable()->index()->comment('mining coinbase');
            $table->longText('txinwitness')->default('')->comment('txinwitness array json');
            $table->unsignedInteger('sequence')->index()->comment('sequence');
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
        Schema::dropIfExists('transaction_vin');
    }
}
