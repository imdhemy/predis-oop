<?php

namespace Tests\DataTypes;

use Imdhemy\Redis\Contracts\Support\Key;
use Imdhemy\Redis\DataTypes\RedisString;
use Tests\TestCase;

class RedisStringTest extends TestCase
{
    /**
     * @var RedisString
     */
    private $str;

    /**
     * @var Key
     */
    private $key;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->key = $this->createKey();
        $this->str = new RedisString($this->client, $this->key);
    }

    /**
     * @test
     */
    public function test_append()
    {
        $value = mt_rand(0, 10);
        $this->str->append($value);

        $this->assertEquals($value, $this->client->get($this->key));
        $this->assertEquals($this->str->getLength(), mb_strlen($value));
    }

    /**
     * @test
     */
    public function test_bit_count()
    {
        $this->client->set($this->key, 'foobar');
        $this->assertEquals(26, $this->str->bitCount());
        $this->assertEquals(4, $this->str->bitCount(0, 0));
        $this->assertEquals(6, $this->str->bitCount(1, 1));
    }

    /**
     * @test
     */
    public function test_bit_field()
    {
        $this->assertEquals([0], $this->str->bitFieldSet('u8', '#0', 22));
        $this->assertEquals([22], $this->str->bitFieldGet('u8', '#0'));
        $this->assertEquals([27], $this->str->bitFieldIncrementBy('u8', '#0', 5));
    }
}
