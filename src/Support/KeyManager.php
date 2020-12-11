<?php


namespace Imdhemy\Redis\Support;

use ArrayAccess;
use DateTime;
use Illuminate\Support\Collection;
use Imdhemy\Redis\Contracts\DataTypes\RedisHashMap;
use Imdhemy\Redis\Contracts\DataTypes\RedisList;
use Imdhemy\Redis\Contracts\DataTypes\RedisSet;
use Imdhemy\Redis\Contracts\DataTypes\RedisSortedSet;
use Imdhemy\Redis\Contracts\DataTypes\RedisStream;
use Imdhemy\Redis\Contracts\DataTypes\RedisString;
use Imdhemy\Redis\Contracts\Support\Key;
use Imdhemy\Redis\Exceptions\KeyException;
use Predis\ClientInterface;
use Predis\Response\ServerException;
use Predis\Response\Status;

class KeyManager
{
    const DATA_TYPES = [
        'string' => RedisString::class,
        'list' => RedisList::class,
        'set' => RedisSet::class,
        'zset' => RedisSortedSet::class,
        'hash' => RedisHashMap::class,
        'stream' => RedisStream::class,
    ];
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * KeyManager constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function del(Key $key): bool
    {
        return $this->client->del($key);
    }

    /**
     * @param array $keys
     * @return int
     */
    public function delMany(array $keys): int
    {
        return $this->client->del($keys);
    }

    /**
     * @param Key $key
     * @return bool
     */
    public function delete(Key $key): bool
    {
        return $this->del($key);
    }

    /**
     * @param array|Key[] $keys
     * @return int
     */
    public function deleteMany(array $keys): int
    {
        return $this->delMany($keys);
    }

    /**
     * @param Key $key
     * @return string
     */
    public function dump(Key $key): string
    {
        $dump = $this->client->dump($key);
        if (! is_null($dump)) {
            return $dump;
        }

        throw KeyException::dumpErrorKeyNotFound($key);
    }

    /**
     * @param Key $key
     * @param string $dump
     * @param int $ttl
     * @return bool
     */
    public function restore(Key $key, string $dump, int $ttl = 0): bool
    {
        try {
            $result = $this->client->restore($key, $ttl, $dump);

            return strtolower($result) === 'ok';
        } catch (ServerException $exception) {
            throw KeyException::restoreErrorKeyExists($key);
        }
    }

    /**
     * @param Key $key
     * @return bool
     */
    public function exists(Key $key): bool
    {
        return $this->client->exists($key);
    }

    /**
     * @param Key $key
     * @param int $second
     * @return bool
     */
    public function expire(Key $key, int $second): bool
    {
        return $this->client->expire($key, $second);
    }

    /**
     * @param Key $key
     * @param int $time
     * @return bool
     */
    public function expireAtTimestamp(Key $key, int $time): bool
    {
        return $this->client->expireat($key, $time);
    }

    /**
     * @param Key $key
     * @param DateTime $dateTime
     * @return bool
     */
    public function expireAtDatetime(Key $key, DateTime $dateTime): bool
    {
        return $this->expireAtTimestamp($key, $dateTime->getTimestamp());
    }

    /**
     * @param string $pattern
     * @return ArrayAccess|Key[]|Collection
     */
    public function match(string $pattern): ArrayAccess
    {
        $result = $this->client->keys($pattern);
        $collection = new Collection();
        foreach ($result as $key) {
            $collection->add(new \Imdhemy\Redis\Support\Key($key));
        }

        return $collection;
    }

    /**
     * @param string $pattern
     * @return ArrayAccess|Key[]|Collection
     */
    public function keys(string $pattern): ArrayAccess
    {
        return $this->match($pattern);
    }

    /**
     * @param Key $key
     * @return bool
     */
    public function persist(Key $key): bool
    {
        return $this->client->persist($key);
    }

    /**
     * @param Key $key
     * @param int $millisecond
     * @return bool
     */
    public function pExpire(Key $key, int $millisecond): bool
    {
        return $this->client->pexpire($key, $millisecond);
    }

    /**
     * @param Key $key
     * @param int $timestamp
     * @return bool
     */
    public function pExpireAt(Key $key, int $timestamp): bool
    {
        return $this->client->pexpireat($key, $timestamp);
    }

    /**
     * @param Key $key
     * @return int
     */
    public function pTTl(Key $key): int
    {
        return $this->client->pttl($key);
    }

    /**
     * @return Key|null
     */
    public function randomKey(): ?Key
    {
        $response = $this->client->randomkey();

        return $response ? new \Imdhemy\Redis\Support\Key($response) : null;
    }

    /**
     * @param Key $key
     * @param Key $newKey
     * @return Key
     */
    public function rename(Key $key, Key $newKey): Key
    {
        try {
            $this->client->rename($key, $newKey);

            return $newKey;
        } catch (ServerException $exception) {
            throw  KeyException::renameKeyNotFound($key);
        }
    }

    /**
     * @param Key $key
     * @param Key $target
     * @return bool
     */
    public function renameNx(Key $key, Key $target): bool
    {
        try {
            return $this->client->renamenx($key, $target);
        } catch (ServerException $exception) {
            throw  KeyException::renameKeyNotFound($key);
        }
    }

    /**
     * @param Key $key
     * @param Key $target
     * @return bool
     */
    public function renameIfAvailable(Key $key, Key $target): bool
    {
        return $this->renameNx($key, $target);
    }

    /**
     * @param Key $key
     * @return bool
     */
    public function touch(Key $key): bool
    {
        return $this->client->touch($key);
    }

    /**
     * @param Key $key
     * @return int
     */
    public function ttl(Key $key): int
    {
        return $this->client->ttl($key);
    }

    /**
     * @param Key $key
     * @return string
     */
    public function type(Key $key): string
    {
        /** @var Status $result */
        $result = $this->client->type($key);

        if (isset(self::DATA_TYPES[$result->getPayload()])) {
            return self::DATA_TYPES[$result->getPayload()];
        }

        throw KeyException::getTypeNotFound($key);
    }
}
