<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * The Outputs collection class.
 *
 * @extends \Cjpg\Bitcoin\Blk\BaseCollection<int, \Cjpg\Bitcoin\Blk\Output>
 */
class Outputs extends BaseCollection
{
    /**
     * Creates a new outputs collection from a simple array like structure
     *
     * @param array<int, \Cjpg\Bitcoin\Blk\Output> $inputs
     * @return void
     */
    public function __construct(array $inputs = [])
    {
        parent::__construct($inputs);

        // map the outputs to an instance of class Output.
        $this->items = array_map(function ($item) {
            if (!$item instanceof Output) {
                return Output::fromArray($item);
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
