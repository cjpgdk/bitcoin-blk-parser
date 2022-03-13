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
     * @param array<int, \Cjpg\Bitcoin\Blk\Input> $inputs
     * @return void
     */
    public function __construct(array $inputs = [])
    {
        parent::__construct($inputs);

        // map the inputs to an instance of class Input.
        $this->items = array_map(function ($item) {
            if (!$item instanceof Input) {
                return Input::fromArray($item);
            }
            return $item;
        }, $this->items);
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
