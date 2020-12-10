<?php


namespace Imdhemy\Redis\Support;

use DateTime;
use Imdhemy\Redis\Contracts\Support\Key;
use Imdhemy\Redis\Exceptions\KeyException;
use Predis\ClientInterface;
use Predis\Response\ServerException;

class KeyManager
{
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

    /**
     * @param Key $key
     * @return int
     */
    public function delete(Key $key): int
    {
        return $this->client->del($key);
    }

    /**
     * @param array|Key[] $keys
     * @return int
     */
    public function deleteMany(array $keys): int
    {
        return $this->client->del($keys);
    }

    /**
     * @param Key $key
     * @return string
     */
    public function dump(Key $key): string
    {
        $dump = $this->client->dump($key);
        if (!is_null($dump)) {
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
}
