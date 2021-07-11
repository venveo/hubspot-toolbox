<?php

namespace venveo\hubspottoolbox\traits;

use SevenShores\Hubspot\Factory;
use venveo\hubspottoolbox\HubSpotToolbox;

trait HubSpotApiKeyAuthorization
{
    private static $hsKeyed;

    public function getHubSpotFromKey(): Factory
    {
        if (self::$hsKeyed) {
            return self::$hsKeyed;
        }

        $settings = HubSpotToolbox::$plugin->getSettings();
        $apiKey = \Craft::parseEnv($settings->apiKey);
        return self::$hsKeyed = Factory::create($apiKey);
    }
}