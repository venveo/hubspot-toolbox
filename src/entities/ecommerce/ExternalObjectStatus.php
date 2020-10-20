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
class ExternalObjectStatus extends HubSpotEntity
{
    public string $storeId;
    public string $objectType;
    public string $externalObjectId;
    public string $hubspotId;
    public string $lastProcessedAt;
    public array $errors;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['storeId', 'objectType', 'externalObjectId', 'lastProcessedAt'], 'required'];
        return $rules;
    }
}