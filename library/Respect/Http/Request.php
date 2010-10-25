<?php

namespace Respect\Http;

class Request extends Message
{

    protected $type = self::TYPE_REQUEST;
    protected $method;
    protected $uri;

    public function parseStartLine($line)
    {
        
    }

}