<?php


namespace Imdhemy\Redis\DataTypes;

use Imdhemy\Redis\Contracts\DataTypes\RedisString as StringContract;
use Imdhemy\Redis\Contracts\Support\Key;
use Predis\ClientInterface;

class RedisString implements StringContract
{
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
}
