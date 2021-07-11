<?php

namespace venveo\hubspottoolbox\features;

use craft\base\SavableComponent;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;

abstract class HubSpotFeature extends SavableComponent implements HubSpotFeatureInterface
{
    public $enabled;

    public function getCpEditUrl() {
        return UrlHelper::cpUrl('hubspot-toolbox/' . static::getHandle());
    }

    public function getRequiredScopes(): array
    {
        return [];
    }

    public function getMenuItem() {
        return null;
    }

    /**
     * Shamelessly taken from Andrew Welch
     * Source: https://nystudio107.com/blog/writing-craft-plugins-with-extensible-components
     * @return array
     */
    public static function getTemplatesRoot(): array
    {
        $reflect = new \ReflectionClass(static::class);
        $classPath = FileHelper::normalizePath(
                dirname($reflect->getFileName())
                . '/../templates'
            )
            . DIRECTORY_SEPARATOR;
        $id = StringHelper::toKebabCase($reflect->getShortName());

        return [$id, $classPath];
    }

    public function renderSettingsHtml(): \Twig\Markup
    {
        return Template::raw($this->getSettingsHtml());
    }
}