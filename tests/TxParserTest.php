<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\Readers\BlkReader;
use Cjpg\Bitcoin\Blk\InputType;
use Cjpg\Bitcoin\Blk\MoneyUnit;
use Cjpg\Bitcoin\Blk\TxParser;
use Test\Data;

final class TxParserTest extends TestCase
{
    public function testConstructBlkReader(): BlkReader
    {
        $dataDir = __DIR__ . '/data/';
        $reader = new BlkReader($dataDir, true);
        // needed to make phpunit happy! :(.... :)
        $this->assertInstanceOf(BlkReader::class, $reader);
        return $reader;
    }

    public function testTxParserFromHex(): void
    {
        $tx = TxParser::fromHex(Data::$txHex[0]['hex']);
        
        $this->assertSame(Data::$txHex[0]['hex'], $tx->hex);
        $this->assertSame(Data::$txHex[0]['txid'], $tx->txid);
        $this->assertSame(Data::$txHex[0]['hash'], $tx->hash);
        $this->assertSame(Data::$txHex[0]['version'], $tx->version);
        $this->assertSame(Data::$txHex[0]['size'], $tx->size);
        $this->assertSame(Data::$txHex[0]['vsize'], $tx->vsize);
        $this->assertSame(Data::$txHex[0]['weight'], $tx->weight);
        $this->assertSame(Data::$txHex[0]['locktime'], $tx->locktime->value);

        // inputCount
        $this->assertCount($tx->inputCount, Data::$txHex[0]['vin']);

        foreach (Data::$txHex[0]['vin'] as $i => $vin) {
            $in = $tx->inputs->get($i);

            if (isset($vin['coinbase'])) {

                $this->assertTrue($in->isCoinbase());
                $this->assertSame($vin['coinbase'], $in->scriptSig);
                $this->assertSame($vin['sequence'], $in->sequence);

            } else {

                $this->assertFalse($in->isCoinbase());
                $this->assertSame($vin['txid'], $in->txid);
                $this->assertSame($vin['vout'], $in->vout);
                $this->assertSame($vin['scriptSig']['hex'], $in->scriptSig);
                $this->assertSame($vin['sequence'], $in->sequence);

            }
        }

        // outputCount
        $this->assertCount($tx->outputCount, Data::$txHex[0]['vout']);
        
        foreach (Data::$txHex[0]['vout'] as $i => $vout) {
            $out = $tx->outputs->get($i);
            
            $this->assertSame($vout['value'], (float)$out->value->format(MoneyUnit::BTC));
            $this->assertSame($vout['n'], $out->n);
            $this->assertSame($vout['scriptPubKey']['hex'], $out->scriptPubKey);
        }
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
            return;/** @phpstan-ignore-line */
        }

        $dataDir = __DIR__ . '/data/';

        // make sure we are at the begining.
        $reader->rewind();

        foreach ($reader->blocks() as $idx => $block) {
            $blockFile = $dataDir . $block->blockHash() . '.block';
            if (!file_exists($blockFile)) {
                $this->markTestIncomplete(
                    "TX Data test missing block data [{$block->blockHash()}]"
                );
                continue;/** @phpstan-ignore-line */
            }

            /** @phpstan-ignore-next-line */
            $blockData = json_decode(file_get_contents($blockFile), false);

            foreach ($block->transactions() as $i => $tx) {
                $blockTx = $blockData->tx[$i];

                // txid
                $this->assertSame(
                    $blockTx->txid,
                    $tx->txid,
                    "txid failed: {$block->blockHash()}"
                );

                // hash
                $this->assertSame(
                    $blockTx->hash,
                    $tx->hash,
                    "hash failed: {$block->blockHash()}:{$tx->txid}"
                );

                // version
                $this->assertSame(
                    $blockTx->version,
                    $tx->version,
                    "version failed: {$block->blockHash()}:{$tx->txid}"
                );

                // size
                $this->assertSame(
                    $blockTx->size,
                    $tx->size,
                    "size failed: {$block->blockHash()}:{$tx->txid}"
                );

                // weight
                $this->assertSame(
                    $blockTx->weight,
                    $tx->weight,
                    "weight failed: {$block->blockHash()}:{$tx->txid}"
                );

                // locktime
                $this->assertSame(
                    $blockTx->locktime,
                    $tx->locktime->value,
                    "locktime failed: {$block->blockHash()}:{$tx->txid}"
                );

                // inputCount
                $this->assertCount(
                    $tx->inputCount,
                    $blockTx->vin,
                    "inputCount failed: {$block->blockHash()}:{$tx->txid}"
                );
                $this->assertCount(
                    $tx->inputCount,
                    $tx->inputs,
                    "inputCount2 failed: {$block->blockHash()}:{$tx->txid}"
                );

                // outputCount
                $this->assertCount(
                    $tx->outputCount,
                    $blockTx->vout,
                    "outputCount failed: {$block->blockHash()}:{$tx->txid}"
                );
                $this->assertCount(
                    $tx->outputCount,
                    $tx->outputs,
                    "outputCount2 failed: {$block->blockHash()}:{$tx->txid}"
                );

                // segwit
                if ($tx->size != $tx->vsize) {
                    $this->assertTrue(
                        $tx->segwit,
                        "Segwit failed: {$block->blockHash()}:{$tx->txid}"
                    );
                } else {
                    $this->assertFalse(
                        $tx->segwit,
                        "Segwit failed: {$block->blockHash()}:{$tx->txid}"
                    );
                }

                // hex
                $this->assertSame(
                    $blockTx->hex,
                    $tx->hex,
                    "hex failed: {$block->blockHash()}:{$tx->txid}"
                );

                // inputs
                foreach ($tx->inputs as $vi => $vin) {
                    if ($vin->isCoinbase()) {
                        $this->assertSame($blockTx->vin[$vi]->coinbase, $vin->scriptSig);
                    } else {
                        $this->assertSame($blockTx->vin[$vi]->txid, $vin->txid);
                        $this->assertSame($blockTx->vin[$vi]->vout, $vin->vout);
                        $this->assertSame($blockTx->vin[$vi]->scriptSig->hex, $vin->scriptSig);
                    }

                    $this->assertSame($blockTx->vin[$vi]->sequence, $vin->sequence);

                    if ($vin->witness) {
                        $this->assertSame($blockTx->vin[$vi]->txinwitness, $vin->witness);
                    }
                }

                // outputs
                foreach ($tx->outputs as $vo => $vout) {
                    //                                          // cast to float as json output is always float. :(
                    $this->assertSame($blockTx->vout[$vo]->value, (float)$vout->value->format(MoneyUnit::BTC));
                    $this->assertSame($blockTx->vout[$vo]->n, $vout->n);
                    $this->assertSame($blockTx->vout[$vo]->scriptPubKey->hex, $vout->scriptPubKey);
                }
            }
        }
    }
}
