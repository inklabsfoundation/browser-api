<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ERC20Transaction extends Model
{
    use SoftDeletes;
    protected $table = 'erc20_transaction';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addERC20Transaction(string $tx_id, string $block_hash, int $erc20_id, string $erc20_symbol, string $sender_address_hex, string $sender_address_qtum, string $receiver_address_hex, string $receiver_address_qtum, string $erc20_value){
        $this->tx_id = $tx_id;
        $this->block_hash = $block_hash;
        $this->erc20_id = $erc20_id;
        $this->erc20_symbol = $erc20_symbol;
        $this->sender_address_hex = $sender_address_hex;
        $this->sender_address_qtum = $sender_address_qtum;
        $this->receiver_address_hex = $receiver_address_hex;
        $this->receiver_address_qtum = $receiver_address_qtum;
        $this->erc20_value = $erc20_value;

        if(!$this->isDirty() || $this->save()){
            return $this->tx_id;
        }else{
            return false;
        }
    }

    public function transaction_obj(){
        $this->belongsTo('App\Transaction', 'tx_id', 'tx_id');
    }

    public function getAddressERC20TransactionArrPaginate(string $address_qtum, int $per_page, ERC20 $erc20_obj = null){
        $tmp = $this->join('transaction', 'transaction.tx_id', '=', 'erc20_transaction.tx_id')
            ->join('block', 'block.block_hash', '=', 'transaction.block_hash');

        return $tmp->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id)
            ->where(function($query) use ($address_qtum){
                $query->where('erc20_transaction.sender_address_qtum', '=', $address_qtum)
                    ->orWhere('erc20_transaction.receiver_address_qtum', '=', $address_qtum);
        })
            ->orderBy('block.block_time', 'desc')
            ->orderBy('transaction.tx_index', 'desc')
            ->paginate($per_page);
    }
}
