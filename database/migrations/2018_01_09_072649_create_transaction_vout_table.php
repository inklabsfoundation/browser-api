<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionVoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_vout', function (Blueprint $table) {
            $table->string('tx_id')->index()->comment('transaction id');
            $table->unsignedInteger('n')->index()->comment('vout n');
            $table->string('vout_value', 64)->comment('value hex');
            $table->text('script_pub_key_asm')->default('')->comment('script_pub_key asm');
            $table->string('script_pub_key_hex')->default('')->index()->comment('script_pub_key hex');
            $table->string('script_pub_key_type')->index()->comment('script_pub_key type');
            $table->unsignedInteger('script_pub_key_reqsigs')->nullable()->index()->comment('script_pub_key reqsigs');
            $table->string('script_pub_key_addresses')->nullable()->index()->comment('script_pub_key addresses');
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
        Schema::dropIfExists('transaction_vout');
    }
}
