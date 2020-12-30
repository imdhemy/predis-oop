<?php


namespace Imdhemy\Redis\DataTypes;

use Imdhemy\Redis\Contracts\DataTypes\RedisString as StringContract;
use Imdhemy\Redis\Contracts\Support\Key;
use Predis\ClientInterface;

class RedisString implements StringContract
{
    const SET = 'SET';
    const GET = 'GET';
    const INCRBY = 'INCRBY';
    const OVERFLOW = 'OVERFLOW';

    const WRAP = 'WRAP';
    const SAT = 'SAT';
    const FAIL = 'FAIL';
    
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Key
     */
    private $key;

    /**
     * @var int
     */
    private $length;

    /**
     * RedisString constructor.
     * @param ClientInterface $client
     * @param Key $key
     */
    public function __construct(ClientInterface $client, Key $key)
    {
        $this->client = $client;
        $this->key = $key;
        $this->length = 0;
    }

    /**
     * @param string $value
     * @return self
     */
    public function set(string $value): self
    {
        $this->client->set($this->key, $value);

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setIfNotExists(string $value): self
    {
        $this->client->set($this->key, $value, null, null, 'NX');

        return $this;
    }

    public function setIfExists(string $value): self
    {
        $this->client->set($this->key, $value, null, null, 'XX');

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function override(string $value): self
    {
        return $this->setIfExists($value);
    }

    /**
     * @param string $value
     * @param int $seconds
     * @return $this
     */
    public function setAndExpire(string $value, int $seconds): self
    {
        $this->client->set($this->key, $value, $seconds);

        return $this;
    }

    /**
     * @param string $value
     * @param int $milliseconds
     * @return $this
     */
    public function setAndExpireMs(string $value, int $milliseconds): self
    {
        $this->client->set($this->key, $value, null, $milliseconds);

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAndKeepTTL(string $value): self
    {
        $this->client->set($this->key, $value, null, 'KEEPTTL');

        return $this;
    }

    /**
     * @param string $value
     * @return self
     */
    public function append(string $value): self
    {
        $this->length = $this->client->append($this->key, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $start
     * @param int $end
     * @return int
     */
    public function bitCount(int $start = 0, int $end = -1): int
    {
        return $this->client->bitcount($this->key, $start, $end);
    }

    /**
     * @param string $type
     * @param string $offset
     * @param string $value
     * @return array|null
     */
    public function bitFieldSet(string $type, string $offset, string $value): ?array
    {
        return $this->client->bitfield($this->key, self::SET, $type, $offset, $value);
    }

    /**
     * @param string $type
     * @param string $offset
     * @param string $overflowControl
     * @return array|null
     */
    public function bitFieldGet(string $type, string $offset, string $overflowControl = self::WRAP): ?array
    {
        return $this->client->bitfield(
            $this->key,
            self::GET,
            $type,
            $offset,
            self::OVERFLOW,
            $overflowControl
        );
    }

    /**
     * @param string $type
     * @param string $offset
     * @param string $increment
     * @param string $overflowControl
     * @return array|null
     */
    public function bitFieldIncrementBy(
        string $type,
        string $offset,
        string $increment,
        string $overflowControl = self::WRAP
    ):
    ?array {
        return $this->client->bitfield(
            $this->key,
            self::INCRBY,
            $type,
            $offset,
            $increment,
            self::OVERFLOW,
            $overflowControl
        );
    }

    /**
     * @param string ...$arguments
     * @return array|null
     */
    public function bitField(...$arguments): ?array
    {
        return $this->client->bitfield($this->key, ...$arguments);
    }
}
