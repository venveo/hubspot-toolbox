<?php
namespace venveo\hubspottoolbox\features;

class FormsFeature extends HubSpotFeature {

    public static function getName(): string
    {
        return 'Forms';
    }

    public static function getHandle(): string
    {
        return 'forms';
    }
}