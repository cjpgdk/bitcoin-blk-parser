<?php

use Cjpg\Bitcoin\Blk\Readers\BlkReader;

require '../vendor/autoload.php';

// the bitcoin data folder 
$bitcoinDataFolder = "/home/user/.bitcoin";
$bitcoinDataFolder = "/home/christianmj/.btcdata";

// create the reader, and tell it to load all blk*.dat files.
$reader = new BlkReader($bitcoinDataFolder.'/blocks', true);

// If you omit loading the files in the constructor you can load them with.
// $reader->loadData();

// Loop all the blk files in the repository 
$reader->rewind();

/*

You can use while, foreach or for loop.
 * 
while ($reader->valid())
 * 
foreach ($reader as $i => $file)
 * 
for ($i = 0; $i < $reader->count(); $i++)
    $reader->moveTo($i);
*/
while ($reader->valid()) {
    
    echo "Reading: ".$reader->fileName().PHP_EOL; //  blkXXXXX.dat
    //
    // echo $reader[$i].PHP_EOL; // /your/full/path/blocks/ blkXXXXX.dat
    
    foreach ($reader->blocks() as $idx => $block) {
        
        echo "Block: ".$block->blockHash().PHP_EOL;
        echo "  Time ......: ".date('r', $block->timestamp()).PHP_EOL;
        echo "  TX Count ..: ".$block->transactionCount().PHP_EOL;
        echo "  Size ......: ".$block->size().PHP_EOL;
        echo "  Difficulty : ".$block->difficulty().PHP_EOL.PHP_EOL;
        
        echo "  Transactions:".PHP_EOL;
        
        foreach ($block->transactions() as $tx) {
            echo "    TXID ...: ".$tx->txid.PHP_EOL;
            echo "    HASH ...: ".$tx->hash.PHP_EOL;
        }
        
        echo PHP_EOL;
    }
    
    echo "**************************************".PHP_EOL.PHP_EOL;
    $reader->next();
}
$reader->rewind();
