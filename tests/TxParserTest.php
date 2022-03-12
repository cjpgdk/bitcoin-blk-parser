<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Test\Data;

final class TxParserTest extends TestCase
{
    public function testConstructBlkReader(): BlkReader
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
    public function testTxParser(BlkReader $reader): void
    {
        if (!Data::$compareTxData) {
            $this->markTestSkipped(
                "TX Data test are not enabled 'Data::\$compareTxData'"
            );
            return; /** @phpstan-ignore-line */
        }
        
        $dataDir = __DIR__.'/data/';
        
        // make sure we are at the begining.
        $reader->rewind();
        
        foreach ($reader->blocks() as $idx => $block) {
            
            $blockFile = $dataDir.$block->blockHash().'.block';
            if (!file_exists($blockFile)) {
                $this->markTestIncomplete(
                    "TX Data test missing block data [{$block->blockHash()}]"
                );
                continue; /** @phpstan-ignore-line */
            }
            
            /** @phpstan-ignore-next-line */
            $blockData = json_decode(file_get_contents($blockFile), false);
            
            foreach ($block->transactions() as $i => $tx) {
                $blockTx = $blockData->tx[$i];
                
                // txid
                $this->assertSame($blockTx->txid, $tx->txid, "txid failed: {$block->blockHash()}");
                
                // hash
                $this->assertSame($blockTx->hash, $tx->hash, "hash failed: {$block->blockHash()}:{$tx->txid}");
                
                // version
                $this->assertSame($blockTx->version, $tx->version, "version failed: {$block->blockHash()}:{$tx->txid}");
                
                // size
                $this->assertSame($blockTx->size, $tx->size, "size failed: {$block->blockHash()}:{$tx->txid}");
                
                // weight
                $this->assertSame($blockTx->weight, $tx->weight, "weight failed: {$block->blockHash()}:{$tx->txid}");
                
                // locktime
                $this->assertSame($blockTx->locktime, $tx->locktime, "locktime failed: {$block->blockHash()}:{$tx->txid}");
                
                // inputCount
                $this->assertCount($tx->inputCount, $blockTx->vin, "inputCount failed: {$block->blockHash()}:{$tx->txid}");
                $this->assertCount($tx->inputCount, $tx->inputs, "inputCount2 failed: {$block->blockHash()}:{$tx->txid}");
                
                // outputCount
                $this->assertCount($tx->outputCount, $blockTx->vout, "outputCount failed: {$block->blockHash()}:{$tx->txid}");
                $this->assertCount($tx->outputCount, $tx->outputs, "outputCount2 failed: {$block->blockHash()}:{$tx->txid}");
                
                // segwit
                if($tx->size != $tx->vsize) {
                    $this->assertTrue($tx->segwit, "Segwit failed: {$block->blockHash()}:{$tx->txid}");
                } else {
                    $this->assertFalse($tx->segwit, "Segwit failed: {$block->blockHash()}:{$tx->txid}");
                }
                
                // hex
                $this->assertSame($blockTx->hex, $tx->hex, "hex failed: {$block->blockHash()}:{$tx->txid}");
                
                // inputs
                foreach ($tx->inputs as $vi => $vin) {
                    if (isset($vin['coinbase'])) {
                        $this->assertSame($blockTx->vin[$vi]->coinbase, $vin['coinbase']);
                        
                    } else {
                        $this->assertSame($blockTx->vin[$vi]->txid, $vin['txid']);
                        $this->assertSame($blockTx->vin[$vi]->vout, $vin['vout']);
                        $this->assertSame($blockTx->vin[$vi]->scriptSig->hex, $vin['script_sig']);
                        
                    }
                    
                    $this->assertSame($blockTx->vin[$vi]->sequence, $vin['sequence']);
                    
                    if (isset($vin['witness'])) {
                        $this->assertSame($blockTx->vin[$vi]->txinwitness, $vin['witness']);
                    }
                    
                }
                
                // outputs
                foreach ($tx->outputs as $vo => $vout) {
                    $this->assertSame($blockTx->vout[$vo]->value, ($vout['value']/100000000.0));
                    $this->assertSame($blockTx->vout[$vo]->n, $vout['n']);
                    $this->assertSame($blockTx->vout[$vo]->scriptPubKey->hex, $vout['script_pub_key']);
                }
                
            }
        }
        
    }
}
