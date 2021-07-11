<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use craft\helpers\DateTimeHelper;
use venveo\hubspottoolbox\entities\HubSpotEntity;

/**
 * @package venveo\hubspottoolbox\entities
 */
class ExternalObjectStatus extends HubSpotEntity
{
    public string $storeId;
    public string $objectType;
    public string $externalObjectId;
    /**
     * @var string
     */
    public $hubspotId;
    public $lastProcessedAt;
    public array $errors;

    public function init()
    {
        parent::init();
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['storeId', 'objectType', 'externalObjectId', 'lastProcessedAt'], 'required'];
        return $rules;
    }

    public function microtimeAttributes()
    {
        return ['lastProcessedAt'];
    }
}