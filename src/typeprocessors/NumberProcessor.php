<?php

namespace venveo\hubspottoolbox\typeprocessors;

class NumberProcessor implements TypeProcessorInterface {

    public static function getHandle(): string
    {
        return 'NUMBER';
    }

    public static function process($input)
    {
        return (float)$input;
    }
}