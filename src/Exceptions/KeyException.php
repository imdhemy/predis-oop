<?php


namespace Imdhemy\Redis\Exceptions;

use Imdhemy\Redis\Contracts\Support\Key;
use RuntimeException;

class KeyException extends RuntimeException
{
    /**
     * @param Key $key
     * @return static
     */
    public static function dumpErrorKeyNotFound(Key $key): self
    {
        return new self(sprintf("Couldn't dump the key %s. Key not found!", $key));
    }

    /**
     * @param Key $key
     * @return static
     */
    public static function restoreErrorKeyExists(Key $key): self
    {
        return new self(sprintf("The target key %s already exists", $key));
    }
}
