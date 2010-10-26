<?php

namespace Respect\Http;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    protected $object;

    protected function setUp()
    {
        $this->object = new Request;
    }

    public function testParseLine()
    {
        $this->object->parseStartLine("GET /fooBar HTTP/1.1");
        $this->assertEquals('1.1', $this->object->getVersion());
        $this->assertEquals('/fooBar', $this->object->getUri());
        $this->assertEquals('GET', $this->object->getMethod());
    }

}