<?php

namespace App\Console\Commands\QtumCrawler;

use App\Address;
use App\AddressERC20Balance;
use App\AddressTransaction;
use App\Block;
use App\ERC20;
use App\ERC20Transaction;
use App\Jobs\WebCacheJob;
use app\Libraries\Classes\Qtum;
use App\Transaction;
use App\TransactionVin;
use App\TransactionVout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BlockCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qtum:crawl-block';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Qtum block data';

    private $is_forked = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {//$hex = get_contract_address_from_tx('ae8269ec48f86067af602ea671909a33d7f55293d510a018d6aa0c1edcebff77', 0);dd($hex,Qtum::fromHexAddress($hex));
//        $hex = '8967bae43747688f7fb4ad0d5c12912938283e15';
//        dd($hex, Qtum::fromHexAddress($hex));
        while(true) {
            try {
                try {
                    $db_latest_block_obj = (new Block)->getLatestBlock();
                } catch (\Exception $e) {
                    $db_latest_block_obj = new \stdClass();
                    //$db_latest_block_obj->block_hash = '0000000000000000000000000000000000000000000000000000000000000000';
                    $db_latest_block_obj->block_height = 0;
                    $db_latest_block_obj->block_hash = Qtum::getBlockHash($db_latest_block_obj->block_height);
                }
                $chain_best_block_hash = Qtum::getBestBlockHash();
                if ($db_latest_block_obj->block_hash == $chain_best_block_hash) {
                    // is newest block in database
                    $this->alert('newest block in database');
                    $this->alert($db_latest_block_obj->block_hash);

                    sleep(5); // wait for new block

                    return false;
                }

                $chain_next_block_hash = Qtum::getBlockHash($db_latest_block_obj->block_height + 1);
                $chain_next_block_obj = Qtum::getBlockInfo($chain_next_block_hash);


                $this->is_forked = false;
                // determine if forked
                if ($chain_next_block_obj['previousblockhash'] != $db_latest_block_obj->block_hash) {
                    $this->warn('forked: [need_prv]' .$db_latest_block_obj->block_hash . '[got]'. $chain_next_block_obj['previousblockhash'] .'|'.json_encode($chain_next_block_obj));
                    bug_report('`forked: [need_prv]' .$db_latest_block_obj->block_hash . '[got]'. $chain_next_block_obj['previousblockhash'].'`'.'  ```'.json_encode($chain_next_block_obj).'```');
                    //@todo forked
                    $this->is_forked = true;

                    DB::beginTransaction();


                    try {
                        // 获取prev 区块信息，判断是否在数据库
                        $db_block_obj = null;
                        while (is_null($db_block_obj)) {
                            try {
                                try {
                                    $db_block_obj = (new Block)->getBlockByBlockHash($chain_next_block_obj['previousblockhash']);
                                }catch (\Exception $e){
                                    throw new \Exception('previous_block_hash not found');
                                }
                                // 如果数据库中存在prev block，说明该区块为分叉原点
                                // 将分叉原点之后的数据库区块标记为无效
                                $db_orphaned_block_obj_arr = (new Block)->getBlockArrAfterBlockHeight($db_block_obj->block_height);
                                $result = $this->deleteBlockNextBlock($db_orphaned_block_obj_arr[0]);
                                if ($result == false) {
                                    //DB::rollBack();
                                    $this->warn('Delete block next block failed' . json_encode($db_orphaned_block_obj_arr[0]));
                                    bug_report('`Delete block next block failed' . json_encode($db_orphaned_block_obj_arr[0]) . '`');

                                    //break;
                                }
                                foreach ($db_orphaned_block_obj_arr as $db_orphaned_block_obj) {
                                    $this->warn('doing rollback:' . $db_orphaned_block_obj->block_hash);
                                    $db_orphaned_transaction_obj_arr = (new Transaction)->getTransactionArrByBlockHash($db_orphaned_block_obj->block_hash);
                                    $this->deleteERC20($db_orphaned_transaction_obj_arr);
                                    $this->deleteBlockData($db_orphaned_block_obj, $db_orphaned_transaction_obj_arr, $db_orphaned_block_obj_arr[0]);
                                }
                                break;
                            } catch (\Exception $e) {
                                if($e->getMessage() == 'previous_block_hash not found') {
                                    // 如果不存在，那么获取前一个区块
                                    $chain_next_block_obj = Qtum::getBlockInfo($chain_next_block_obj['previousblockhash']);
                                    $db_block_obj = null;
                                    continue;
                                }
                                throw $e;
                            }
                        }
                    }catch (\Exception $e){
                        DB::rollBack();
                        throw $e;
                    }

                    DB::commit();
                    continue;
                }


                DB::beginTransaction();
                // not forked, sync
                try{
                    $this->saveERC20($chain_next_block_obj['tx']);

                    $this->saveBlockData($chain_next_block_obj);
                }catch (\Exception $e){
                    DB::rollBack();
                    throw $e;
                }
                DB::commit();

                // cache new data to redis
                dispatch(new WebCacheJob);

                $this->info($chain_next_block_obj['hash']);

                // @todo get current block height in database, determine whether it is the newest block
                // @todo if not the newest, `getblockhash` `getblock`
                // @todo save block info

                // @todo get prev_block_hash and compare it with database last block hash, determine if forked
                // @todo if forked, fall back database TODO
                // @todo if not, continue

                // @todo get transaction array
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error($e->getTraceAsString());
                $this->error($e->getMessage());
                $this->error($e->getTraceAsString());
            }
        }
    }

    private function saveERC20(array $transaction_hash_arr){
        foreach ($transaction_hash_arr as $transaction_hash) {
            $transaction_receipt_obj = Qtum::getTransactionReceipt($transaction_hash);

            if(empty($transaction_receipt_obj)){
                continue;
            }

            $this->addERC20($transaction_receipt_obj);

            $this->addERC20Transaction($transaction_receipt_obj);

            $this->addERC20AddressBalance($transaction_receipt_obj);
        }
    }

    private function addERC20(array $transaction_receipt_obj) {
        $erc20_address_hex = $transaction_receipt_obj[0]['contractAddress'];

        try {
            $creator_erc20_balance = Qtum::getAddressERC20Balance($transaction_receipt_obj[0]['contractAddress'], $transaction_receipt_obj[0]['from']);
            if ($creator_erc20_balance === '') {
                // not erc20
                return false;
            }
            $erc20_decimal = Qtum::getERC20Decimal($erc20_address_hex);
            if ($erc20_decimal === null) {
                // not erc20
                return false;
            }
            $erc20_total_supply = Qtum::getERC20TotalSupply($erc20_address_hex);
            if ($erc20_total_supply === '') {
                // not erc20
                return false;
            }
        }catch (\Exception $e){
            // not erc20
            Log::info($transaction_receipt_obj[0]['transactionHash'].' is not an erc20 transaction');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return false;
        }

        $erc20_address_qtum = Qtum::fromHexAddress($erc20_address_hex);
        $creator_address_hex = $transaction_receipt_obj[0]['from'];
        $creator_address_qtum = Qtum::fromHexAddress($creator_address_hex);
        $tx_id = $transaction_receipt_obj[0]['transactionHash'];
        $block_hash = $transaction_receipt_obj[0]['blockHash'];
        $erc20_name = Qtum::getERC20Name($erc20_address_hex);
        $erc20_symbol = Qtum::getERC20Symbol($erc20_address_hex);

        try {
            $erc20_obj = (new ERC20)->getERC20ByAddressHex($erc20_address_hex);
            $erc20_id = $erc20_obj->erc20_id;
        }catch (\Exception $e){
            $erc20_id = (new ERC20)->addERC20($erc20_address_qtum, $erc20_address_hex, $creator_address_qtum, $creator_address_hex, $tx_id, $block_hash, $erc20_name, $erc20_symbol, $erc20_total_supply, $erc20_decimal);
        }

        return $erc20_id;
    }

    // place after add erc20 function
    private function addERC20Transaction(array $transaction_receipt_obj){

        $erc20_address_hex = $transaction_receipt_obj[0]['contractAddress'];
        try {
            $erc20_obj = (new ERC20)->getERC20ByAddressHex($erc20_address_hex);
        }catch(\Exception $e){
            // after add erc20, cannot match, so it can't be an erc20 token
            Log::info($transaction_receipt_obj[0]['transactionHash'].' is not an erc20 transaction');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return false;
        }

        if(empty($transaction_receipt_obj[0]['log'])){
            // transfer error
            return false;
        }
        if($transaction_receipt_obj[0]['log'][0]['topics'][0] != 'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'){
            // not transfer topics
            return false;
        }

        $tx_id = $transaction_receipt_obj[0]['transactionHash'];
        $block_hash = $transaction_receipt_obj[0]['blockHash'];
        $erc20_id = $erc20_obj->erc20_id;
        $erc20_symbol = $erc20_obj->erc20_symbol;
        $sender_address_hex = $transaction_receipt_obj[0]['from'];
        $sender_address_qtum = Qtum::fromHexAddress($sender_address_hex);
        $receiver_address_hex = substr($transaction_receipt_obj[0]['log'][0]['topics'][2], 24);
        $receiver_address_qtum = Qtum::fromHexAddress($receiver_address_hex);
        $erc20_value = ltrim($transaction_receipt_obj[0]['log'][0]['data'], '0');

        $tx_id = (new ERC20Transaction)->addERC20Transaction($tx_id, $block_hash, $erc20_id, $erc20_symbol, $sender_address_hex, $sender_address_qtum, $receiver_address_hex, $receiver_address_qtum, $erc20_value);

        return $tx_id;
    }

    private function addERC20AddressBalance(array $transaction_receipt_obj){
        $erc20_address_hex = $transaction_receipt_obj[0]['contractAddress'];
        try {
            $erc20_obj = (new ERC20)->getERC20ByAddressHex($erc20_address_hex);
        }catch(\Exception $e){
            // after add erc20, cannot match, so it can't be an erc20 token
            Log::info($transaction_receipt_obj[0]['transactionHash'].' is not an erc20 transaction');
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return false;
        }

        if(empty($transaction_receipt_obj[0]['log'])){
            // transfer error
            return false;
        }
        if($transaction_receipt_obj[0]['log'][0]['topics'][0] != 'ddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef'){
            // not transfer topics
            return false;
        }

        $tx_id = $transaction_receipt_obj[0]['transactionHash'];
        $block_hash = $transaction_receipt_obj[0]['blockHash'];
        $block_height = Qtum::getBlockInfo($block_hash)['height'];
        $erc20_id = $erc20_obj->erc20_id;
        $erc20_symbol = $erc20_obj->erc20_symbol;

        // sender
        $sender_address_hex = $transaction_receipt_obj[0]['from'];
        $sender_address_qtum = Qtum::fromHexAddress($sender_address_hex);
        $sender_erc20_balance_hex = Qtum::getAddressERC20Balance($erc20_address_hex, $sender_address_hex);
        try {
            $result = (new AddressERC20Balance)->updateAddressERC20Balance($sender_address_hex, $erc20_id, $sender_erc20_balance_hex, $block_height, $block_hash);
        }catch (\Exception $e){
            $result = (new AddressERC20Balance)->addAddressERC20Balance($sender_address_qtum, $sender_address_hex, $erc20_id, $erc20_symbol, $sender_erc20_balance_hex, $block_height, $block_hash);
        }

        if($result == false){
            // 更新erc20余额失败，等下一个改变余额的区块吧
            return false;
        }

        // receiver
        $receiver_address_hex = substr($transaction_receipt_obj[0]['log'][0]['topics'][2], 24);
        $receiver_address_qtum = Qtum::fromHexAddress($receiver_address_hex);
        $receiver_erc20_balance_hex = Qtum::getAddressERC20Balance($erc20_address_hex, $receiver_address_hex);
        try {
            $result = (new AddressERC20Balance)->updateAddressERC20Balance($receiver_address_hex, $erc20_id, $receiver_erc20_balance_hex, $block_height, $block_hash);
        }catch (\Exception $e){
            $result = (new AddressERC20Balance)->addAddressERC20Balance($receiver_address_qtum, $receiver_address_hex, $erc20_id, $erc20_symbol, $receiver_erc20_balance_hex, $block_height, $block_hash);
        }

        if($result == false){
            // 更新erc20余额失败，等下一个改变余额的区块吧
            return false;
        }


        return true;
    }

    private function saveAddressQtumBalance(string $address_qtum, string $qtum_balance_dec, string $updated_at_block_height, string $updated_at_block_hash){
        try {
            $result = (new Address)->updateAddressQtumBalance($address_qtum, $qtum_balance_dec, $updated_at_block_height, $updated_at_block_hash);
        }catch (\Exception $e){
            $address_hex = Qtum::getHexAddress($address_qtum);
            $result = (new Address)->addAddress($address_qtum, $address_hex, $qtum_balance_dec, $updated_at_block_height, $updated_at_block_hash);
        }
        if($result == false){
            return false;
        }

        return true;
    }

    private function saveBlockData(array $chain_block_obj){

            $tx_index = 0;
            foreach ($chain_block_obj['tx'] as $transaction_hash) {
                $proof_of_what = 'POW';

                $raw_transaction = Qtum::getRawTransaction($transaction_hash);
                $chain_transaction_obj = Qtum::decodeRawTransaction($raw_transaction);

                // tx related addresses
                $related_address_arr = [];

                // vin
                $total_vin_value = 0;
                foreach ($chain_transaction_obj['vin'] as $chain_transaction_vin_obj) {
                    if (isset($chain_transaction_vin_obj['txid'])) {
                        // POS
                        $proof_of_what = 'POS';
                        // get prev transaction vout value
                        $prev_vout_data = (new TransactionVout)->getTransactionNAddressValue($chain_transaction_vin_obj['txid'], $chain_transaction_vin_obj['vout']);
                        // @todo waiting4fix witness_v0_scripthash
                        if($prev_vout_data['address_qtum'] == 'witness_v0_scripthash__waiting4fix'){$prev_vout_data['address_hex'] = 'witness_v0_scripthash__waiting4fix';}
                        else{$prev_vout_data['address_hex'] = Qtum::getHexAddress($prev_vout_data['address_qtum']);}
                        $result = (new TransactionVin)->addTransactionVin($chain_transaction_obj['txid'], $chain_transaction_vin_obj['txid'], $chain_transaction_vin_obj['vout'], $prev_vout_data['address_qtum'], $prev_vout_data['address_hex'], $prev_vout_data['value'], isset($chain_transaction_vin_obj['scriptSig']) ? json_encode($chain_transaction_vin_obj['scriptSig']) : '', null, '', $chain_transaction_vin_obj['sequence']);
                    } else {
                        // POW
                        $proof_of_what = 'POW';

                        $result = (new TransactionVin)->addTransactionVin(
                            $chain_transaction_obj['txid'],
                            null,
                            null,
                            null,
                            null,
                            null,
                            isset($chain_transaction_vin_obj['scriptSig']) ? json_encode($chain_transaction_vin_obj['scriptSig']) : '',
                            $chain_transaction_vin_obj['coinbase'],
                            isset($chain_transaction_vin_obj['txinwitness']) ? $chain_transaction_vin_obj['txinwitness'][0] : '',
                            $chain_transaction_vin_obj['sequence']);
                    }
                    if ($result === false) {
                        throw new \Exception('TransactionVin Add Error.' . json_encode($chain_transaction_obj));
                    }

                    // tx related addresses
                    if (isset($prev_vout_data)) {
                        $related_address_arr[] = $prev_vout_data['address_qtum'];
                        // total vin value
                        $total_vin_value = bcadd($total_vin_value, bchexdec($prev_vout_data['value']));

                        if(false == $this->saveAddressQtumBalance($prev_vout_data['address_qtum'], -1*bchexdec($prev_vout_data['value']), $chain_block_obj['height'], $chain_block_obj['hash'])){
                            throw new \Exception('Address Vin Add Error.' . json_encode($chain_transaction_obj));
                        }
                    }
                }

                // vout
                $total_vout_value = 0;
                foreach ($chain_transaction_obj['vout'] as $chain_transaction_vout_obj) {
                    switch($chain_transaction_vout_obj['scriptPubKey']['type']){
                        case 'create':
                            //@todo add contract
                            $script_pub_key_addresses = Qtum::fromHexAddress(get_contract_address_from_tx($chain_transaction_obj['txid'], $chain_transaction_vout_obj['n']));
                            break;
                        case 'call':
                            $script_pub_key_addresses = Qtum::fromHexAddress(explode(' ', $chain_transaction_vout_obj['scriptPubKey']['asm'])[4]);
                            break;
                        case 'witness_v0_keyhash': //@todo to be test
                            $script_pub_key_addresses = Qtum::fromHexAddress(explode(' ', $chain_transaction_vout_obj['scriptPubKey']['asm'])[1]);
                            break;
                        case 'witness_v0_scripthash': //@todo to be fixed
                            $script_pub_key_addresses = 'witness_v0_scripthash__waiting4fix';
                            break;
                        default:
                            $script_pub_key_addresses = isset($chain_transaction_vout_obj['scriptPubKey']['addresses'][0]) ? $chain_transaction_vout_obj['scriptPubKey']['addresses'][0] : null;
                    }

                    $result = (new TransactionVout)->addTransactionVout($chain_transaction_obj['txid'], $chain_transaction_vout_obj['n'], bcdechex(bcmul($chain_transaction_vout_obj['value'],pow(10, 8))), $chain_transaction_vout_obj['scriptPubKey']['asm'], $chain_transaction_vout_obj['scriptPubKey']['hex'], $chain_transaction_vout_obj['scriptPubKey']['type'], isset($chain_transaction_vout_obj['scriptPubKey']['reqSigs']) ? $chain_transaction_vout_obj['scriptPubKey']['reqSigs'] : null, $script_pub_key_addresses);
                    if ($result === false) {
                        throw new \Exception('TransactionVout Add Error.' . json_encode($chain_transaction_obj['txid'], $chain_transaction_vout_obj['n'], bcdechex(bcmul($chain_transaction_vout_obj['value'],pow(10, 8))), $chain_transaction_vout_obj['scriptPubKey']['asm'], $chain_transaction_vout_obj['scriptPubKey']['hex'], $chain_transaction_vout_obj['scriptPubKey']['type'], isset($chain_transaction_vout_obj['scriptPubKey']['reqSigs']) ? $chain_transaction_vout_obj['scriptPubKey']['reqSigs'] : null, $script_pub_key_addresses));
                    }

                    // total vout value
                    $total_vout_value = bcadd($total_vout_value, bcmul($chain_transaction_vout_obj['value'], pow(10, 8)));

                    // tx related addresses
                    if (isset($chain_transaction_vout_obj['scriptPubKey']['addresses'][0])) {
                        $related_address_arr[] = $chain_transaction_vout_obj['scriptPubKey']['addresses'][0];
                        if(false == $this->saveAddressQtumBalance($chain_transaction_vout_obj['scriptPubKey']['addresses'][0], 1*bcmul($chain_transaction_vout_obj['value'], pow(10, 8)), $chain_block_obj['height'], $chain_block_obj['hash'])){
                            throw new \Exception('Address Vout Add Error.' . json_encode($chain_transaction_obj));
                        }
                    }
                }

                // receipt: fee, erc20 transaction
                $total_mined = 0;
                $total_fee = $total_vin_value - $total_vout_value;
                if($total_vin_value <= 0){
                    $total_fee = 0;
                }
                if($total_fee <= 0){
                    $total_fee = 0;
                    $total_mined = $total_vout_value - $total_vin_value;
                }

                // transaction
                $db_tx_id = (new Transaction)->addTransaction($chain_transaction_obj['txid'], $chain_block_obj['hash'], $tx_index, $chain_transaction_obj['size'], $chain_transaction_obj['vsize'], $chain_transaction_obj['version'], $chain_transaction_obj['locktime'], bcdechex($total_vout_value), bcdechex($total_fee), bcdechex($total_mined));
                if ($db_tx_id === false) {
                    throw new \Exception('Transaction Add Error.' . json_encode([$chain_transaction_obj['txid'], $chain_block_obj['hash'], $chain_transaction_obj['size'], $chain_transaction_obj['vsize'], $chain_transaction_obj['version'], $chain_transaction_obj['locktime'], bcdechex($total_vout_value), bcdechex($total_fee), bcdechex($total_mined)]));
                }


                // tx addresses relation
                foreach (array_unique($related_address_arr) as $related_address_qtum) {
                    $related_address_hex = Qtum::getHexAddress($related_address_qtum);
                    $result = (new AddressTransaction)->addAddressTransaction($related_address_qtum, $related_address_hex, $chain_transaction_obj['txid']);
                    if ($result === false) {
                        throw new \Exception('AddressTransaction Add Error.' . json_encode([$related_address_qtum, $related_address_hex, $chain_transaction_obj['txid']]));
                    }
                }

                $tx_index++;
            }

            // block
            try {
                $db_block_hash = (new Block)->addBlock($chain_block_obj['hash'], $chain_block_obj['strippedsize'], $chain_block_obj['size'], $chain_block_obj['weight'], $chain_block_obj['height'], $chain_block_obj['version'], $chain_block_obj['versionHex'], $chain_block_obj['merkleroot'], $chain_block_obj['hashStateRoot'], $chain_block_obj['hashUTXORoot'], $chain_block_obj['time'], $chain_block_obj['mediantime'], $chain_block_obj['nonce'], $chain_block_obj['bits'], bcdechex(bcmul(number_format($chain_block_obj['difficulty'], 8, '.', ''), pow(10, 8))), $chain_block_obj['chainwork'], $chain_block_obj['previousblockhash'], isset($chain_block_obj['nextblockhash']) ? $chain_block_obj['nextblockhash'] : null, $chain_block_obj['flags'], $chain_block_obj['proofhash'], $chain_block_obj['modifier'], isset($chain_block_obj['signature']) ? $chain_block_obj['signature'] : '');

                if ($db_block_hash === false) {
                    throw new \Exception('Block Add Error.' . json_encode([$chain_block_obj['hash'], $chain_block_obj['strippedsize'], $chain_block_obj['size'], $chain_block_obj['weight'], $chain_block_obj['height'], $chain_block_obj['version'], $chain_block_obj['versionHex'], $chain_block_obj['merkleroot'], $chain_block_obj['hashStateRoot'], $chain_block_obj['hashUTXORoot'], $chain_block_obj['time'], $chain_block_obj['mediantime'], $chain_block_obj['nonce'], $chain_block_obj['bits'], bcdechex(bcmul(number_format($chain_block_obj['difficulty'], 8, '.', ''), pow(10, 8))), $chain_block_obj['chainwork'], $chain_block_obj['previousblockhash'], isset($chain_block_obj['nextblockhash']) ? $chain_block_obj['nextblockhash'] : null, $chain_block_obj['flags'], $chain_block_obj['proofhash'], $chain_block_obj['modifier'], isset($chain_block_obj['signature']) ? $chain_block_obj['signature'] : '']));
                }
            }catch (\Exception $e){
                if($e->getCode() == '23000' && $this->is_forked === false){
                    // save current block to not deleted
                    $restore_result = (new Block)->restoreBlockByBlockHash($chain_block_obj['hash']);
                    if ($restore_result === false) {
                        throw new \Exception('Block Restore Error.' . json_encode([$chain_block_obj['hash'], $chain_block_obj['strippedsize'], $chain_block_obj['size'], $chain_block_obj['weight'], $chain_block_obj['height'], $chain_block_obj['version'], $chain_block_obj['versionHex'], $chain_block_obj['merkleroot'], $chain_block_obj['hashStateRoot'], $chain_block_obj['hashUTXORoot'], $chain_block_obj['time'], $chain_block_obj['mediantime'], $chain_block_obj['nonce'], $chain_block_obj['bits'], bcdechex(bcmul(number_format($chain_block_obj['difficulty'], 8, '.', ''), pow(10, 8))), $chain_block_obj['chainwork'], $chain_block_obj['previousblockhash'], isset($chain_block_obj['nextblockhash']) ? $chain_block_obj['nextblockhash'] : null, $chain_block_obj['flags'], $chain_block_obj['proofhash'], $chain_block_obj['modifier'], isset($chain_block_obj['signature']) ? $chain_block_obj['signature'] : '']));
                    }
                }else{
                    throw $e;
                }
            }

            // save previous block
            $db_block_hash = (new Block)->saveBlock($chain_block_obj['previousblockhash'], ['next_block_hash'=>$chain_block_obj['hash']]);
            if ($db_block_hash === false) {
                throw new \Exception('Previous Block Save Error.' . json_encode([$chain_block_obj['hash'], $chain_block_obj['strippedsize'], $chain_block_obj['size'], $chain_block_obj['weight'], $chain_block_obj['height'], $chain_block_obj['version'], $chain_block_obj['versionHex'], $chain_block_obj['merkleroot'], $chain_block_obj['hashStateRoot'], $chain_block_obj['hashUTXORoot'], $chain_block_obj['time'], $chain_block_obj['mediantime'], $chain_block_obj['nonce'], $chain_block_obj['bits'], bcdechex(bcmul(number_format($chain_block_obj['difficulty'], 8, '.', ''), pow(10, 8))), $chain_block_obj['chainwork'], $chain_block_obj['previousblockhash'], isset($chain_block_obj['nextblockhash']) ? $chain_block_obj['nextblockhash'] : null, $chain_block_obj['flags'], $chain_block_obj['proofhash'], $chain_block_obj['modifier'], isset($chain_block_obj['signature']) ? $chain_block_obj['signature'] : '']));
            }
    }

    private function deleteBlockNextBlock(Block $block_obj){
        $block_obj->next_block_hash = null;
        if(!$block_obj->isDirty() || $block_obj->save()){
            return $block_obj->{$block_obj->primaryKey};
        }else{
            return false;
        }
    }
    private function deleteERC20($transaction_obj_arr){
        foreach($transaction_obj_arr as $transaction_obj){

            // delete erc20
            (new ERC20)->where('tx_id', '=', $transaction_obj->tx_id)->delete();

            // delete erc20 transaction
            (new ERC20Transaction)->where('tx_id', '=', $transaction_obj->tx_id)->delete();

            //@todo update erc20 balance
        }
    }
    private function deleteBlockData(Block $chain_block_obj, $transaction_obj_arr, Block $base_block_obj){
        foreach($transaction_obj_arr as $transaction_obj) {
            // get vin
            $vin_obj_arr = (new TransactionVin)->getTransactionVinArrByTXID($transaction_obj->tx_id);
            foreach($vin_obj_arr as $vin_obj){
                // save vin address qtum
                if(!is_null($vin_obj->prev_vout_address_qtum)) {
                    if (false == $this->saveAddressQtumBalance($vin_obj->prev_vout_address_qtum, 1 * bchexdec($vin_obj->prev_vout_value), $base_block_obj->block_height, $base_block_obj->block_hash)) {
                        throw new \Exception('Address Vin Add Rollback Error.' . json_encode($transaction_obj));
                    }
                }
            }
            (new TransactionVin)->where('tx_id', '=', $vin_obj->tx_id)->delete();

            // get vout
            $vout_obj_arr = (new TransactionVout)->getTransactionVoutArrByTXID($transaction_obj->tx_id);
            foreach($vout_obj_arr as $vout_obj){
                // save vout address qtum
                if(!is_null($vout_obj->script_pub_key_addresses) && $vout_obj->script_pub_key_addresses != 'witness_v0_scripthash__waiting4fix'){
                    if(false == $this->saveAddressQtumBalance($vout_obj->script_pub_key_addresses, -1*bchexdec($vout_obj->vout_value), $base_block_obj->block_height, $base_block_obj->block_hash)){
                        throw new \Exception('Address Vout Add Rollback Error.' . json_encode($transaction_obj));
                    }
                }
            }
            (new TransactionVout)->where('tx_id', '=', $vout_obj->tx_id)->delete();


            // delete address transaction relation
            (new AddressTransaction)->where('tx_id', '=', $transaction_obj->tx_id)->delete();

            // delete transaction
            (new Transaction)->where('tx_id', '=', $transaction_obj->tx_id)->delete();
        }

        // delete block
        (new Block)->where('block_hash', '=', $chain_block_obj->block_hash)->delete();
    }
}
