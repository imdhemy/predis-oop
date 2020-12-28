<?php


namespace Tests;

use Imdhemy\Redis\Contracts\Support\Key as KeyContract;
use Imdhemy\Redis\Support\Key;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Predis\Client;

class TestCase extends BaseTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new Client();
    }

    /**
     * @param string|null $name
     * @return KeyContract
     */
    protected function createKey(?string $name = null): KeyContract
    {
        $name = $name ?? "redis_key_" . time();
        return new Key($name);
    }
}
