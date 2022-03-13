<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk;

use Cjpg\Bitcoin\Blk\Readers\Stream;

/**
 * TxParser reads the transaction from an input of bytes (binary string) or a
 * hexadecimal string.
 *
 * **fee can't be calculated without having access to the spending output.**
 *
 * @todo make output value into a Money object for simple calculations.
 */
class TxParser
{
    /**
     * The transaction id.
     *
     * @var string
     */
    public readonly string $txid;

    /**
     * The transaction hash (differs from txid for witness transactions).
     *
     * @var string
     */
    public readonly string $hash;

    /**
     * The transaction size
     *
     * @var int
     */
    public readonly int $size;

    /**
     * The virtual transaction size (differs from size for witness transactions)
     *
     * @var int
     */
    public readonly int $vsize;

    /**
     * The block height or timestamp of when  the transaction can be spend.
     *
     * @var LockTime
     */
    public readonly LockTime $locktime;

    /**
     * The version of this transaction.
     *
     * @var int
     */
    public readonly int $version;

    /**
     * The number of inputs contained in this transaction.
     *
     * @var int
     */
    public readonly int $inputCount;

    /**
     * The transaction inputs.
     *
     * @var Inputs
     */
    public readonly Inputs $inputs;

    /**
     * The number of outputs contained in this transaction.
     *
     * @var int
     */
    public readonly int $outputCount;

    /**
     * The transaction outputs.
     *
     * **Format**
     *
     * *Note that the value is the number of Satoshis '1 btc = 10^8'*
     *
     * `script_pub_key` will change after publish, as i will make a small script
     * parser standalone package
     *
     * ```json
     * [
     *   {
     *     "value": NUMBER,
     *     "n": NUMBER,
     *     "script_pub_key": "HEX String"
     *   }
     *   ...
     * ]
     *
     * @var array<mixed>
     */
    public readonly array $outputs;

    /**
     * Flag indicating the presence of witness data
     *
     * @var bool
     */
    public readonly bool $segwit;

    /**
     * The full transaction as a hexadecimal value.
     *
     * @var string|null
     */
    public readonly ?string $hex;

    /**
     * The transaction weight
     *
     * @var int
     */
    public readonly int $weight;

    /**
     *
     * @param string $txid
     * @param string $hash
     * @param int $size
     * @param int $vsize
     * @param int $locktime
     * @param int $version
     * @param int $inputCount
     * @param array<mixed> $inputs
     * @param int $outputCount
     * @param array<mixed> $outputs
     * @param string|null $hex
     * @param bool $segwit
     */
    public function __construct(
        string $txid,
        string $hash,
        int $size,
        int $vsize,
        int $locktime,
        int $version,
        int $inputCount,
        array $inputs,
        int $outputCount,
        array $outputs,
        ?string $hex = null,
        bool $segwit = false
    ) {

        $this->txid = $txid;
        $this->hash = $hash;
        $this->size = $size;
        $this->vsize = $vsize;
        $this->locktime = new LockTime($locktime);
        $this->version = $version;
        $this->inputCount = $inputCount;
        $this->inputs = new Inputs($inputs);
        $this->outputCount = $outputCount;
        $this->outputs = $outputs;
        $this->hex = $hex;
        $this->segwit = $segwit;

        $this->weight = $this->vsize * 3 + $this->size;
    }

    /**
     * Reads the transaction data from the stream.
     *
     * *Note that the stream must be positioned at transaction start.*
     *
     * @param Stream $block
     * @return \Cjpg\Bitcoin\Blk\TxParser
     */
    public static function fromStream(Stream $block): TxParser
    {
        // for later to calculate the tx hash/txid
        $pos = $block->tell();

        // The tx version.
        $version = Utilities::swapEndian(bin2hex($block->read(4)), true);

        // check segwit flag!
        $flag0 = hexdec(bin2hex($block->read(1)));
        $flag1 = hexdec(bin2hex($block->read(1)));
        if (!($segwit = ($flag0 == 0 && $flag1 > 0))) {
            // if not segwit go back 2 bytes.
            $block->seek($block->tell() - 2);
        }

        // Inputs
        // - Count
        $inputCount = $block->readVarInt();
        // - vins
        $inputs = static::readInputsFromStream($block, $inputCount, $segwit);

        // Outputs
        // - Count
        $outputCount = $block->readVarInt();
        // - vouts
        $outputs = static::readOutputsFromStream($block, $outputCount);

        // SegWit
        if ($segwit) {
            // for the tx hash/txid
            $segwitStart =  $segwitEnd = null;
            static::readWitnessesFromStream(
                $block,
                $inputCount,
                $inputs,
                $segwitStart,
                $segwitEnd
            );
        }

        // locktime
        $locktime = Utilities::swapEndian(bin2hex($block->read(4)));

        // Finish reading this transaction, get tx id (hash)
        $currentP = $block->tell();
        /** @phpstan-ignore-next-line */
        $block->seek($pos);

        if ($segwit) {
            // to get the transaction id for the transactions with
            // witness data we must strip the data!

            // TX Version
            $buf = $block->read(4);

            // $pos Start pos + 4 bytes version and 2 bytes segwit flag.
            $block->seek($pos + 6);

            // read up to witness data start.
            $buf .= $block->read($segwitStart - $block->tell());

            // move past the witness data!
            $block->seek($segwitEnd);

            $buf .= $block->read(4); // locktime!


            // the full tx data!
            /** @phpstan-ignore-next-line */
            $block->seek($pos);
            $txData = $block->read($currentP - $pos);
            /** @phpstan-ignore-next-line */
            $block->seek($currentP);

            // tx id.
            $txid  = Utilities::swapEndian(Utilities::hash256($buf), false);
            // tx hash
            $hash  = Utilities::swapEndian(Utilities::hash256($txData), false);
            $hex   = bin2hex($txData);
            $size  = strlen($txData);
            // WITNESS_SCALE_FACTOR = 4
            // ((strlen($buf) * 3 + $size) + 3) / 4;
            $vsize = strlen($buf);
        } else {
            $txData = $block->read($currentP - $pos);
            $txid   = Utilities::swapEndian(Utilities::hash256($txData), false);
            $hash   = $txid;
            $hex    = bin2hex($txData);
            $size   = strlen($txData);
            $vsize  = $size;
        }

        return new TxParser(
            $txid,
            $hash,
            $size,
            $vsize,
            $locktime,
            $version,
            $inputCount,
            $inputs,
            $outputCount,
            $outputs,
            $hex,
            $segwit
        );
    }

    /**
     * Reads the witness data from the stream.
     *
     * @param Stream $block
     * @param int $inputCount
     * @param array<mixed> $inputs
     * @param int|null $ps
     * @param int|null $pe
     * @return void
     */
    protected static function readWitnessesFromStream(
        Stream $block,
        int $inputCount,
        array &$inputs,
        ?int &$ps,
        ?int &$pe
    ): void {
        // for later to calculate the tx hash/txid
        $ps = $block->tell();

        for ($i = 0; $i < $inputCount; $i++) {
            // number of witness elements is some times 0 (zero)!
            $witnessCount = $block->readVarInt();
            for ($j = 0; $j < $witnessCount; $j++) {
                $read = $block->readVarInt();
                $inputs[$i]['witness'][$j] = $read <= 0 ? '' : bin2hex($block->read($read));
            }

            // btc core removes empty witness so we do the same.
            if (empty($inputs[$i]['witness'])) {
                unset($inputs[$i]['witness']);
            }
        }

        // for later to calculate the tx hash/txid
        $pe = $block->tell();
    }

    /**
     * Reads the outputs of the transaction from the stream.
     *
     * @param Stream $block
     * @param int $outputCount
     * @return array<mixed>
     */
    protected static function readOutputsFromStream(Stream $block, int $outputCount): array
    {
        $outputs = [];
        for ($i = 0; $i < $outputCount; ++$i) {
            $outputs[$i]['value'] = Utilities::swapEndian(
                bin2hex($block->read(8))
            );
            $outputs[$i]['n'] = $i;
            // @todo make a quick script package and use!
            $outputs[$i]['script_pub_key'] = bin2hex(
                $block->read($block->readVarInt())
            );
        }
        return $outputs;
    }

    /**
     * Reads the inputs of the transaction from the stream.
     *
     * @param Stream $block
     * @param int $inputsCount
     * @param bool $segwit
     * @return array<mixed>
     */
    protected static function readInputsFromStream(Stream $block, int $inputsCount, bool $segwit): array
    {
        $inputs = [];
        for ($i = 0; $i < $inputsCount; ++$i) {
            $inputs[$i] = [];

            if ($segwit) {
                // only if the tx has witness.
                $inputs[$i]['witness'] = [];
            }

            // txid
            $inputs[$i]['txid'] = Utilities::swapEndian(
                bin2hex($block->read(32)),
                false
            );

            // vout
            $inputs[$i]['vout'] = Utilities::swapEndian(
                bin2hex($block->read(4))
            );

            // script_sig (can by empty!)
            $read = $block->readVarInt();
            $indputData = $read <= 0 ? '' : $block->read($read);
            if ($inputs[$i]['txid'] == '0000000000000000000000000000000000000000000000000000000000000000') {
                // Coin base tx.
                unset($inputs[$i]['txid']);
                unset($inputs[$i]['vout']);
                $inputs[$i]['coinbase'] = bin2hex($indputData);
            } else {
                // @todo make a quick script package and use!
                $inputs[$i]['script_sig'] = bin2hex($indputData);
            }

            // sequence
            $inputs[$i]['sequence'] = Utilities::swapEndian(
                bin2hex($block->read(4))
            );
        }
        return $inputs;
    }
}
