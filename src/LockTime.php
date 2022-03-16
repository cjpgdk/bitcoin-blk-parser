<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use DateTime;
use RuntimeException;
use JsonSerializable;

/**
 * Transaction LockTime.
 *
 * **LockTime < 500000000** Specifies the block height after which this
 * transaction can be included in a block.
 *
 * **LockTime >= 500000000** pecifies the UNIX timestamp after which this
 * transaction can be included in a block.
 *
 * @link https://github.com/bitcoin/bips/blob/master/bip-0113.mediawiki BIP 113
 * @link https://en.bitcoin.it/wiki/Timelock
 * @link https://en.bitcoin.it/wiki/NLockTime
 */
class LockTime implements JsonSerializable
{
    /**
     * The lock time value
     *
     * @var int
     */
    public readonly int $value;

    /**
     * Creates a new LockTime object.
     *
     * @param int $locktime The lock time value
     */
    public function __construct(int $locktime)
    {
        $this->value = $locktime;
    }

    /**
     * Get the locked until height.
     *
     * @return int
     * @throws \RuntimeException
     */
    public function height(): int
    {
        if (!$this->isHeightLock()) {
            throw new RuntimeException("This LockTime object is based on time not height");
        }
        return $this->value;
    }

    /**
     * Get the locked until date.
     *
     * @return \DateTime|bool **false** on failure.
     * @throws \RuntimeException
     */
    public function date(): DateTime|bool
    {
        if (!$this->isTimeLock()) {
            throw new RuntimeException("This LockTime object is based on height not time");
        }
        return (new DateTime())->setTimestamp($this->value);
    }

    /**
     * Check if the lock time depends on block height.
     *
     * @return bool
     */
    public function isHeightLock(): bool
    {
        return $this->value < 500000000;
    }

    /**
     * Check if the lock time depends on timestamp.
     *
     * @return bool
     */
    public function isTimeLock(): bool
    {
        return $this->value >= 500000000;
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
