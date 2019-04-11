<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Block extends Model
{
    protected $table = 'block';
    protected $primaryKey = 'block_hash';
    public $incrementing = false;

    use SoftDeletes;

    public function addBlock(string $block_hash, int $strippedsize, int $block_size, int $block_weight, int $block_height, int $block_version, string $version_hex, string $merkleroot, string $hash_state_root, string $hash_utxo_root, int $block_time, int $median_time, int $nonce, string $block_bits, string $difficulty, string $chain_work, string $previous_block_hash, string $next_block_hash = null, string $flags, string $proof_hash, string $modifier, string $signature = ''){
        $this->block_hash = $block_hash;
        $this->strippedsize = $strippedsize;
        $this->block_size = $block_size;
        $this->block_weight = $block_weight;
        $this->block_height = $block_height;
        $this->block_version = $block_version;
        $this->version_hex = $version_hex;
        $this->merkleroot = $merkleroot;
        $this->hash_state_root = $hash_state_root;
        $this->hash_utxo_root = $hash_utxo_root;
        $this->block_time = $block_time;
        $this->median_time = $median_time;
        $this->nonce = $nonce;
        $this->block_bits = $block_bits;
        $this->difficulty = $difficulty;
        $this->chain_work = $chain_work;
        $this->previous_block_hash = $previous_block_hash;
        $this->next_block_hash = $next_block_hash;
        $this->flags = $flags;
        $this->proof_hash = $proof_hash;
        $this->modifier = $modifier;
        $this->signature = $signature;

        if(!$this->isDirty() || $this->save()){
            return $this->{$this->primaryKey};
        }else{
            return false;
        }
    }

    public function saveBlock(string $block_hash, array $data){
        $block_obj = $this->getBlockByBlockHash($block_hash);

        $key_arr = [
            'block_hash',
            'strippedsize',
            'block_size',
            'block_weight',
            'block_height',
            'block_version',
            'version_hex',
            'merkleroot',
            'hash_state_root',
            'hash_utxo_root',
            'block_time',
            'median_time',
            'nonce',
            'block_bits',
            'difficulty',
            'chain_work',
            'previous_block_hash',
            'next_block_hash',
            'flags',
            'proof_hash',
            'modifier',
            'signature',
        ];

        foreach($key_arr as $key){
            if(array_key_exists($key, $data)){
                $block_obj->{$key} = $data[$key];
            }
        }

        if(!$block_obj->isDirty() || $block_obj->save()){
            return $block_obj->{$this->primaryKey};
        }else{
            return false;
        }


    }

    public function getBlockByBlockHash(string $block_hash, $with_trashed = false){
        $tmp = $this->where('block.block_hash', '=', $block_hash);
        if($with_trashed){
            $tmp = $tmp->withTrashed();
        }
        return $tmp->firstOrFail();
    }

    public function restoreBlockByBlockHash(string $block_hash){
        $block_obj = $this->getBlockByBlockHash($block_hash, true);

        return $block_obj->restore();
    }

    public function transaction_obj_arr(){
        return $this->hasMany('App\Transaction', 'block_hash', 'block_hash');
    }

    public function getLatestBlock(){
        return $this->orderBy('block_height', 'desc')->firstOrFail();
    }

    public function getBlockInfo(string $block_hash, ERC20 $erc20_obj = null){
        return $this->where('block.block_hash', '=', $block_hash)
            ->firstOrFail();
    }

    public function getBlockArrPaginate(int $per_page, ERC20 $erc20_obj = null){
        return $this->with(['transaction_obj_arr'=>function($query) use ($erc20_obj){
            if(!is_null($erc20_obj)){
                $query->join('erc20_transaction', 'erc20_transaction.tx_id', '=', 'transaction.tx_id')
                    ->where('erc20_transaction.erc20_id', '=', $erc20_obj->erc20_id);
            }
        }])
            ->orderBy('block.block_time', 'desc')
            ->paginate($per_page);
    }

    public function getBlockArrAfterBlockHeight(int $block_height){
        return $this->where('block_height', '>', $block_height)
            ->orderBy('block.block_height', 'asc')
            ->get();
    }
}
