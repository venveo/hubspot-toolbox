<?php

namespace venveo\hubspottoolbox\helpers;

abstract class HubSpotTimeHelper
{
    /**
     * Converts a given datetime into a string format for the HubSpot API.
     *
     * @param \DateTime|null $datetime
     * @return string
     */
    public static function prepareDateTimeForHubSpot($datetime = null): string
    {
        if (!$datetime) {
            $time = time();
        } else {
            $time = $datetime->getTimestamp();
        }
        return strtotime(gmdate('Y-m-d H:i:s ', $time)) . '000';
    }
}