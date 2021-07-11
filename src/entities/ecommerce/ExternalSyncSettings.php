<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use venveo\hubspottoolbox\entities\HubSpotEntity;
use venveo\hubspottoolbox\validators\EmbeddedModelValidator;

/**
 * @package venveo\hubspottoolbox\entities
 */
class ExternalSyncSettings extends HubSpotEntity
{
    /**
     * @var <ExternalPropertyMapping>[]
     */
    public array $properties = [];

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = ['properties', 'each', 'rule' => [EmbeddedModelValidator::class]];
        return $rules;
    }

    public function addPropertyMapping(ExternalPropertyMapping $mapping)
    {
        $this->properties[] = $mapping;
    }
}