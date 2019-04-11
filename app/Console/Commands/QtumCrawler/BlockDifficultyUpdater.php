<?php

namespace App\Console\Commands\QtumCrawler;

use App\Address;
use App\AddressERC20Balance;
use App\AddressTransaction;
use App\Block;
use App\ERC20;
use App\ERC20Transaction;
use app\Libraries\Classes\Qtum;
use App\Transaction;
use App\TransactionVin;
use App\TransactionVout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class BlockDifficultyUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qtum:crawl-block-difficulty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl Qtum block difficulty';

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

        $stop_block_height = 88000;
        $start_block_height = 1;
        while($start_block_height < $stop_block_height) {
            try {
                $db_current_block_obj = (new Block)->where('block_height', '=', $start_block_height)->firstOrFail();
                $this->info($db_current_block_obj->block_height . ' ' . $db_current_block_obj->block_hash);

                $chain_block_obj = Qtum::getBlockInfo($db_current_block_obj->block_hash);
                $db_current_block_obj->difficulty = bcdechex(bcmul(number_format($chain_block_obj['difficulty'], 8, '.', ''), pow(10, 8)));
                $db_current_block_obj->save();

                $start_block_height++;
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error($e->getTraceAsString());
                $this->error($e->getMessage());
                $this->error($e->getTraceAsString());
            }
        }
    }
}
