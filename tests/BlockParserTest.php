<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\BlockParser;
use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Test\Data;

final class BlockParserTest extends TestCase
{
    public function testConstructBlkReader(): BlkReader
    {
        $dataDir = __DIR__ . '/data/';
        $reader = new BlkReader($dataDir, true);
        // needed to make phpunit happy! :(.... :)
        $this->assertInstanceOf(BlkReader::class, $reader);
        return $reader;
    }


    /**
     * @depends testConstructBlkReader
     */
    public function testBlockParser(BlkReader $reader): void
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

            // timestamp
            $this->assertSame(Data::$blocks[$idx]['ts'], $block->timestamp());

            // merkleRoot
            $this->assertSame(Data::$blocks[$idx]['merkle_root'], $block->merkleRoot());

            // prevBlock
            $this->assertSame(Data::$blocks[$idx]['prev_block'], $block->previousBlock());

            // version
            $this->assertSame(Data::$blocks[$idx]['version'], $block->version());

            // versionHex
            $this->assertSame(Data::$blocks[$idx]['versionHex'], $block->versionHex());

            // nonce
            $this->assertSame(Data::$blocks[$idx]['nonce'], $block->nonce());

            // bits
            $this->assertSame(Data::$blocks[$idx]['bits'], $block->bits());

            // bitsHex
            $this->assertSame(Data::$blocks[$idx]['bitsHex'], $block->bitsHex());

            // size
            $this->assertSame(Data::$blocks[$idx]['size'], $block->size());

            // difficulty
            $this->assertSame(Data::$blocks[$idx]['difficulty'], (int)$block->difficulty());

            // strippedsize
            $this->assertSame(Data::$blocks[$idx]['strippedsize'], $block->strippedSize(), "strippedsize error, HASH: {$block->blockHash()}");

            // weight
            $this->assertSame(Data::$blocks[$idx]['weight'], $block->weight());
        }

        // check that we got all the expected blocks.
        $this->assertSame(count(Data::$blocks), $blockCount);
    }
}
