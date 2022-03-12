<?php

declare(strict_types=1);

namespace Cjpg\Bitcoin\Blk\Readers;

use Cjpg\Bitcoin\Blk\Utilities;
use InvalidArgumentException;
use RuntimeException;

/**
 * An internal stream class for reading the blk files.
 *
 * @internal
 */
final class Stream
{
    /**
     * Set position equal to offset bytes.
     * @
     */
    public const SEEK_SET = SEEK_SET;
    /**
     * Set position to current location plus offset.
     */
    public const SEEK_CUR = SEEK_CUR;
    /**
     * Set position to end-of-file plus offset.
     */
    public const SEEK_END = SEEK_END;

    /**
     * The underlying stream resource
     *
     * @var resource|null
     */
    protected $stream;

    /**
     * @var null|int
     */
    private $size;

    /**
     * Initialize a new stream from a resource or {@see IStream}
     *
     * if $stream is a {@see IStream}
     * it will be rewinded and detached from the source
     *
     * @param resource|\Cjpg\Bitcoin\Blk\Readers\Stream $stream
     */
    public function __construct($stream)
    {
        if (
            !($stream instanceof Stream) &&
            !is_resource($stream) &&
            !in_array(get_resource_type($stream), ['stream', 'socket'])
        ) {
            throw new InvalidArgumentException('$stream must be a valid PHP stream or socket resource');
        }
        if ($stream instanceof Stream) {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            $this->stream = $stream->detach();
        } else {
            $this->stream = $stream;
        }
    }

    /**
     * Create a new stream from a string.
     *
     * Note that this will create a new file with the help of {@see tmpfile()},
     * if you do not call {@see Stream::close()} the file will remain on the system.
     *
     * @param string $string
     * @return \Cjpg\Bitcoin\Blk\Readers\Stream|null returns null if fopen php://temp fails
     */
    public static function fromString(string $string = ''): Stream|null
    {
        if ($resource = fopen('php://temp', 'r+')) {
            fwrite($resource, $string);
            rewind($resource);
            return new static($resource);
        }
        return null;
    }

    /**
     * Create a stream from an existing file.
     *
     * @param string $filename Filename or stream URI to use as basis of stream.
     * @param string $mode Mode with which to open the underlying filename/stream.
     * @return \Cjpg\Bitcoin\Blk\Readers\Stream|null
     */
    public static function fromFile(string $filename, string $mode = 'r+'): ?Stream
    {
        if (!file_exists($filename) || !is_file($filename)) {
            throw new RuntimeException(sprintf('File [%s] does not exists.', $filename));
        }
        if ($resource = fopen($filename, $mode)) {
            return new static($resource);
        }
        return null;
    }

    /**
     * Closes the stream.
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->stream) {
            fclose($this->stream);
        }
        $this->detach();
    }

    /**
     * Get the current stream and separates it from the current instance.
     *
     * @return resource|null
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        $this->size = null;
        return $stream;
    }

    /**
     * Get the size of the stream.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): int|null
    {
        if ($this->stream && $this->size === null) {
            if ($stats = fstat($this->stream)) {
                /** @phpstan-ignore-next-line */
                $this->size = isset($stats['size']) ? $stats['size'] : 0;
            }
        }
        return $this->size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int|null Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell(): ?int
    {
        if (!$this->stream) {
            $position = null;
        } elseif (($position = ftell($this->stream)) === false) {
            throw new RuntimeException('Unable get the position in the stream.');
        }
        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable');
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->stream  || !$this->isSeekable() || (fseek($this->stream, $offset, $whence) === -1)) {
            throw new RuntimeException('Could not seek in stream.');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void
    {
        if (!$this->stream || !$this->isSeekable()) {
            throw new RuntimeException('Could not rewind stream.');
        }
        rewind($this->stream);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        if (!($mode = $this->getMetadata('mode'))) {
            return false;
        }
        return strstr($mode, 'w') !== false || strstr($mode, '+') !== false;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write(string $string): int
    {
        if (!$this->stream || !$this->isWritable()) {
            throw new RuntimeException('Current stream is not writable.');
        }
        if (($written = fwrite($this->stream, $string)) === false) {
            throw new RuntimeException('Filed to write to stream.');
        }
        $this->size = null;
        return $written;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        if (!($mode = $this->getMetadata('mode'))) {
            return false;
        }
        return strstr($mode, 'r') !== false || strstr($mode, '+') !== false;
    }

    /**
     * Read a variable int.
     *
     * @return int
     */
    public function readVarInt(): int
    {
        $size = ord($this->read(1));
        if ($size == 253) {
            $size = Utilities::swapEndian(bin2hex($this->read(2)));
        } elseif ($size == 254) {
            $size = Utilities::swapEndian(bin2hex($this->read(4)));
        } elseif ($size == 255) {
            $size = Utilities::swapEndian(bin2hex($this->read(8)));
        }
        return $size;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException The stream is not readable.
     */
    public function read(int $length): string
    {
        if (!$this->stream || !$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new RuntimeException('The stream is not readable.');
        }
        return $data;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents(): string
    {
        if (!$this->stream) {
            throw new RuntimeException('No stream to read from.');
        }

        if (($data = stream_get_contents($this->stream)) === false) {
            throw new RuntimeException('Unable to read from stream.');
        }
        return $data;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata(string $key = null)
    {
        /** @phpstan-ignore-next-line */
        if (!$this->stream || !($meta = stream_get_meta_data($this->stream))) {
            return null;
        }
        if (!$key) {
            return $meta;
        }
        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * Gets the stream into a string
     *
     * @link http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->stream) {
            return '';
        }
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }

    /**
     * Closes the stream.
     *
     * @link https://www.php.net/manual/en/language.oop5.decon.php#object.destruct
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
}
