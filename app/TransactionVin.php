<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionVin extends Model
{
    use SoftDeletes;
    protected $table = 'transaction_vin';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addTransactionVin(string $tx_id, string $prev_tx_id = null, int $prev_vout = null, string $prev_vout_address_qtum = null, string $prev_vout_address_hex = null, string $prev_vout_value = null, string $script_sig = '', string $coinbase = null, string $txinwitness = '', int $sequence) :bool {
        $this->tx_id = $tx_id;
        $this->prev_tx_id = $prev_tx_id;
        $this->prev_vout = $prev_vout;
        $this->prev_vout_address_qtum = $prev_vout_address_qtum;
        $this->prev_vout_address_hex = $prev_vout_address_hex;
        $this->prev_vout_value = $prev_vout_value;
        $this->script_sig = $script_sig;
        $this->coinbase = $coinbase;
        $this->txinwitness = $txinwitness;
        $this->sequence = $sequence;

        if(!$this->isDirty() || $this->save()){
            return true;
        }else{
            return false;
        }
    }

    public function getTransactionVinArrByTXID(string $tx_id){
        return $this->where('tx_id', '=', $tx_id)->get();
    }
}
