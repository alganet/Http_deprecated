<?php

namespace Respect\Http;

class Response extends Message
{

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

    public function parseStartLine($line)
    {
        preg_match(Message::FORMAT_STATUS_LINE, $line, $matches);
        if ($matches)
            list(, $this->version, $this->code, $this->phrase) = $matches;
    }

}