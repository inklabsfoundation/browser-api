<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use SoftDeletes;
    protected $table = 'transaction';
    protected $primaryKey = null;
    public $incrementing = false;

    public function addTransaction(string $tx_id, string $block_hash, int $tx_index, int $tx_size, int $tx_vsize, int $tx_version, int $lock_time, string $total_vout_value_hex, string $total_fee_hex, string $total_mined_hex){
        $this->tx_id = $tx_id;
        $this->block_hash = $block_hash;
        $this->tx_index = $tx_index;
        $this->tx_size = $tx_size;
        $this->tx_vsize = $tx_vsize;
        $this->tx_version = $tx_version;
        $this->lock_time = $lock_time;
        $this->total_vout_value = $total_vout_value_hex;
        $this->total_fee = $total_fee_hex;
        $this->total_mined = $total_mined_hex;

        if(!$this->isDirty() || $this->save()){
            return $this->tx_id;
        }else{
            return false;
        }
    }

    public function block_obj(){
        return $this->belongsTo('App\Block', 'block_hash', 'block_hash');
    }
    public function transaction_vin_obj_arr(){
        return $this->hasMany('App\TransactionVin', 'tx_id', 'tx_id');
    }
    public function transaction_vout_obj_arr(){
        return $this->hasMany('App\TransactionVout', 'tx_id', 'tx_id');
    }
    public function erc20_transaction_obj(){
        return $this->hasOne('App\ERC20Transaction', 'tx_id', 'tx_id');
    }
    public function address_transaction_obj_arr(){
        return $this->hasMany('App\AddressTransaction', 'tx_id', 'tx_id');
    }

    public function getTotalAmount(ERC20 $erc20_obj = null){
        $tmp = $this->with(['block_obj' => function($query){
            $query->whereNull('deleted_at');
        }]);

        if(!is_null($erc20_obj)) {
            $tmp = $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }

        return $tmp->count();
    }


    public function getAmountByDays(int $days, ERC20 $erc20_obj = null){
        $db_prefix = config('database.connections.'.config('database.default').'.prefix');

        $tmp = $this->join('block', 'block.block_hash', '=', 'transaction.block_hash')
            //->whereBetween('block.block_time', [Carbon::now()->addDay(-1000)->timestamp, Carbon::now()->timestamp]);
            ->whereBetween('block.block_time', [Carbon::now()->addDay(-$days+1)->timestamp, Carbon::now()->timestamp]);

        if(!is_null($erc20_obj)) {
            $tmp = $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }

        return $tmp->groupBy(DB::raw('FROM_UNIXTIME('.$db_prefix.'block.block_time, "%Y%m%d")'))
            ->select([DB::raw('FROM_UNIXTIME('.$db_prefix.'block.block_time, "%Y%m%d") as dates'), DB::raw('COUNT('.$db_prefix.'transaction.tx_id) as amount')])
            ->get();
    }

    public function getTransactionArr(int $per_page = 10, ERC20 $erc20_obj = null){

        $tmp = $this->join('block', 'block.block_hash', '=', 'transaction.block_hash');

        if(!is_null($erc20_obj)){
            $tmp = $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }

        return $tmp->orderBy('block.block_time', 'desc')
            ->orderBy('transaction.tx_index', 'desc')
            ->paginate($per_page);
    }

    public function getTransactionInfo(string $transaction_hash, ERC20 $erc20_obj = null){
        $tmp = $this->with(['transaction_vin_obj_arr', 'transaction_vout_obj_arr', 'erc20_transaction_obj', 'address_transaction_obj_arr', 'block_obj']);

        if(!is_null($erc20_obj)){
            $tmp = $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }

        return $tmp->where('transaction.tx_id', '=', $transaction_hash)
            ->firstOrFail();
    }

    public function getTransactionArrByBlockHash(string $block_hash){
        return $this->join('block', 'block.block_hash', '=', 'transaction.block_hash')
            ->where('transaction.block_hash', '=', $block_hash)
            ->orderBy('transaction.tx_index', 'asc')
            ->get();
    }

    public function getTransactionArrByBlockHashPaginate(string $block_hash, int $per_page, ERC20 $erc20_obj = null){
        $tmp = $this->join('block', 'block.block_hash', '=', 'transaction.block_hash')
            ->where('transaction.block_hash', '=', $block_hash);

        if(!is_null($erc20_obj)){
            $tmp = $tmp->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
        }else{
            $tmp = $tmp->with('erc20_transaction_obj')
                ->with('transaction_vin_obj_arr')
                ->with('transaction_vout_obj_arr');
        }

        return $tmp->orderBy('transaction.tx_index', 'desc')
            ->paginate($per_page);
    }
}
