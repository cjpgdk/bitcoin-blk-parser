<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

/**
 * An input in a transaction
 */
class Input
{
    /**
     * The input type Coinbase or Script.
     *
     * @var InputType
     */
    public readonly InputType $type;

    /**
     * The transactions id of the spending transaction.
     *
     * @var string
     */
    public readonly string $txid;

    /**
     * The output index of the spending transaction.
     *
     * @var int
     */
    public readonly int $vout;

    /**
     * The input script signature.
     *
     * @var string
     */
    public readonly string $scriptSig;

    /**
     * The input sequence.
     *
     * @var int
     */
    public readonly int $sequence;

    /**
     * The input witnesses if any.
     *
     * @var array<int, string>|null
     */
    public readonly ?array $witness;

    /**
     *
     * @param InputType $type
     * @param string $txid
     * @param string $scriptSig
     * @param int $vout
     * @param int $sequence
     * @param array<int, string>|null $witness
     */
    public function __construct(
        InputType $type,
        string $txid,
        string $scriptSig,
        int $vout = 4294967295,
        int $sequence = 4294967295,
        ?array $witness = null
    ) {
        $this->type = $type;
        $this->txid = $txid;
        $this->vout = $vout;
        $this->scriptSig = $scriptSig;
        $this->sequence = $sequence;
        $this->witness = $witness;
    }

    /**
     * Create an instance of Input class from a simple array
     *
     * @template TValue
     * @param array<string, TValue> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $type = array_key_exists('coinbase', $data) ? InputType::COINBASE : InputType::SCRIPT;
        $txid = array_key_exists('txid', $data) ? $data['txid'] : str_repeat('0', 64);
        $vout = array_key_exists('vout', $data) ? (int) $data['vout'] : 4294967295;
        $scriptSig = array_key_exists('script_sig', $data) ? $data['script_sig'] : null;
        if (is_null($scriptSig) && $type == InputType::COINBASE) {
            $scriptSig = $data['coinbase'];
        }
        $sequence = array_key_exists('sequence', $data) ? (int) $data['sequence'] : 4294967295;
        $witness = array_key_exists('witness', $data) ? $data['witness'] : null;
        return new self(
            $type,
            $txid,
            /** @phpstan-ignore-next-line */
            $scriptSig,
            $vout,
            $sequence,
            $witness
        );
    }
}
