<?php
/**
 * HubSpot Toolbox plugin for Craft CMS 3.x
 *
 * Turnkey HubSpot integration for CraftCMS
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2018 Venveo
 */

namespace venveo\hubspottoolbox\services\hubspot;

use Craft;
use craft\base\Component;
use craft\db\ActiveQuery;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\DateTimeHelper;
use venveo\hubspottoolbox\entities\ObjectProperty;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\propertymappers\PropertyMapperInterface;
use venveo\hubspottoolbox\propertymappers\PropertyMapperPipeline;
use venveo\hubspottoolbox\records\HubSpotObjectMapper as HubSpotObjectMapperRecord;
use venveo\hubspottoolbox\records\HubSpotObjectMapping as HubSpotObjectMappingRecord;
use venveo\hubspottoolbox\traits\HubSpotTokenAuthorization;

class PropertiesService extends Component
{
    use HubSpotTokenAuthorization;

    /**
     * Gets all properties for a type of object from HubSpot
     *
     * @param $objectType
     * @param string[] $specificProperties
     * @return ObjectProperty[]
     */
    public function getObjectProperties($objectType, $specificProperties = []): array
    {
        $data = $this->getHubSpot()->objectProperties($objectType)->all()->getData();
        if (count($specificProperties)) {
            $data = array_filter($data, function ($item) use ($specificProperties) {
                return in_array($item->name, $specificProperties, true);
            });
        }
        $properties = array_map(function ($item) {
            return new ObjectProperty($item);
        }, $data);
        return $properties;
    }

    /**
     * @param $type
     * @param null $sourceTypeId
     * @return PropertyMapperInterface
     */
    public function getMapper($type, $sourceTypeId = null): PropertyMapperInterface
    {
        return $this->getOrCreateObjectMapper($type, $sourceTypeId, true);
    }

    /**
     * @param HubSpotObjectMapping $mapping
     * @return bool
     * @throws \Exception
     */
    public function saveMapping(HubSpotObjectMapping $mapping): bool
    {
        if ($mapping->id) {
            $record = $this->_createMappingQuery()->where(['=', 'id', $mapping->id])->one();
        } else {
            $record = new HubSpotObjectMappingRecord();
            $record->mapperId = $mapping->mapperId;
        }
        if (!$record) {
            throw new \Exception('Mapping not found');
        }
        $record->property = $mapping->property;
        $record->template = $mapping->template;
        $record->datePublished = $mapping->datePublished;
        $record->save();
        $mapping->id = $record->id;
        $mapping->dateCreated = $record->dateCreated;
        $mapping->dateUpdated = $record->dateUpdated;
        $mapping->uid = $record->uid;
        return true;
    }

    /**
     * @param $id
     * @return HubSpotObjectMapping|null
     */
    public function getMappingById($id) {
        $mapping = $this->_createMappingQuery()->where(['id' => $id])->one();
        if (!$mapping) {
            return null;
        }
        return $this->_createPropertyMappingFromRecord($mapping);
    }

    /**
     * @param HubSpotObjectMapping $mapping
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteMapping(HubSpotObjectMapping $mapping) {
        return HubSpotObjectMappingRecord::findOne($mapping->id)->delete();
    }

    /**
     * @param PropertyMapperInterface $mapper
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function publishMappings(PropertyMapperInterface $mapper): void
    {
        $record = HubSpotObjectMapperRecord::findOne($mapper->id);
        $unpublishedMappings = $record->getUnpublishedMappings()->all();
        $unpublishedMappingProperties = ArrayHelper::getColumn($unpublishedMappings, 'property');
        $publishedMappings = $record->getPublishedMappings()->andWhere([
            'IN',
            'property',
            $unpublishedMappingProperties
        ])->all();

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            foreach ($publishedMappings as $mapping) {
                $mapping->delete();
            }
            /** @var HubSpotObjectMappingRecord $unpublishedMapping */
            foreach ($unpublishedMappings as $unpublishedMapping) {
                $unpublishedMapping->datePublished = DateTimeHelper::currentUTCDateTime();
                $unpublishedMapping->save();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        HubSpotToolbox::$plugin->ecommSettings->saveMappingSettings();
    }

    /**
     * @param HubSpotObjectMapperRecord $mapperRecord
     * @param false $setProperties
     * @return PropertyMapperInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function _createPropertyMapperFromRecord(HubSpotObjectMapperRecord $mapperRecord, $setProperties = false): PropertyMapperInterface
    {

        $config = [
            'type' => $mapperRecord->type,
            'id' => $mapperRecord->id,
            'sourceTypeId' => $mapperRecord->sourceTypeId,
            'dateCreated' => $mapperRecord->dateCreated,
            'dateUpdated' => $mapperRecord->dateUpdated,
            'uid' => $mapperRecord->uid,
        ];
        $mappings = array_map(function ($mapping) {
            return new HubSpotObjectMapping($mapping);
        }, $mapperRecord->mappings);
        $mapper = $this->_createPropertyMapper($config);
        $mappingsByName = ArrayHelper::index($mappings, 'property');
        $mapper->setPropertyMappings($mappingsByName);
        if ($setProperties) {
            $propertiesByName = ArrayHelper::index($this->getObjectProperties($mapper::getHubSpotObjectName()), 'name');
            $mapper->setProperties($propertiesByName);
        }
        return $mapper;
    }

    /**
     * @param $mapperType
     * @return array|PropertyMapperInterface[]
     */
    public function getPropertyMappersByType($mapperType)
    {
        /** @var HubSpotObjectMapperRecord $mapperRecord */
        $mapperRecords = $this->_createMapperQuery($mapperType)->with(['mappings'])->all();

        return array_map(function ($mapper) {
            return $this->_createPropertyMapperFromRecord($mapper);
        }, $mapperRecords);
    }

    /**
     * Gets or creates a property mapper from its type.
     * @param string $mapperType
     * @param null|int $sourceTypeId
     * @param bool $setProperties
     * @return PropertyMapperInterface
     */
    public function getOrCreateObjectMapper(
        string $mapperType,
        $sourceTypeId = null,
        $setProperties = false
    ): PropertyMapperInterface {
        /** @var HubSpotObjectMapperRecord $mapperRecord */
        $mapperRecord = $this->_createMapperQuery($mapperType, $sourceTypeId)->with(['mappings'])->one();
        if (!$mapperRecord) {
            $mapperRecord = new HubSpotObjectMapperRecord([
                'type' => $mapperType,
                'sourceTypeId' => $sourceTypeId
            ]);
            $mapperRecord->save();
        }
        $mapper = $this->_createPropertyMapperFromRecord($mapperRecord, $setProperties);
        return $mapper;
    }

    /**
     * @param HubSpotObjectMappingRecord $propertyMappingRecord
     * @return HubSpotObjectMapping
     */
    protected function _createPropertyMappingFromRecord(HubSpotObjectMappingRecord $propertyMappingRecord): HubSpotObjectMapping
    {
        $propertyMapping = new HubSpotObjectMapping();
        $propertyMapping->property = $propertyMappingRecord->property;
        $propertyMapping->uid = $propertyMappingRecord->uid;
        $propertyMapping->id = $propertyMappingRecord->id;
        $propertyMapping->template = $propertyMappingRecord->template;
        $propertyMapping->dateCreated = $propertyMappingRecord->dateCreated;
        $propertyMapping->datePublished = $propertyMappingRecord->datePublished;
        $propertyMapping->dateUpdated = $propertyMappingRecord->dateUpdated;

        return $propertyMapping;
    }

    /**
     * @param $config
     * @return PropertyMapperInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function _createPropertyMapper($config): PropertyMapperInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var PropertyMapperInterface $feature */
            $propertyMapper = ComponentHelper::createComponent($config, PropertyMapperInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);
            $propertyMapper = null;
        }
        return $propertyMapper;
    }

    /**
     * @return ActiveQuery
     */
    protected function _createMappingQuery(): ActiveQuery
    {
        return HubSpotObjectMappingRecord::find();
    }


    /**
     * @param $mapperType
     * @param null $sourceTypeId
     * @return ActiveQuery
     */
    protected function _createMapperQuery($mapperType, $sourceTypeId = null): ActiveQuery
    {
        $query = HubSpotObjectMapperRecord::find()->where(['=', 'type', $mapperType]);
        if ($sourceTypeId !== null) {
            $query->andWhere(['sourceTypeId' => $sourceTypeId]);
        }
        return $query;
    }

    public function createPropertyMapperPipeline($mapperType): PropertyMapperPipeline
    {
        $mappers = $this->getPropertyMappersByType($mapperType);
        /** @var PropertyMapperPipeline $pipeline */
        $pipeline = \Craft::createObject(PropertyMapperPipeline::class);
        $pipeline->setPropertyMappers($mappers);
        return $pipeline;
    }

    /**
     * Produces an array of all unique, published property names for a mapper type
     *
     * @param string $mapperType
     * @return string[]
     */
    public function getAllUniqueMappedPropertyNames(string $mapperType): array
    {
        return (new Query())->select(['mapping.property'])->distinct(true)->from(['mapping' => HubSpotObjectMappingRecord::tableName()])
            ->leftJoin(['mapper' => HubSpotObjectMapperRecord::tableName()], '[[mapper.id]] = [[mapping.mapperId]]')
            ->where(['mapper.type' => $mapperType])
            ->andWhere(['NOT', ['mapping.datePublished' => null]])
            ->column();
    }
}
