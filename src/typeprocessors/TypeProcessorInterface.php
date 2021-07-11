<?php

namespace venveo\hubspottoolbox\typeprocessors;

interface TypeProcessorInterface {
    public static function getHandle(): string;

    public static function process($input);
}