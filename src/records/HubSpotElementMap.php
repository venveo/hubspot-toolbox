<?php

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveRecord;

/**
 * Class HubSpotElementMap
 * @package venveo\hubspottoolbox\records
 * @property int $id [int]
 * @property int $elementId [int]
 * @property int $elementSiteId [int]
 * @property int $remoteObjectId [int]
 * @property string $dateLastSynced [datetime]
 */
class HubSpotElementMap extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hubspot_element_map}}';
    }

    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'dateLastSynced';
        return $attributes;
    }
}