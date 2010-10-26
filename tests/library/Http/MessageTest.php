<?php

namespace Respect\Http;

use Mockery;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function testBuilderIncompleteShortLength()
    {
        $build = Message::getBuilder();
        $incomplete = $build('FooBar');
        $this->assertEquals('FooBar', $incomplete);
    }

    public function testBuilderIncompleteInvalid()
    {
        $build = Message::getBuilder();
        $incomplete = $build('FooBar FooBar FooBar FooBar');
        $this->assertEquals('FooBar FooBar FooBar FooBar', $incomplete);
    }

    public function testBuilderValidRequest()
    {
        $build = Message::getBuilder();
        $message = $build("GET /foo HTTP/1.1\r\n");
        $this->assertType('Respect\Http\Request', $message);
    }

    public function testBuilderValidResponse()
    {
        $build = Message::getBuilder();
        $message = $build("HTTP/1.1 200 Ok\r\n");
        $this->assertType('Respect\Http\Response', $message);
    }

    public function testIsResponse()
    {
        $response = "HTTP/1.1 200 Ok\r\n";
        $this->assertEquals(
            Message::TYPE_RESPONSE, Message::isResponse($response)
        );
    }

    public function testIsRequest()
    {
        $request = "POST /foo HTTP/1.1\r\n";
        $this->assertEquals(
            Message::TYPE_REQUEST, Message::isRequest($request)
        );
    }

    public function testNormalizeWhitespace()
    {
        $denormalized = "POST   /foo    HTTP/1.1    \r\n";
        $normalized = "POST /foo HTTP/1.1 \r\n";
        $this->assertEquals(
            $normalized, Message::normalizeWhitespace($denormalized)
        );
    }

    public function testHeaders()
    {
        $build = Message::getBuilder();
        $message = $build(
            "HTTP/1.1 301 Moved Permanently\r\nLocation: /\r\nFoo: Bar\r\n"
        );
        $this->assertType('Respect\Http\Response', $message);
        $this->assertEquals(2, count($message->getHeaders()));
    }

}