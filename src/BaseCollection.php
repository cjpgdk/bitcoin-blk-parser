<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \Iterator<TKey, TValue>
 *
 * @internal For internal use, a will be coded to fit what is needed in this project.
 */
abstract class BaseCollection implements ArrayAccess, Countable, JsonSerializable, Iterator
{
    /**
     * The items in this collection.
     *
     * @var array<TKey, TValue>
     */
    protected array $items = [];

    /**
     * The keys in this collection.
     *
     * Used for Iterator to move to next using mixed values for keys.
     *
     * @var array<int, TKey>
     */
    protected array $keys = [];

    /**
     * The current position of the iterator.
     *
     * @var int
     */
    protected int $position;

    /**
     * @param array<TKey, TValue> $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $items;
        $this->keys = array_keys($this->items);
        $this->rewind();
    }

    abstract public function jsonSerialize(): mixed;

    /**
     * Get all the items.
     *
     * @return array<TKey, TValue>
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Add an item.
     *
     * @param TValue $item
     * @param ?TKey $key
     * @return static
     */
    public function add($item, $key = null): self
    {
        if (!is_null($key)) {
            $this->items[$key] = $item;
        } else {
            $this->items[] = $item;
        }
        $this->keys = array_keys($this->items);

        return $this;
    }

    /**
     * Get an item by {@param $key}.
     *
     * @param TKey $key
     * @return TValue|null
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }
        return null;
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Count the number of items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if an offset($key) exists.
     *
     * @param TKey $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get the item at offset($key).
     *
     * @param TKey $key
     * @return TValue
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set/Add an item.
     *
     * @param TKey|null $key
     * @param TValue $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
        $this->keys = array_keys($this->items);
    }

    /**
     * Unset an item from the list.
     *
     * @param  TKey  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Get the current element.
     *
     * @return TValue
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->offsetGet(
            $this->keys[$this->position]
        );
    }

    /**
     * Get the key of the current element.
     *
     * @return TKey
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->keys[$this->position];
    }

    /**
     * Move forward to next element.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Rewind the iterator to the first element.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Checks if current iterator position is valid.
     */
    public function valid(): bool
    {
        if (!array_key_exists($this->position, $this->keys)) {
            return false;
        }
        return $this->offsetExists(
            $this->keys[$this->position]
        );
    }
}
