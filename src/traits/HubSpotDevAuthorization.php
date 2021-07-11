<?php

namespace venveo\hubspottoolbox\traits;

use SevenShores\Hubspot\Factory;
use venveo\hubspottoolbox\HubSpotToolbox;

trait HubSpotDevAuthorization
{
    private static $hsDev = null;

    private static $appId = null;

    /**
     * @return Factory
     */
    public function getHubSpotDev(): Factory
    {
        if (self::$hsDev) {
            return self::$hsDev;
        }

        $settings = HubSpotToolbox::$plugin->getSettings();
        $devKey = \Craft::parseEnv($settings->devApiKey);
        return self::$hsDev = Factory::create($devKey);
    }

    public function getAppId() {
        if (self::$appId) {
            return self::$appId;
        }
        $settings = HubSpotToolbox::$plugin->getSettings();
        return \Craft::parseEnv($settings->appId);
    }
}