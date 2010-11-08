<?php

namespace Respect\Http;

abstract class Message
{
    const STATE_START_LINE=1;
    const STATE_HEADERS=2;
    const STATE_BODY=3;

    const TYPE_UNKNOWN=0;
    const TYPE_REQUEST=1;
    const TYPE_RESPONSE=2;

    const FORMAT_REQUEST_LINE = "/^([A-Z]*) (.*?) HTTP\/([0-9]+\.[0-9]+)\s*$/";
    const FORMAT_STATUS_LINE = "/^HTTP\/([0-9]+\.[0-9]+) ([0-9]{3}) (.+)\s*$/";

    static protected $reasonersActivationHeaders = array(
        'Transfer-Encoding' => array('ContentLength', 'TransferEncoding'),
        'Content-Length' => array('ContentLength'),
        'Content-Type' => array('ContentLength'),
        'Connection' => array('ContentLength'),
        'Host' => array('ResourceUri'),
    );
    protected $state = 1;
    protected $version = '1.0';
    protected $type = null;
    protected $buffer = '';
    protected $headers = array();
    protected $headersSet = array();

    public function getVersion()
    {
        return $this->version;
    }

    public function feed($textFragment)
    {
        $this->buffer .= $textFragment;
        while ($this->parse())
            ;
    }

    public function hasHeader($fieldName)
    {
        $fieldName = Header::normalizeFieldName($fieldName);
        return isset($this->headersSet[$fieldName]);
    }

    public function getHeader($fieldName)
    {
        $fieldName = Header::normalizeFieldName($fieldName);
        $found = false;
        foreach ($this->headers as $h) {
            if ($fieldName == $h->getName())
                $found = $h;
        }
        return $found;
    }

    public function parse()
    {
        switch ($this->state) {
            case self::STATE_START_LINE:
                $line = $this->getBufferLine();
                if (!$line)
                    return false;
                $this->parseStartLine($line);
                $this->state = self::STATE_HEADERS;
                return true;
                break;
            case self::STATE_HEADERS:
                $line = $this->getBufferLine();
                if (!$line)
                    return false;
                if ('' !== $line)
                    $this->parseHeader($line);
                else
                    $this->state = self::STATE_BODY;
                return true;
                break;
            case self::STATE_BODY:
                echo 1;
                return false;
                break;
        }
    }

    public function parseStartLine($line)
    {
        return $line;
    }

    public function parseHeader($line)
    {
        $header = Header::createFromLine($line);
        $this->headers[] = $header;
        $headerName = $header->getName();
        $this->headersPresent[$headerName] = 1;
        if (isset(self::$reasonersActivationHeaders[$headerName]))
            $this->reason(self::$reasonersActivationHeaders[$headerName]);
    }

    public function reason($reasonerName)
    {
        $reasonerClass = __NAMESPACE__ . '\\Reasoners\\' . $reasonerName;
        $reasoner = new $reasonerClass;
        $reasoner->apply($this);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    protected function getBufferLine()
    {

        $parts = explode("\r\n", $this->buffer, 2);
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
        $textFragment = explode("\r\n", $textFragment, 2);
        $textFragment = $textFragment[0];

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
            self::FORMAT_STATUS_LINE, $textFragment
        ) ? self::TYPE_RESPONSE : self::TYPE_UNKNOWN;
    }

    public static function isRequest($textFragment)
    {
        //Sample: GET /fooBar HTTP/1.1
        return preg_match(
            self::FORMAT_REQUEST_LINE, $textFragment
        ) ? self::TYPE_REQUEST : self::TYPE_UNKNOWN;
    }

    public static function normalizeWhitespace($text)
    {
        //Turns one or more spaces or horizontal tabs into a sigle space
        return trim(preg_replace("/[ \t]+/", ' ', $text), ' ');
    }

}