<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\BlockParser;
use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Test\Data;

final class BlockParserTest extends TestCase
{
    
    public function testConstructBlkReader()
    {
        $dataDir = __DIR__.'/data/';
        $reader = new BlkReader($dataDir, true);
        // needed to make phpunit happy! :(.... :)
        $this->assertInstanceOf(BlkReader::class, $reader);
        return $reader;
    }
    
    
    /**
     * @depends testConstructBlkReader
     */
    public function testBlockParser(BlkReader $reader)
    {
        // make sure we are at the begining.
        $reader->rewind();
        
        // we only have one blk file for the tests so no
        // need to loop other than blocks.
        $blockCount = 0;
        foreach ($reader->blocks() as $idx => $block) {
            // id must id correct.
            $this->assertSame($blockCount++, $idx);
            // the block must be BlockParser object.
            $this->assertInstanceOf(BlockParser::class, $block);
            
            // blockHash
            $this->assertSame(Data::$blocks[$idx]['hash'], $block->blockHash());
            
            // transactionCount
            $this->assertSame(Data::$blocks[$idx]['ntx'], $block->transactionCount());
        }
        
        // check that we got all the expected blocks.
        $this->assertSame(count(Data::$blocks), $blockCount);
    }
}
