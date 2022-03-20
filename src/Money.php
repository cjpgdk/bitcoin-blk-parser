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
     * Add $value to the current.
     *
     * *$value must be in satoshi or a Money object*
     *
     * @param Money|int $value The number of satoshi to add, or a Money object
     * @return static
     */
    public function add(int|self $value)
    {
        $this->value += $this->getSatoshi($value);
        return $this;
    }

    /**
     * Subtract $value from the current.
     *
     * *$value must be in satoshi or a Money object*
     *
     * @param Money|int $value The number of satoshi to subtract, or a Money object
     * @return static
     */
    public function sub(int|self $value)
    {
        $this->value -= $this->getSatoshi($value);
        return $this;
    }

    /**
     * Multiply the current value with $num.
     *
     * @param Money|int $num The multiplier
     * @return static
     */
    public function mul(int|self $num)
    {
        $this->value *= $this->getSatoshi($num);
        return $this;
    }

    /**
     * Divide the current value with $num.
     *
     * *Note that the resulting value will be rounded using PHP round method
     * and then cast to int.*
     *
     * @param Money|int $num The multiplier
     * @param int $mode Use one of the PHP round constants PHP_ROUND_HALF_UP,
     * PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD or PHP_ROUND_HALF_UP.
     * @return static
     */
    public function div(int|self $num, int $mode = PHP_ROUND_HALF_UP)
    {
        $precision = 0;
        $this->value = (int)round(
            $this->value / $this->getSatoshi($num),
            $precision,
            $mode
        );
        return $this;
    }

    /**
     * Ensure value is an int
     * @param int|self $num
     * @return int
     */
    private function getSatoshi(int|self $num): int
    {
        if ($num instanceof static) {
            $num = $num->format(MoneyUnit::Sat);
        }
        /** @phpstan-ignore-next-line */
        return $num;
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
