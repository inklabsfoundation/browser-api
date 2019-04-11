<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->string('tx_id')->index()->comment('transaction id');
            $table->string('block_hash')->index()->comment('block hash');
            $table->unsignedInteger('tx_index')->index()->comment('transaction index');
            $table->unsignedInteger('tx_size')->comment('transaction size');
            $table->unsignedInteger('tx_vsize')->comment('transaction vsize');
            $table->integer('tx_version')->index()->comment('transaction version');
            $table->unsignedInteger('lock_time')->index()->comment('transaction lock time');
            $table->string('total_vout_value', 64)->comment('total value hex');
            $table->string('total_fee', 64)->comment('total fee hex');
            $table->string('total_mined', 64)->comment('total mined hex');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tx_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction');
    }
}
