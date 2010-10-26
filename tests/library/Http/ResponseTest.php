<?php

namespace Respect\Http;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp()
    {
        $this->object = new Response;
    }

    public function testParseLine()
    {
        $this->object->parseStartLine("HTTP/1.1 200 Ok\r\n");
        $this->assertEquals(1, $this->object->getVersion());
        $this->assertEquals(200, $this->object->getCode());
        $this->assertEquals('Ok', $this->object->getPhrase());
    }

}