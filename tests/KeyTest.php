<?php

namespace Tests;

use Imdhemy\Redis\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    /**
     * @test
     */
    public function test_get_parts()
    {
        $key = new Key('user:1000:followers');
        $this->assertEquals(['user', '1000', 'followers'], $key->getParts());
    }
}
