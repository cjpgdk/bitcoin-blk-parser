<?php

namespace Cjpg\Bitcoin\Blk;

use Cjpg\Bitcoin\Blk\Readers\Stream;

/**
 * TxParser reads the transaction from an input of bytes (binary string) or a
 * hexadecimal string.
 * 
 * **fee can't be calculated without having access to the spending output.**
 * 
 * @todo make locktime an object that calculates time or block height.
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
     * @var int Will change to a lock time object at some point.
     */
    public readonly int $locktime;
    
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
     * **Format**
     * 
     * `script_sig` will change after publish, as i will make a small script
     * parser standalone package.
     * 
     * 
     * 
     * Note that witness is only present on transactions that are flagged as
     * SegWit.
     * 
     * The item `coinbase` is only present on the coinbase transaction,
     * and item `script_sig` is present on the reset.
     * 
     * 
     * ```json
     * [
     *  {
     *    //* For coinbase transaction the txid is 64 zeroes (0)
     *    "txid": "HEX",
     *    "vout": NUMBER,
     *    //* Only for coinbase transaction (the first tx)
     *    "coinbase": "HEX String",
     *    //* Not present on coinbase but on the rest.
     *    "script_sig": "HEX String",
     *    "witness": [
     *      "HEX String",  ...
     *    ],
     *    "sequence": NUMBER
     *  },
     *  ...
     * ]
     * 
     * ```
     * 
     * @var array
     */
    public readonly array $inputs;
    
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
     * @var array
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
     * @var string
     */
    public readonly string $hex;
    
    /**
     * The transaction weight
     * 
     * @var int
     */
    public readonly int $weight;

    /*
      "": 3206
     */

    public function __construct(string $txid, string $hash, int $size, 
            int $vsize, int $locktime, int $version, int $inputCount, 
            array $inputs,  int $outputCount, array $outputs, 
            ?string $hex = null, bool $segwit = false)
    {
        
        $this->txid = $txid;
        $this->hash = $hash;
        $this->size = $size;
        $this->vsize = $vsize;
        $this->locktime = $locktime;
        $this->version = $version;
        $this->inputCount = $inputCount;
        $this->inputs = $inputs;
        $this->outputCount = $outputCount;
        $this->outputs = $outputs;
        $this->hex = $hex;
        $this->segwit = $segwit;
        
        $this->weight = $this->size * 3 + $this->vsize;
    }
    /**
     * Reads the transaction data from the stream.
     * 
     * *Note that the stream must be positioned at transaction start.*
     * 
     * @param Stream $block
     * @return static
     */
    public static function fromStream(Stream $block): self
    {
        // for later to calculate the tx hash/txid
        $pos = $block->tell();
        
        // The tx version.
        $version = Utilities::swapEndian(bin2hex($block->read(4)), true);
        
        // check segwit flag!
        $flag0 = hexdec(bin2hex($block->read(1)));
        $flag1 = hexdec(bin2hex($block->read(1)));
        if(!($segwit = ($flag0 == 0 && $flag1 > 0))) {
            // if not segwit go back 2 bytes.
            $block->seek($block->tell()-2);
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
        if($segwit) {
            // for the tx hash/txid
            $segwitStart =  $segwitEnd = null;
            static::readWitnessesFromStream(
                $block, $inputCount, $inputs, $segwitStart, $segwitEnd
            );
        }
        
        // locktime
        $locktime = Utilities::swapEndian(bin2hex($block->read(4)));
        
        // Finish reading this transaction, get tx id (hash)
        $currentP = $block->tell();
        $block->seek($pos);
        
        if($segwit) {
            // to get the transaction id for the transactions with
            // witness data we must strip the data!
            
            // TX Version
            $buf = $block->read(4);
            
            // $pos Start pos + 4 bytes version and 2 bytes segwit flag.
            $block->seek($pos+6);
            
            // read up to witness data start.
            $buf .= $block->read($segwitStart - $block->tell());
            
            // move past the witness data!
            $block->seek($segwitEnd);
            
            $buf .= $block->read(4); // locktime!

            
            // the full tx data!
            $block->seek($pos);
            $txData = $block->read($currentP - $pos);
            $block->seek($currentP);
            
            // tx id.
            $txid  = Utilities::swapEndian(Utilities::hash256($buf), false);
            // tx hash
            $hash  = Utilities::swapEndian(Utilities::hash256($txData), false);
            $hex   = bin2hex($txData);
            $size  = strlen($txData);
            $vsize = strlen($buf);
        } else {
            $txData = $block->read($currentP - $pos);
            $txid   = Utilities::swapEndian(Utilities::hash256($txData), false);
            $hash   = $txid;
            $hex    = bin2hex($txData);
            $size   = strlen($txData);
            $vsize  = $size;
        }
        
        return new static(
            $txid, $hash, $size, $vsize, $locktime, $version,
            $inputCount, $inputs, $outputCount, $outputs, $hex, $segwit
        );
    }
    
    /**
     * Reads the witness data from the stream.
     * 
     * @param Stream $block
     * @param int $inputCount
     * @param array $inputs
     * @param int|null $ps
     * @param int|null $pe
     * @return array
     */
    protected static function readWitnessesFromStream(Stream $block, 
            int $inputCount, array & $inputs, ?int & $ps, ?int & $pe)
    {
        // for later to calculate the tx hash/txid
        $ps = $block->tell();

        for ($i = 0; $i < $inputCount; $i++) {
            // number of witness elements is some times 0 (zero)!
            $witnessCount = $block->readVarInt();
            for ($j = 0; $j < $witnessCount; $j++) {
                $read = $block->readVarInt();
                $inputs[$i]['witness'][$i] = $read<=0?'':bin2hex($block->read($read));
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
     * @return array
     */
    protected static function readOutputsFromStream(Stream $block, int $outputCount): array
    {
        $outputs = [];
        for($i = 0;$i < $outputCount; ++$i) {
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
     * @return array
     */
    protected static function readInputsFromStream(Stream $block, int $inputsCount, bool $segwit): array
    {
        $inputs = [];
        for($i = 0;$i < $inputsCount; ++$i) {
            $inputs[$i] = [];
            
            if($segwit) {
                // only if the tx has witness.
                $inputs[$i]['witness'] = [];
            }
            
            // txid
            $inputs[$i]['txid'] = Utilities::swapEndian(
                bin2hex($block->read(32)), false
            );
            
            // vout
            $inputs[$i]['vout'] = Utilities::swapEndian(
                bin2hex($block->read(4))
            );
            
            // script_sig (can by empty!)
            $read = $block->readVarInt();
            $indputData = $read <= 0 ? '' : $block->read($read);
            if($inputs[$i]['txid'] == '0000000000000000000000000000000000000000000000000000000000000000') {
                // Coin base tx.
                unset($inputs[$i]['txid']);
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
