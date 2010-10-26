<?php

namespace Respect\Http;

class Header
{

    protected static $supportedFields = array(
    );
    protected $name;
    protected $value;

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function createFromLine($line)
    {
        $parts = explode(':', $line, 2);
        if (2 > count($parts))
            return false;
        list($fieldName, $fieldValue) = $parts;
        $fieldName = self::normalizeFieldName($fieldName);
        $fieldClass = self::getFieldClassName($fieldName);
        $fieldValue = Message::normalizeWhitespace($fieldValue);
        return new $fieldClass($fieldName, $fieldValue);
    }

    public static function normalizeFieldName($fieldName)
    {
        $nameParts = explode('-', strtolower($fieldName));
        $nameParts = array_map('ucfirst', $nameParts);
        return Message::normalizeWhitespace(implode('-', $nameParts));
    }

    public static function getFieldClassName($fieldName)
    {
        $className = str_replace('-', '', $fieldName);
        if (in_array($className, self::$supportedFields))
            return __NAMESPACE__ . '\\Headers\\' . $className;
        else
            return
            __NAMESPACE__ . '\\Header';
    }

    public function __construct($fieldName, $fieldValue)
    {
        $this->name = $fieldName;
        $this->value = $fieldValue;
    }

}