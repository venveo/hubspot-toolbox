<?php

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveRecord;

/**
 * Class HubSpotFeature
 * @package venveo\hubspottoolbox\records
 * @property int $id [int]
 * @property string $type [varchar(255)]
 * @property bool $enabled
 * @property string $settings
 */
class HubSpotFeature extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hubspot_features}}';
    }
}