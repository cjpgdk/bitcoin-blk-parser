<?php

use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Cjpg\Bitcoin\Blk\InputType;
use Cjpg\Bitcoin\Blk\MoneyUnit;

require '../vendor/autoload.php';

// the data folder 
$dataFolder = "/var/www/data";

// create the reader, and tell it NOT to load blk*.dat files.
$reader = new BlkReader($dataFolder, false);

// main net blk file, this is the default.
// $reader->setMagicBytes("\xf9\xbe\xb4\xd9");

// now we add our custom file, we know the filename.
$reader->add('updloaded.dat', 0);
// $reader->offsetSet(0, 'updloaded.dat');

// the new file is added at the index we sat to 0
echo $reader[0].PHP_EOL; // /var/www/data/updloaded.dat;

// since we set the offset to zero (0) we can just read the blocks.
// if we set the offset to 99 or some other offset we need to move to that offset
// $reader->moveTo(99)->blocks();
foreach ($reader->blocks() as $block) {
    
    // $block is an instance of \Cjpg\Bitcoin\Blk\BlockParser;
    echo "Block: ".$block->blockHash().PHP_EOL;
    echo "  Time ......: ".date('r', $block->timestamp()).PHP_EOL;
    echo "  TX Count ..: ".$block->transactionCount().PHP_EOL;
    echo "  Size ......: ".$block->size().PHP_EOL;
    echo "  Difficulty : ".$block->difficulty().PHP_EOL.PHP_EOL;
    
    // the block as a binary string
    // $block->block($hex = false);
    
    // the block as a hexadecimal string
    // $block->block($hex = true);
    
    echo "  Transactions:".PHP_EOL;
    
    // transactions.
    foreach ($block->transactions() as $tx) {
        
        // $tx is an instance of \Cjpg\Bitcoin\Blk\TxParser;
        echo "    TXID ...: ".$tx->txid.PHP_EOL;
        echo "    HASH ...: ".$tx->hash.PHP_EOL;
        echo "    Size ...: ".$tx->size.PHP_EOL;
        echo "    SegWit .: ".($tx->segwit ? 'Yes':'No').PHP_EOL;
        
        // inputs.
        echo PHP_EOL;
        echo "    Vins ...: ".$tx->inputCount.PHP_EOL;
        
        foreach ($tx->inputs as $vi => $vin) {
            
            // $vin->isCoinbase() == ($vin->type == InputType::COINBASE)
            if ($vin->type == InputType::COINBASE) {
                
                echo "     - Coinbase (Hex) ....: ".$vin->scriptSig.PHP_EOL;
                
            } else {
                
                echo "     - Spending tx .......: ".$vin->txid.PHP_EOL;
                echo "     - Spending tx output : ".$vin->vout.PHP_EOL;
                echo "     - ScriptSig (Hex) ...: ".$vin->scriptSig.PHP_EOL;
                echo "     - Sequence ..........: ".$vin->sequence.PHP_EOL;
                
            }

            // if $block->segwit then witness flag is set in one or more 
            // tranactions, but if the witness data for a tranaction is empty
            // the 'witness' is removed.
            if ($vin->witness) {
                for ($i = 0; $i < count($vin->witness); $i++) {
                    echo "     - witness[$i] (Hex) .: ".$vin->witness[$i].PHP_EOL;
                }
            }
            echo PHP_EOL;
        }
        
        // outputs.
        echo PHP_EOL;
        echo "    Vouts ..: ".$tx->outputCount.PHP_EOL;
        
        foreach ($tx->outputs as $vo => $vout) {
            
            echo "     - Value (Satoshis) ..: ".$vout->value.PHP_EOL;
            echo "     - Value (BTC) .......: ".$vout->value->format(MoneyUnit::BTC).PHP_EOL;
            echo "     - N .................: ".$vout->n.PHP_EOL;
            echo "     - scriptPubKey (Hex) : ".$vout->scriptPubKey.PHP_EOL;
            
            echo PHP_EOL;
        }
        
    }
    echo "**************************************".PHP_EOL.PHP_EOL;
            
}