<?php

namespace Tests\Support;

use ArrayAccess;
use DateTime;
use Imdhemy\Redis\Contracts\DataTypes\RedisString;
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
        $this->client->set($key, 'foo_bar');
        $this->assertTrue($this->keys->delete($key));
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

    /**
     * @test
     */
    public function test_persist()
    {
        $key = new Key('foo');
        $this->client->set($key, 'bar');
        $keys = new KeyManager($this->client);
        $keys->expireAtDatetime($key, (new DateTime())->modify('+1 day'));
        $this->assertNotEquals(-1, $this->client->ttl($key));
        $this->assertTrue($keys->persist($key));
        $this->assertEquals(-1, $this->client->ttl($key));
    }

    /**
     * @test
     */
    public function test_p_expire()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('foo');
        $this->client->set($key, 'bar');
        $keys->pExpire($key, 1000);
        sleep(1);
        $this->assertEquals(-2, $this->client->ttl($key));
    }

    /**
     * @test
     */
    public function test_p_expire_at()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('foo');
        $this->client->set($key, 'bar');
        $keys->pExpireAt($key, (new DateTime())->modify('+1 day')->getTimestamp() * 1000);
        $this->assertGreaterThan(0, $this->client->ttl($key));
    }

    /**
     * @test
     */
    public function test_p_ttl()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('myKey' . time());
        $this->assertEquals(-2, $keys->pTTl($key));
        $this->client->set($key, 'foo_bar');
        $keys->pExpire($key, 1000 * 1000);
        $this->assertGreaterThan(-2, $keys->pTTl($key));
    }

    /**
     * @test
     */
    public function test_random_key()
    {
        $keys = new KeyManager($this->client);
        $this->assertInstanceOf(Key::class, $keys->randomKey());
    }

    /**
     * @test
     */
    public function test_rename()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('myKey');
        $this->client->set($key, 'hello');
        $newKey = new Key('myMotherKey');
        $response = $keys->rename($key, $newKey);
        $this->assertEquals($newKey, $response);
        $keys->delete($newKey);
    }

    /**
     * @test
     */
    public function test_rename_nx()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('redis:' . time());
        $this->client->set($key, 'foo_bar');
        $target = new Key('new_name');
        $this->assertTrue($keys->renameIfAvailable($key, $target));
        $keys->delete($target);
    }

    /**
     * @test
     */
    public function test_ttl()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('redis:' . time());
        $this->client->set($key, 'foo_bar');
        $this->assertEquals(-1, $keys->ttl($key));
    }

    /**
     * @test
     */
    public function test_type()
    {
        $keys = new KeyManager($this->client);
        $key = new Key('redis:' . time());
        $this->client->set($key, 'foo_bar');
        $this->assertEquals(RedisString::class, $keys->type($key));
    }

    /**
     * @test
     */
    public function test_set_ttl()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo_bar');
        $this->assertTrue($this->keys->setTTL($key, 1));
    }

    /**
     * @test
     */
    public function test_get_ttl()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo_bar');
        $this->keys->setTTL($key, 1);
        $this->assertEquals(1, $this->keys->getTTL($key));
    }

    /**
     * @test
     */
    public function test_set_ttl_ms()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo_bar');
        $this->assertTrue($this->keys->setTTLMs($key, 1000));
    }

    /**
     * @test
     */
    public function test_get_ttl_ms()
    {
        $key = new Key('redis' . time());
        $this->client->set($key, 'foo_bar');
        $this->keys->setTTLMs($key, 1000);
        $this->assertEquals(1000, $this->keys->getTTLMs($key));
    }
}
