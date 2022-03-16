<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use JsonSerializable;

/**
 * class Money
 */
class Money implements JsonSerializable
{
    private int $value;

    /**
     * Creates e new Money object from {@param $value} and {@param $unit}
     *
     * @param int $value
     * @param MoneyUnit $unit The unit that the {@param $value} is in.
     */
    public function __construct(int $value, MoneyUnit $unit = MoneyUnit::Sat)
    {
        $this->value = $value * $unit->value;
    }

    /**
     * Format the value in {@param $unit}
     *
     * *Note that the return value may be a scientific notation 1 Sat = 1.0E-6 Cent*
     *
     * @param MoneyUnit $unit
     * @return int|float
     */
    public function format(MoneyUnit $unit = MoneyUnit::Sat): int|float
    {
        return $this->value / $unit->value;
    }
    
    /**
     * JsonSerializable implementation.
     *
     * @return int
     */
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    /**
     * Gets the value as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
