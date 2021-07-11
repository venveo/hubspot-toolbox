<?php

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveRecord;

/**
 * Class HubSpotObjectProperty
 * @package venveo\hubspottoolbox\records
 * @property int $id [int]
 * @property string $objectType [varchar(32)]
 * @property string $name [varchar(32)]
 * @property string $dataType [varchar(32)]
 */
class HubSpotObjectProperty extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hubspot_object_properties}}';
    }
}