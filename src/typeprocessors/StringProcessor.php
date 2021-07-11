<?php

namespace venveo\hubspottoolbox\typeprocessors;

class StringProcessor implements TypeProcessorInterface {

    public static function getHandle(): string
    {
        return 'STRING';
    }

    public static function process($input)
    {
        return (string)$input;
    }
}