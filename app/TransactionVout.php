<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionVout extends Model
{
    use SoftDeletes;
    protected $table = 'transaction_vout';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addTransactionVout(string $tx_id, int $n, string $vout_value, string $script_pub_key_asm, string $script_pub_key_hex, string $script_pub_key_type, int $script_pub_key_reqsigs = null, string $script_pub_key_addresses = null) :bool {
        $this->tx_id = $tx_id;
        $this->n = $n;
        $this->vout_value = $vout_value;
        $this->script_pub_key_asm = $script_pub_key_asm;
        $this->script_pub_key_hex = $script_pub_key_hex;
        $this->script_pub_key_type = $script_pub_key_type;
        $this->script_pub_key_reqsigs = $script_pub_key_reqsigs;
        $this->script_pub_key_addresses = $script_pub_key_addresses;

        if(!$this->isDirty() || $this->save()){
            return true;
        }else{
            return false;
        }
    }

    public function getTransactionNAddressValue(string $tx_id, int $n) :array {
        $transaction_vout_obj = $this->where('tx_id', '=', $tx_id)
            ->where('n', '=', $n)
            ->firstOrFail();
        return [
            'address_qtum' => $transaction_vout_obj->script_pub_key_addresses,
            'value' => $transaction_vout_obj->vout_value
        ];
    }


    public function getTransactionVoutArrByTXID(string $tx_id){
        return $this->where('tx_id', '=', $tx_id)->get();
    }
}
