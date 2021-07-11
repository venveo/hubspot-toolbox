<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use craft\validators\UriValidator;
use venveo\hubspottoolbox\entities\HubSpotEntity;
use venveo\hubspottoolbox\validators\EmbeddedModelValidator;

/**
 * Class Settings
 * @package venveo\hubspottoolbox\entities
 */
class Settings extends HubSpotEntity
{
    public bool $enabled;
    public ?string $webhookUri = null;
    /**
     * @var <ExternalSyncSettings>[]
     */
    public array $mappings = [];

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['enabled', 'mappings'], 'required'];
        $rules[] = ['webhookUri', UriValidator::class];
        $rules[] = ['mappings', 'each', 'rule' => [EmbeddedModelValidator::class]];
        return $rules;
    }

    public function setObjectSettings($objectType, ExternalSyncSettings $settings)
    {
        $this->mappings[$objectType] = $settings;
    }
}