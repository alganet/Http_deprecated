<?php

namespace Respect\Http;

abstract class Message
{
    const STATE_START_LINE=1;
    const STATE_HEADERS=2;
    const STATE_BODY=3;

    const VERSION_0=0;
    const VERSION_1=1;

    const TYPE_UNKNOWN=0;
    const TYPE_REQUEST=1;
    const TYPE_RESPONSE=2;

    protected $state = 1;
    protected $version = 0;
    protected $type = null;
    protected $buffer = '';
    protected $headers = array();

    public function feed($textFragment)
    {
        $this->buffer .= $textFragment;
        do {
            $line = $this->getBufferLine();
            $this->parse();
        } while ($line);
    }

    public function parse()
    {
        switch ($this->state) {
            case self::STATE_START_LINE:
                $line = $this->getBufferLine();
                if (!$line)
                    return;
                $this->parseStartLine($line);
                $this->state = self::STATE_HEADERS;
                break;
            case self::STATE_HEADERS:
                $line = $this->getBufferLine();
                if ('' !== $line)
                    $this->parseHeader($line);
                else
                    $this->state = self::STATE_BODY;
                break;
            case self::STATE_BODY:
                break;
        }
    }

    public function parseStartLine($line)
    {
        return $line;
    }

    public function parseHeader($line)
    {
        return new Header;
    }

    protected function getBufferLine()
    {
        $parts = explode(chr(10) . chr(13), $this->buffer, 2);
        $line = false;
        if (2 === count($parts))
            list($line, $this->buffer) = $parts;
        return $line;
    }

    public static function getBuilder()
    {
        $text = '';
        $message = null;
        return function($textFragment) use (&$text, &$message) {
            if (!is_null($message)) {
                $message->feed($textFragment);
                return $message;
            } else {
                $text .= $textFragment;
                switch (Message::guessType($text)) {
                    case Message::TYPE_REQUEST:
                        $message = new Request();
                        $message->feed($text);
                        return $message;
                        break;
                    case Message::TYPE_RESPONSE:
                        $message = new Response();
                        $message->feed($text);
                        return $message;
                        break;
                    default:
                        return $text;
                }
            }
        };
    }

    public static function guessType($textFragment)
    {
        $textFragment = self::normalizeWhitespace($textFragment);
        //Minimal HTTP Status-Line size is 14 chars: GET / HTTP/1.1
        if (14 > strlen($textFragment))
            return self::TYPE_UNKNOWN;
        $httpPosition = strpos($textFragment, 'HTTP/');
        if (false === $httpPosition)
            return self::TYPE_UNKNOWN;
        elseif (0 === $httpPosition)
            return self::isResponse($textFragment);
        else
            return self::isRequest($textFragment);
    }

    public static function isResponse($textFragment)
    {
        //Sample: HTTP/1,1 200 Created
        return preg_match(
            "/^HTTP\/1\.[0-9]+ [0-9]{3} (\w)+\s*[\r][\n]$/", $textFragment
        ) ? self::TYPE_RESPONSE : self::TYPE_UNKNOWN;
    }

    public static function isRequest($textFragment)
    {
        //Sample: GET /fooBar HTTP/1.1
        return preg_match(
            "/^[A-Z]* .*? HTTP\/1\.[0-9]+\s*[\r][\n]$/", $textFragment
        ) ? self::TYPE_REQUEST : self::TYPE_UNKNOWN;
    }

    public static function normalizeWhitespace($text)
    {
        //Turns one or more spaces or horizontal tabs into a sigle space
        return trim(preg_replace("/[ \t]+/", ' ', $text), ' ');
    }

}