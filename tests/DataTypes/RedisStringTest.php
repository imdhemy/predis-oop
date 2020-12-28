<?php

namespace Tests\DataTypes;

use Imdhemy\Redis\DataTypes\RedisString;
use Tests\TestCase;

class RedisStringTest extends TestCase
{
    /**
     * @test
     */
    public function test_append()
    {
        $key = $this->createKey();
        $str = new RedisString($this->client, $key);
        $value = mt_rand(0, 10);
        $str->append($value);

        $this->assertEquals($value, $this->client->get($key));
        $this->assertEquals($str->getLength(), mb_strlen($value));
    }
}
