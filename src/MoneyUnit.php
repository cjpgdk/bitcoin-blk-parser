<?php
// @codingStandardsIgnoreFile

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * MoneyUnit
 */
enum MoneyUnit: int
{
    /**
     * 1 BTC (1 coin)
     */
    case BTC = 100_000_000;
    /**
     * 1 bitcent cBTC (0.01 BTC)
     */
    case Cent = 1_000_000;
    /**
     * 1 millibit mBTC (0.001 BTC)
     */
    case MilliBTC = 100_000;
    /**
     * 1 bit μBTC (0.000001 BTC)
     */
    case Bit = 100;
    /**
     * 1 satoshi (0.00000001 BTC) 
     */
    case Sat = 1;
}
