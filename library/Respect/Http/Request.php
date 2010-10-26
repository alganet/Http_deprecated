<?php

namespace Respect\Http;

class Request extends Message
{

    protected $response;
    protected $type = self::TYPE_REQUEST;
    protected $method;
    protected $uri;

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

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
            list(, $this->method, $uri, $this->version) = $matches;
        $this->uri = urldecode($uri);
    }

}