<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Cjpg\Bitcoin\Blk\Money;
use Cjpg\Bitcoin\Blk\MoneyUnit;

final class MoneyTest extends TestCase
{
    public function testMoneySatoshi(): void
    {
        $oneSatoshi = new Money(1, MoneyUnit::Sat);

        // format default MoneyUnit::Sat
        $this->assertSame(1, $oneSatoshi->format(MoneyUnit::Sat));
        $this->assertSame(1, $oneSatoshi->format());
        $this->assertSame($oneSatoshi->format(MoneyUnit::Sat), $oneSatoshi->format());

        $this->assertSame(0.01, $oneSatoshi->format(MoneyUnit::Bit));

        $this->assertSame(0.00001, $oneSatoshi->format(MoneyUnit::MilliBTC));

        $this->assertSame(0.000001, $oneSatoshi->format(MoneyUnit::Cent));

        $this->assertSame(0.00000001, $oneSatoshi->format(MoneyUnit::BTC));
    }

    public function testMoneyBTC(): void
    {
        $oneSatoshi = new Money(1, MoneyUnit::BTC);

        // format default MoneyUnit::Sat
        $this->assertSame(100000000, $oneSatoshi->format(MoneyUnit::Sat));
        $this->assertSame(100000000, $oneSatoshi->format());
        $this->assertSame($oneSatoshi->format(MoneyUnit::Sat), $oneSatoshi->format());

        $this->assertSame(1, $oneSatoshi->format(MoneyUnit::BTC));

        $this->assertSame(100, $oneSatoshi->format(MoneyUnit::Cent));

        $this->assertSame(1000, $oneSatoshi->format(MoneyUnit::MilliBTC));

        $this->assertSame(1000000, $oneSatoshi->format(MoneyUnit::Bit));
    }
}
