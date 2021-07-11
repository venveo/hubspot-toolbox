<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use venveo\hubspottoolbox\entities\HubSpotEntity;

/**
 * @package venveo\hubspottoolbox\entities
 */
class ExternalImportMessage extends HubSpotEntity
{
    public string $externalObjectId;
    public array $properties;
    public array $associations;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['externalObjectId', 'properties', 'associations'], 'required'];
        return $rules;
    }
}