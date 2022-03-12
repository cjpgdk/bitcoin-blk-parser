<?php

use Cjpg\Bitcoin\Blk\Readers\BlkReader;

require '../vendor/autoload.php';

// the bitcoin data folder 
$bitcoinDataFolder = "/home/user/.bitcoin";

// create the reader, and tell it to load all blk*.dat files.
$reader = new BlkReader($bitcoinDataFolder.'/blocks', true);

// If you omit loading the files in the constructor you can load them with.
// $reader->loadData();

/*
 * while loop.
 */

// Loop all the blk files in the repository 
while ($reader->valid()) {
    
    // echo the filename.
    echo $reader->fileName().PHP_EOL; //  blkXXXXX.dat
    
    $reader->next();
}
$reader->rewind();


/*
 * foreach loop.
 */
foreach ($reader as $i => $file) {
    
    // echo the filename.
    // echo $reader->fileName().PHP_EOL; //  blkXXXXX.dat
    
    // echo the full path and file name.
    echo $reader[$i].PHP_EOL; // /your/full/path/blocks/ blkXXXXX.dat
}
   