<?php

namespace venveo\hubspottoolbox\records;

use craft\db\ActiveRecord;

/**
 * Class HubSpotObjectMapper
 * @package venveo\hubspottoolbox\records
 * @property int $id [int]
 * @property string $type [varchar(128)]
 * @property int $sourceTypeId [int]
 */
class HubSpotObjectMapper extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%hubspot_object_mappers}}';
    }

    public function getMappings(): \craft\db\ActiveQuery
    {
        return $this->hasMany(HubSpotObjectMapping::class, ['mapperId' => 'id']);
    }

    public function getUnpublishedMappings(): \craft\db\ActiveQuery
    {
        return $this->getMappings()->andWhere(['datePublished' => null]);
    }

    public function getPublishedMappings(): \craft\db\ActiveQuery
    {
        return $this->getMappings()->andWhere(['not', ['datePublished' => null]]);
    }
}