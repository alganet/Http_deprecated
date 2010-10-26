<?php

namespace Respect\Http;

class Request extends Message
{

    protected $type = self::TYPE_REQUEST;
    protected $method;
    protected $uri;

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function parseStartLine($line)
    {
        preg_match(Message::FORMAT_REQUEST_LINE, $line, $matches);
        if ($matches)
            list(, $this->method, $this->uri, $this->version) = $matches;
    }

}