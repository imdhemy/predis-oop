<?php

namespace Tests\Support;

use ArrayAccess;
use DateTime;
use Imdhemy\Redis\Exceptions\KeyException;
use Imdhemy\Redis\Support\Key;
use Imdhemy\Redis\Support\KeyManager;
use Tests\TestCase;

class KeyManagerTest extends TestCase
{
    /**
     * @var KeyManager
     */
    private $keys;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new KeyManager($this->client);
    }

    /**
     * @test
     */
    public function test_delete()
    {
        $key = new Key('redis');
        $this->assertIsNumeric($this->keys->delete($key));
    }

    /**
     * @test
     */
    public function test_delete_many()
    {
        $this->assertIsNumeric($this->keys->deleteMany(['foo', 'bar']));
    }

    /**
     * @test
     */
    public function test_dump_throws_exception_if_key_not_found()
    {
        $this->expectException(KeyException::class);
        $this->keys->dump(new Key("redis" . time()));
    }

    /**
     * @test
     */
    public function test_dump()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo');
        $this->assertIsString($this->keys->dump($key));
    }

    /**
     * @test
     */
    public function test_restore_returns_true_on_success()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo');
        $dump = $this->keys->dump($key);
        $this->client->del($key);
        $this->assertTrue($this->keys->restore($key, $dump));
    }

    /**
     * @test
     */
    public function test_exists()
    {
        $key = new Key('redis' . (time() + rand(0, 100)));
        $this->assertFalse($this->keys->exists($key));

        $this->client->set($key, 'foo');
        $this->assertTrue($this->keys->exists($key));
    }

    /**
     * @test
     */
    public function test_expire()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo');
        $this->assertTrue($this->keys->expire($key, 10));
    }

    /**
     * @test
     */
    public function test_expire_at_time()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo');
        $time = (new DateTime())->getTimestamp();
        $this->assertTrue($this->keys->expireAtTimestamp($key, $time));
    }

    /**
     * @test
     */
    public function test_expire_at_datetime()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo');
        $this->assertTrue($this->keys->expireAtDatetime($key, new DateTime()));
    }

    /**
     * @test
     */
    public function test_matches()
    {
        $result = $this->keys->match('redis*');
        $this->assertInstanceOf(ArrayAccess::class, $result);
        $this->assertInstanceOf(Key::class, $result->first());
    }
}
