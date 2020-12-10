<?php


namespace Tests;

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
}
