<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('block', function (Blueprint $table) {
            $table->string('block_hash')->primary()->comment('block hash');
            $table->unsignedInteger('strippedsize')->comment('strippedsize');
            $table->unsignedInteger('block_size')->comment('block size');
            $table->unsignedInteger('block_weight')->comment('block weight');
            $table->unsignedInteger('block_height')->index()->comment('block height');
            $table->integer('block_version')->index()->comment('block version');
            $table->string('version_hex')->index()->comment('version hex');
            $table->string('merkleroot')->index()->comment('merkleroot');
            $table->string('hash_state_root')->index()->comment('hash state root');
            $table->string('hash_utxo_root')->index()->comment('hash utxo root');
            $table->unsignedInteger('block_time')->index()->comment('block time');
            $table->unsignedInteger('median_time')->index()->comment('median time');
            $table->unsignedInteger('nonce')->index()->comment('nonce');
            $table->string('block_bits')->index()->comment('block bits');
            $table->string('difficulty', 64)->comment('difficulty hex, decimal 8');
            $table->string('chain_work')->index()->comment('chain work');
            $table->string('previous_block_hash')->index()->comment('previous block hash');
            $table->string('next_block_hash')->nullable()->index()->comment('next block hash');
            $table->string('flags')->index()->comment('flags');
            $table->string('proof_hash')->index()->comment('proof hash');
            $table->string('modifier')->index()->comment('modifier');
            $table->text('signature')->default('')->comment('signature');
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
        Schema::dropIfExists('block');
    }
}
