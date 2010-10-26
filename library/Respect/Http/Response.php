<?php

namespace Respect\Http;

class Response extends Message
{

    protected $request;
    protected $type = self::TYPE_RESPONSE;
    protected $code;
    protected $phrase;

    public function getCode()
    {
        return $this->code;
    }

    public function getPhrase()
    {
        return $this->phrase;
    }

    public function getRequest()
    {
        return $this->request ? : new Request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function parseStartLine($line)
    {
        preg_match(Message::FORMAT_STATUS_LINE, $line, $matches);
        if ($matches)
            list(, $this->version, $this->code, $this->phrase) = $matches;
    }

}