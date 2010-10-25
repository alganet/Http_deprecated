<?php

namespace Respect\Http;

class Response extends Message
{

    protected $type = self::TYPE_RESPONSE;
    protected $code;
    protected $phrase;

    public function parseStartLine($line)
    {
        
    }

}