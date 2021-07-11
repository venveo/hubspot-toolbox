<?php

namespace venveo\hubspottoolbox\features;

use craft\base\SavableComponentInterface;

interface HubSpotFeatureInterface extends SavableComponentInterface
{
    public function getCpEditUrl();

    public static function getName(): string;

    public static function getHandle(): string;

    public static function getTemplatesRoot(): array;

    public function getRequiredScopes(): array;
}