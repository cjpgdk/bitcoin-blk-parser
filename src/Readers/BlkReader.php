<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk\Readers;

use Cjpg\Bitcoin\Blk\BlockParser;
use Cjpg\Bitcoin\Blk\Utilities;
use Generator;
use RuntimeException;

/**
 * BLK File reader class.
 */
class BlkReader extends Reader
{
    
    /**
     * The full path to the folder that contains the bitcoin blk files.
     * 
     * @var string
     */
    protected string $blkFolder;

    /**
     * The magic bytes that indicate a block start.
     * 
     * @var string Byte string eg. mainnet "\xf9\xbe\xb4\xd9"
     * @see \Cjpg\Bitcoin\Blk\Readers\BlkReader::setMagicBytes($magicBytes)
     */
    protected string $magicBytes = "\xf9\xbe\xb4\xd9";
    
    /**
     * Initialize a new instance of the bitcoin blk reader class.
     * 
     * If the intention is to read all or most of the blk files
     * set $loadFiles to true.
     * 
     * @param string $blkFolder Path to the blk files.
     * @param bool $loadFiles Indicates if the blk files should be loaded doing the construction of the class.
     * @throws \RuntimeException The blk folder do not exists
     */
    public function __construct(string $blkFolder, bool $loadFiles = false)
    {
        $this->rewind();
        
        $this->blkFolder = rtrim($blkFolder, "/\\");

        if(!is_dir($this->blkFolder)) {
            throw new RuntimeException("The blk folder do not exists [{$this->blkFolder}]");
        }
        
        $this->setOffsetGetFormatter(function($blkFile) {
            return $this->blkFolder.DIRECTORY_SEPARATOR.$blkFile;
        });
        
        if($loadFiles) {
            $this->loadData();
        }
    }
    
    /**
     * Gets the blocks from the current blk file.
     * 
     * ```php
     * 
     * use Cjpg\Bitcoin\Blk\Readers\BlkReader;
     * 
     * $blkReader = new BlkReader('/raid10/bitcoin/blocks', true);
     * 
     * foreach($blkReader as $reader) {
     * 
     *     // $reader instance of BlkReader
     *     foreach($blkFile->blocks() as $idx => $block) {
     * 
     *         // $idx is the block number in the blk file.
     *         // INDEX STARTS FROM 0!
     *         // The first blk file 00000.dat contains 119995 blocks.
     * 
     *         // $block is an instance of \Cjpg\Bitcoin\Blk\BlockParser
     *         $block;
     *     }
     * }
     * 
     * ```
     * 
     * @return \Generator<int, \Cjpg\Bitcoin\Blk\BlockParser>
     * @throws \RuntimeException The blk folder do not exists
     */
    public function blocks(): Generator
    {
        // make sure the current file is set!
        // this is a hack for users not using the reader as an array.
        $this->current(); 

        $fStream = Stream::fromFile($this->currentItem);
        if (!$fStream) {
            throw new RuntimeException("Unable read from file [{$this->currentItem}]");
        }
        $idx = 0;
        while($fStream->read(4) == $this->magicBytes) {
            
            $blocksize = Utilities::swapEndian(bin2hex($fStream->read(4)));

            yield $idx++ => new BlockParser(
                $fStream->read($blocksize),
                $blocksize,
                $this->magicBytes
            );
        }

        $fStream->close();
    }
    
    /**
     * Set the magic bytes that indicates a block start.
     * 
     * Note that this must be bytes not a hexadecimal string!
     * 
     * eg. for main net (default) "\xf9\xbe\xb4\xd9".
     * 
     * ###### Known magic values:
     * 
     * | Network | Magic value | setMagicBytes(...) |
     * | :--- | :--- | :--- |
     * | main | 0xD9B4BEF9 | "\xf9\xbe\xb4\xd9" |
     * | testnet/regtest | 0xDAB5BFFA | "\xFA\xBF\xB5\xDA" |
     * | testnet3 | 0x0709110B | "\x0B\x11\x09\x07" |
     * | signet | 0x40CF030A | "\x0A\x03\xCF\x40" |
     * 
     * @param string $magicBytes
     * @return void
     * @link https://en.bitcoin.it/wiki/Protocol_documentation Known magic values
     */
    public function setMagicBytes(string $magicBytes): void
    {
        $this->magicBytes = $magicBytes;
    }
    

    /**
     * Loads the available blk files.
     * 
     * @return static
     */
    public function loadData(): self
    {
        $files = glob($this->blkFolder.DIRECTORY_SEPARATOR."blk*.dat");
        if (!$files) {
            return $this;
        }
        $this->items = array_map(function($item) {
                // This allows input folder to be '/path../' and '/path..'
                return ltrim(
                    str_replace($this->blkFolder, '', $item),
                    DIRECTORY_SEPARATOR
                );
            },
            $files
        );
        return $this;
    }

    /**
     * Gets the file name of the file that we are currently reading from.
     *
     * @return string Filename without path
     */
    public function fileName(): string
    {
        return $this->items[$this->currentPosition];
    }

}
