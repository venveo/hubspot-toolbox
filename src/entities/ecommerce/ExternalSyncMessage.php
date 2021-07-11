<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\hubspottoolbox\entities\ecommerce;

use venveo\hubspottoolbox\entities\HubSpotEntity;

/**
 * Class HubSpotSyncMessage
 * @package venveo\hubspottoolbox\entities
 */
class ExternalSyncMessage extends HubSpotEntity
{
    public const ACTION_UPSERT = 'UPSERT';
    public const ACTION_DELETE = 'DELETE';

    public const ASSOCIATION_DEAL = 'DEAL';
    public const ASSOCIATION_CONTACT = 'CONTACT';
    public const ASSOCIATION_PRODUCT = 'PRODUCT';

    public string $action = self::ACTION_UPSERT;
    public ?string $changedAt = null;
    public string $externalObjectId = '';
    public array $properties = [];
    public array $associations = [];

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['action', 'externalObjectId', 'properties'], 'required'];
        $rules[] = ['action', 'in', 'range' => [self::ACTION_UPSERT, self::ACTION_DELETE]];
        return $rules;
    }

    /**
     * Adds an associated record
     * @param $associationType
     * @param $externalObjectId
     */
    public function addAssociation($associationType, $externalObjectId): void
    {
        if (!isset($this->associations[$associationType])) {
            $this->associations[$associationType] = [];
        }
        $this->associations[$associationType][] = $externalObjectId;
    }
}