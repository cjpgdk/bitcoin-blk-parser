<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk\Readers;

use Exception;
use Cjpg\Bitcoin\Blk\BaseCollection;

/**
 * Base of the reader classes.
 *
 * @extends \Cjpg\Bitcoin\Blk\BaseCollection<int, string>
 */
abstract class Reader extends BaseCollection
{
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
     * Get the item at offset($key).
     *
     * @param int $key
     * @return string|null
     */
    public function offsetGet($key): mixed
    {
        $value = parent::offsetGet($key);

        if ($formatter = $this->offsetGetFormatter) {
            return $formatter($value);
        }
        return $value;
    }

    /**
     * Move to {@param $offset}.
     *
     * @param int $offset
     * @return static
     */
    public function moveTo(int $offset): self
    {
        $this->position = $offset;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        throw new Exception("The reader do not support jsonSerialize");
    }
}
