<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk\Readers;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Base of the reader classes.
 * @implements \ArrayAccess<int, mixed>
 * @implements \Iterator<static>
 */
abstract class Reader implements Countable, ArrayAccess, Iterator
{

    /**
     * The current item in use by the iterator.
     * @var mixed
     */
    protected mixed $currentItem;

    /**
     * The current position of the iterator.
     * 
     * @var int
     */
    protected int $currentPosition;

    /**
     * The items from which the reader can read.
     * 
     * @var array<mixed>
     */
    protected array $items;

    /**
     * A custom function to format the items in the list.
     * 
     * This is used to append/pre-pend or change the output
     * of the items from offsetGet($key) and current().
     * 
     * @var callable|null
     * @see \Cjpg\Bitcoin\Blk\Readers\Reader::setOffsetGetFormatter($callable)
     */
    protected $offsetGetFormatter;
    
    /**
     * Loads the data into the reader.
     * 
     * @return static
     */
    abstract public function loadData(): self;

    /**
     * Set the item output formatter.
     * 
     * ```php
     * $filesPath = "/home/nisse/storeage";
     * 
     * $readerObj = new CustomReader();
     * 
     * $readerObj->setOffsetGetFormatter(function($item) use ($filesPath){
     *     return $filesPath.DIRECTORY_SEPARATOR.$item;
     * });
     * 
     * $readerObj->offsetSet(0, 'file0.txt');
     * 
     * $file0 = $readerObj->current();
     * // echo $file0; // == /home/nisse/storeage/file0.txt
     * 
     * ```
     * 
     * @param callable $callable
     * @return static
     */
    public function setOffsetGetFormatter(callable $callable)
    {
        $this->offsetGetFormatter = $callable;
        return $this;
    }

    /**
     * Get the number of items in this reader.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get the current element.
     * 
     * @return static
     */
    public function current(): self
    {
        $this->currentItem = $this->offsetGet($this->currentPosition);
        return $this;
    }

    /**
     * Get the key of the current element.
     * 
     * @return int
     */
    public function key(): int
    {
        return $this->currentPosition;
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        ++$this->currentPosition;
    }

    /**
     * Rewind the iterator to the first element.
     */
    public function rewind(): void
    {
        $this->currentPosition = 0;
    }

    /**
     * Checks if current iterator position is valid.
     */
    public function valid(): bool
    {
        return $this->offsetExists($this->currentPosition);
    }

    /**
     * Check if an offset($key) exists.
     * 
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get the item at offset($key).
     * 
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        if ($formatter = $this->offsetGetFormatter) {
            return $formatter($this->items[$key]);
        }
        return $this->items[$key];
    }

    /**
     * Set/Add an item.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Unset an item from the list.
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

}
