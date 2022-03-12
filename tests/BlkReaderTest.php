<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\BlockParser;
use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Test\Data;

final class BlkReaderTest extends TestCase
{
    
    
    public function testBlkReaderConstructs(): BlkReader
    {
        $dataDir = __DIR__.'/data/';
        $reader = new BlkReader($dataDir, true);
        
        // $reader->fileName(): no path
        $this->assertEquals('blkmain0.dat', $reader->fileName());
        // $reader[0]: with path
        $this->assertEquals($dataDir.'blkmain0.dat', $reader[0]);
        // $reader->current() == BlkReader::class
        $this->assertInstanceOf(BlkReader::class, $current = $reader->current());
        // $reader->current() === $reader
        $this->assertSame($current, $reader);
        
        return $reader;
    }
    
    /**
     * @depends testBlkReaderConstructs
     */
    public function testBlkReaderBlockLoop(BlkReader $reader): void
    {
        // make sure we are at the begining.
        $reader->rewind();
        
        while ($reader->valid()) {
            
            // loop blocks
            $blockCount = 0;
            foreach ($reader->blocks() as $idx => $block) {
                
                $this->assertSame($blockCount++, $idx);
                
                $this->assertInstanceOf(BlockParser::class, $block);
            }
            $this->assertSame(count(Data::$blocks), $blockCount);
            
            // move to next blk file.
            $reader->next();
        }
    }
    
    /**
     * @depends testBlkReaderConstructs
     */
    public function testBlkReaderBlockLoopSimple(BlkReader $reader): void
    {
        // make sure we are at the begining.
        $reader->rewind();
        
        foreach ($reader as $blkReader) {
            $this->assertInstanceOf(BlkReader::class, $blkReader);
            $this->assertSame($reader, $blkReader);
            
            // loop blocks
            $blockCount = 0;
            foreach ($blkReader->blocks() as $block) {
                ++$blockCount;
                $this->assertInstanceOf(BlockParser::class, $block);
            }
            $this->assertSame(count(Data::$blocks), $blockCount);
        }
    }
    
}

