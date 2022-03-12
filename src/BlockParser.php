<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use Cjpg\Bitcoin\Blk\Readers\Stream;
use Generator;

/**
 * BlockParser reads the raw block data extracted from blk files.
 * 
 * **NOTE: These requires knowledge of more than just one block in the chain, this is not a db just a reader/parser.**
 * 
 * 1. height (`calculated based on knowing the prev block hash back to genesis.`)
 * 2. confirmations (`total blocks - height`)
 * 3. median time (`MEDIAN([time, of, past, 11, blocks])`)
 * 4. chain work (`requires knowledge of the prev blocks chain work`)
 * 5. next block hash (`it's in the name we need the next block in the chain to tell us`)
 */
class BlockParser
{
    /**
     * The block bytes we read from.
     * 
     * @var Stream|null
     */
    protected ?Stream $block;

    /**
     * The magic bytes that indicates the block start and type.
     * 
     * @var string|null
     */
    protected ?string $magicBytes;

    /**
     * The block size.
     * 
     * @var int|null
     */
    protected ?int $size;

    /**
     * Initialize a new instance of the bitcoin block parser class.
     * 
     * @param string $block The block bytes (binary string!).
     * @param int|null $size [Optional] The block size.
     * @param string|null $magicBytes [Optional] The magic bytes for this block (binary string!).
     */
    public function __construct(string $block, ?int $size = null, ?string $magicBytes = null)
    {
        $this->block = null;
        if ($resource = fopen('php://memory', 'rwb')) {
            fwrite($resource, $block);
            rewind($resource);
            $this->block = new Stream($resource);
        } else {
            throw new RuntimeException('Unable to load block data into memory');
        }
        
        $this->magicBytes = $magicBytes;
        $this->size = $size ?: strlen($block);
    }

    /**
     * 
     * @return \Generator<BlockTransactionReader>
     */
    public function transactions(): Generator
    {
        $txCount = $this->transactionCount();
        
        for($i = 0; $i < $txCount; ++$i) {
            yield $i => TxParser::fromStream($this->block);
        }
    }
    
    /**
     * The block weight.
     * 
     * @return int
     * @link https://github.com/bitcoin/bips/blob/master/bip-0141.mediawiki BIP 141
     */
    public function weight(): int
    {
        return $this->strippedSize() * 3 + $this->size();
    }
    
    /**
     * Check if the block contains any segregated witness transactions.
     * 
     * @return bool
     */
    public function hasSegwit(): bool
    {
        foreach ($this->transactions() as $tx) {
            if ($tx->segwit) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get the stripped size of the block, that is the size of the block
     * without witness data.
     * 
     * @return int
     */
    public function strippedSize(): int
    {
        $txSize = 0;
        $txVSize = 0;
        foreach ($this->transactions() as $tx) {
            $txSize+= $tx->size;
            $txVSize += $tx->vsize;
        }
        return $this->size() - ($txSize - $txVSize);
    }
    
    /**
     * Get the block difficulty.
     * 
     * @return string|float decimal value that may be a string or float.
     */
    public function difficulty(): int|float
    {        
        $bits = $this->bits();
        // src/rpc/blockchain.cpp
        $nShift = ($bits >> 24) & 0xff;
        $dDiff = (float)0x0000ffff / (float)($bits & 0x00ffffff);
        while ($nShift < 29)
        {
            $dDiff *= 256.0;
            $nShift++;
        }
        while ($nShift > 29)
        {
            $dDiff /= 256.0;
            $nShift--;
        }
        return $dDiff;
    }

    /**
     * Get the transaction count.
     * 
     * @return int
     */
    public function transactionCount(): int
    {
        $this->block->seek(80);
        return $this->block->readVarInt();
    }

    /**
     * Gets the block hash
     *
     * @return string
     */
    public function blockHash(): string
    {
        $this->block->seek(0);
        return Utilities::swapEndian(
            Utilities::hash256($this->block->read(80), false), false
        );
    }

    /**
     * Get the block nonce.
     *
     * @return int
     */
    public function nonce(): int
    {
        $this->block->seek(76);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), true);
    }

    /**
     * Get the block bits.
     *
     * @return int
     */
    public function bits(): int
    {
        $this->block->seek(72);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), true);
    }

    /**
     * Get the block bits as hexadecimal.
     *
     * @return string
     */
    public function bitsHex(): string
    {
        $this->block->seek(72);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), false);
    }

    /**
     * Gets the block timestamp.
     *
     * @return int
     */
    public function timestamp(): int
    {
        $this->block->seek(68);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), true);
    }

    /**
     * Gets the merkle root of the block.
     *
     * @return string
     */
    public function merkleRoot(): string
    {
        $this->block->seek(36);
        return Utilities::swapEndian(bin2hex($this->block->read(32)), false);
    }

    /**
     * Gets the previous block hash.
     *
     * @return string
     */
    public function previousBlock(): string
    {
        $this->block->seek(4);
        return Utilities::swapEndian(bin2hex($this->block->read(32)), false);
    }

    /**
     * Gets the block version.
     *
     * @return int
     */
    public function version(): int
    {
        $this->block->seek(0);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), true);
    }

    /**
     * Gets the block version as hexadecimal.
     *
     * @return string
     */
    public function versionHex(): string
    {
        $this->block->seek(0);
        return Utilities::swapEndian(bin2hex($this->block->read(4)), false);
    }
    
    /**
     * Gets the raw block.
     * 
     * *Note that magic bytes and size variable are striped from the block.*
     *
     * @param bool $hex get the block as hex string or binary string.
     * @return string
     */
    public function block(bool $hex = false): string
    {
        $data = $this->block->getContents();
        return $hex ? bin2hex($data) : $data;
    }

    /**
     * Gets the size of the block in bytes.
     * 
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Gets the magic bytes.
     *
     * @return string Binary string.
     */
    public function magicBytes(): string
    {
        return $this->magicBytes;
    }
    
    /**
     * Ends the reader and releases the underlying memory stream.
     */
    public function end()
    {
        if ($this->block) {
            $this->block->close();
        }
        
        $this->block = $this->size = $this->magicBytes = null;
    }
    
    
    /**
     * Closes the stream.
     *
     * @link https://www.php.net/manual/en/language.oop5.decon.php#object.destruct
     * @see \Cjpg\Bitcoin\Blk\BlockParser::end()
     */
    public function __destruct()
    {
        $this->end();
    }
}
