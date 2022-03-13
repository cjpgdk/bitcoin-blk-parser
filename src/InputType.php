<?php
// @codingStandardsIgnoreFile

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * InputType.
 */
enum InputType: int
{
    case COINBASE = 0;
    case SCRIPT = 1;
}
