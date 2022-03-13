<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use JsonSerializable;

/**
 * A output in a transaction
 */
class Output implements JsonSerializable
{
    /**
     * The monetary value of this output.
     *
     * @var Money
     */
    public readonly Money $value;
    /**
     * The output index.
     *
     * @var int
     */
    public readonly int $n;
    /**
     * The script pub key.
     *
     * @var string
     */
    public readonly string $scriptPubKey;

    /**
     * Creates the new output class object.
     *
     * @param int $value he monetary value of this output in Satoshis.
     * @param int $n The output index.
     * @param string $scriptPubKey The script pub key in hexadecimal.
     */
    public function __construct(int $value, int $n, string $scriptPubKey)
    {
        $this->value = new Money($value, MoneyUnit::Sat);
        $this->n = $n;
        $this->scriptPubKey = $scriptPubKey;
    }

    /**
     * Create an instance of Output class from a simple array
     *
     * @template TValue
     * @param array<string, TValue> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data['value'], $data['n'], $data['script_pub_key']);
    }

    /**
     * JsonSerializable implementation.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return [
            'value'          => $this->value,
            'n'              => $this->n,
            'script_pub_key' => $this->scriptPubKey,
        ];
    }
}
