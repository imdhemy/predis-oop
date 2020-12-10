<?php


namespace Tests;

use Predis\Client;

class ExampleTestCase extends TestCase
{
    /**
     * @test
     */
    public function test_client()
    {
        $this->assertInstanceOf(Client::class, $this->client);
        $this->client->set('redis', 'oop');
        $this->assertEquals('oop', $this->client->get('redis'));
    }
}
