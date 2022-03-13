<?php

use Cjpg\Bitcoin\Blk\Readers\BlkReader;

require '../vendor/autoload.php';

// the bitcoin data folder 
$bitcoinDataFolder = "/home/user/.bitcoin";

// create the reader, and tell it to load all blk*.dat files.
$reader = new BlkReader($bitcoinDataFolder.'/blocks', true);

/*
 * while loop.
 * 
while ($reader->valid())

 * 
 * foreach loop
 * 
foreach ($reader as $i => $file)

 * 
 * for loop
 * 
for ($i = 0; $i < $reader->count(); $i++)
    $reader->moveTo($i);

 */
    
// Loop all the blk files in the repository 
while ($reader->valid()) {
    
    // echo the filename.
    echo "Reading: ".$reader->fileName().PHP_EOL; //  blkXXXXX.dat
    
    foreach ($reader->blocks() as $block) {

        // $block is an instance of \Cjpg\Bitcoin\Blk\BlockParser;
        echo "Block: ".$block->blockHash().PHP_EOL;
        echo "  Time ......: ".date('r', $block->timestamp()).PHP_EOL;
        echo "  TX Count ..: ".$block->transactionCount().PHP_EOL;
        echo "  Size ......: ".$block->size().PHP_EOL;
        echo "  Difficulty : ".$block->difficulty().PHP_EOL.PHP_EOL;
        
        foreach ($block->transactions() as $tx) {

            // $tx is an instance of \Cjpg\Bitcoin\Blk\TxParser;
            echo "    TXID ...: ".$tx->txid.PHP_EOL;

        }
        echo PHP_EOL;
    }
    echo "**************************************".PHP_EOL.PHP_EOL;
    
    $reader->next();
}
