<?php

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveQuery;
use craft\db\ActiveRecord;

/**
 * @package venveo\hubspottoolbox\records
 * @property int $id [int]
 * @property int $mapperId
 * @property int $propertyId [varchar(255)]
 * @property string $template
 * @property string $datePublished
 * @property-read ActiveQuery $property
 * @property-read ActiveQuery $mapper
 */
class HubSpotObjectMapping extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%hubspot_object_mappings}}';
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'datePublished';
        return $attributes;
    }

    /**
     * @return ActiveQuery
     */
    public function getMapper(): ActiveQuery
    {
        return $this->hasOne(HubSpotObjectMapper::class, ['id' => 'mapperId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getProperty(): ActiveQuery
    {
        return $this->hasOne(HubSpotObjectProperty::class, ['id' => 'propertyId']);
    }
}