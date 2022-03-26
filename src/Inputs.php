<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * The Inputs collection class.
 *
 * @extends \Cjpg\Bitcoin\Blk\BaseCollection<int, \Cjpg\Bitcoin\Blk\Input>
 */
class Inputs extends BaseCollection
{
    /**
     * Creates a new inputs collection from a simple array like structure
     *
     * @param array<int, \Cjpg\Bitcoin\Blk\Input|array<string, mixed>> $inputs
     * @return void
     */
    public function __construct(array $inputs = [])
    {
        // map the inputs to an instance of class Input.
        $this->items = array_map(function ($item) {
            if (!$item instanceof Input) {
                return Input::fromArray($item);
            }
            return $item;
        }, $inputs);
        $this->keys = array_keys($this->items);
        $this->rewind();
    }

    /**
     * JsonSerializable implementation.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->all();
    }
}
