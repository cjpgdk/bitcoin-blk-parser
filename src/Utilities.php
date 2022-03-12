<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * Helper Utilities
 */
class Utilities
{
    /**
     * Run sha256 hash on $data twice
     *
     * @param string $data
     * @param bool $raw If true return the raw binary data.
     * @return string
     * @uses \Cjpg\Bitcoin\Blk\Utilities::sha256
     */
    public static function hash256(string $data, bool $raw = false): string
    {
        return static::sha256(static::sha256($data, true), $raw);
    }

    /**
     * Run sha256 hash on $data
     *
     * @param string $data
     * @param bool $raw If true return the raw binary data.
     * @return string
     * @uses \hash
     */
    public static function sha256(string $data, bool $raw = false): string
    {
        return hash('sha256', $data, $raw);
    }

    /**
     * Swap endianness of an hexadecimal string.
     *
     * @param string $hex
     * @param bool $hexdec [Default true] convert from hex to decimal.
     * @return int|float|string
     */
    public static function swapEndian(string $hex, bool $hexdec = true): int|float|string
    {
        if (strlen($hex) <= 2) {
            return $hexdec ? hexdec($hex) : $hex;
        }
        $u = unpack("H*", strrev(pack("H*", $hex)));
        if (!$u) {
            return $hexdec ? 0 : '';
        }
        return $hexdec ? hexdec($u[1]) : $u[1];
    }
}
