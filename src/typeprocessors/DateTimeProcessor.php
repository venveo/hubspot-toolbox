<?php

namespace venveo\hubspottoolbox\typeprocessors;

use venveo\hubspottoolbox\helpers\HubSpotTimeHelper;

class DateTimeProcessor implements TypeProcessorInterface {

    public static function getHandle(): string
    {
        return 'DATETIME';
    }

    public static function process($input)
    {
        if ($input instanceof \DateTime) {
            return HubSpotTimeHelper::prepareDateTimeForHubSpot($input);
        }
        return $input;
    }
}