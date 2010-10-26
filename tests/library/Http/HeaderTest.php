<?php

namespace Respect\Http;

class HeaderTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFromLine()
    {
        $line = 'Location: /';
        $name = 'Location';
        $value = '/';
        $header = Header::createFromLine($line);
        $this->assertType('Respect\Http\Header', $header);
        $this->assertEquals($name, $header->getName());
        $this->assertEquals($value, $header->getValue());
    }

}