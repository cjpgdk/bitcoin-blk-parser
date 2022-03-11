<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use Cjpg\Bitcoin\Blk\Readers\Stream;

/**
 * BlockParser reads the raw block data extracted from blk files.
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
        if ($resource = fopen('php://memory', 'r+')) {
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
     * Get the transaction count.
     * 
     * @return int
     */
    public function transactionCount(): int
    {
        $this->block->seek(80);
        return $this->block->getVarInt();
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
            Utilities::hash256($this->block->read(80), false), 
            false
        );
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
