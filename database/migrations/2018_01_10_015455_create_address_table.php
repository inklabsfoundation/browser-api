<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->string('address_qtum')->primary()->comment('address qtum');
            $table->string('address_hex')->nullable()->index()->comment('address hex');
            $table->string('qtum_balance', 64)->comment('qtum balance hex, decimal 8');
            $table->unsignedInteger('updated_at_block_height')->index()->comment('updated at block height');
            $table->string('updated_at_block_hash')->index()->comment('updated at block hash');
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
        Schema::dropIfExists('address');
    }
}
