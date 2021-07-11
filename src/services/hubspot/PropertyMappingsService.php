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
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use craft\helpers\DateTimeHelper;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectMapping;
use venveo\hubspottoolbox\propertymappers\MultiTypePropertyMapper;
use venveo\hubspottoolbox\propertymappers\PropertyMapperInterface;
use venveo\hubspottoolbox\propertymappers\PropertyMapperPipeline;
use venveo\hubspottoolbox\records\HubSpotObjectMapper as HubSpotObjectMapperRecord;
use venveo\hubspottoolbox\records\HubSpotObjectMapping as HubSpotObjectMappingRecord;
use venveo\hubspottoolbox\typeprocessors\DateTimeProcessor;
use venveo\hubspottoolbox\typeprocessors\NumberProcessor;
use venveo\hubspottoolbox\typeprocessors\StringProcessor;
use venveo\hubspottoolbox\typeprocessors\TypeProcessorInterface;

/**
 * Class PropertyMappingsService
 * @package venveo\hubspottoolbox\services\hubspot
 */
class PropertyMappingsService extends Component
{
    /**
     * @var MemoizableArray|null
     * @see $_allMappings()
     */
    private $_allMappings;

    /**
     * @var MemoizableArray|null
     * @see $_allMappers()
     */
    private $_allMappers;

    /**
     * @param HubSpotObjectMapping $mapping
     * @return bool
     * @throws \Exception
     */
    public function saveMapping(HubSpotObjectMapping $mapping): bool
    {
        if ($mapping->id) {
            $record = HubSpotObjectMappingRecord::findOne($mapping->id);
        } else {
            $record = new HubSpotObjectMappingRecord();
        }
        $record->mapperId = $mapping->mapperId;
        $record->propertyId = $mapping->propertyId;
        $record->template = $mapping->template;
        $record->datePublished = $mapping->datePublished;
        if ($record->save()) {
            $this->_allMappings = null;
            $mapping->id = $record->id;
            return true;
        }
        return false;
    }

    /**
     * @param PropertyMapperInterface $mapper
     * @return bool
     */
    public function saveMapper(PropertyMapperInterface $mapper): bool
    {
        if ($mapper->id) {
            $record = HubSpotObjectMapperRecord::findOne($mapper->id);
        } else {
            $record = new HubSpotObjectMapperRecord();
        }
        if ($mapper instanceof MultiTypePropertyMapper) {
            $record->sourceTypeId = $mapper->sourceTypeId;
        }
        $record->type = $mapper->type;
        if ($record->save()) {
            $this->_allMappers = null;
            $mapper->id = $record->id;
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return HubSpotObjectMapping|null
     */
    public function getMappingById($id): HubSpotObjectMapping
    {
        return $this->_allMappings()->firstWhere('id', $id);
    }

    /**
     * @param HubSpotObjectMapping $mapping
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteMapping(HubSpotObjectMapping $mapping)
    {
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
        $mappingsForMapper = $this->_allMappings()->where('mapperId', $mapper->id);
        $unpublishedMappings = $mappingsForMapper->where('datePublished', null)->all();
        $unpublishedMappingPropertyIds = ArrayHelper::getColumn($unpublishedMappings, 'propertyId');
        $publishedMappings = $mappingsForMapper->where('datePublished')->whereIn('propertyId', $unpublishedMappingPropertyIds)->all();

        if (count($unpublishedMappings)) {
            $transaction = Craft::$app->getDb()->beginTransaction();
            try {
                foreach ($publishedMappings as $mapping) {
                    $this->deleteMapping($mapping);
                }
                /** @var HubSpotObjectMapping $unpublishedMapping */
                foreach ($unpublishedMappings as $unpublishedMapping) {
                    $unpublishedMapping->datePublished = DateTimeHelper::currentUTCDateTime();
                    $this->saveMapping($unpublishedMapping);
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        HubSpotToolbox::$plugin->ecommSettings->saveMappingSettings();
    }


    /**
     * @param $mapperType
     * @return PropertyMapperInterface[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getPropertyMappersByType($mapperType): array
    {
        return $this->_allMappers()->where('type', $mapperType)->all();
    }

    /**
     * Gets or creates a property mapper from its type.
     * @param string $mapperType
     * @param null|int $sourceTypeId
     * @return PropertyMapperInterface
     */
    public function getOrCreateObjectMapper(
        string $mapperType,
        $sourceTypeId = null
    ): PropertyMapperInterface {
        /** @var HubSpotObjectMapperRecord $mapperRecord */
        $mapper = $this->_allMappers()->where('type', $mapperType)->firstWhere('sourceTypeId', $sourceTypeId);
        if ($mapper) {
            return $mapper;
        }
        $mapper = $this->_createPropertyMapper([
            'type' => $mapperType,
            'sourceTypeId' => $sourceTypeId
        ]);
        $this->saveMapper($mapper);
        return $mapper;
    }

    /**
     * @param $data
     * @return HubSpotObjectMapping
     */
    protected function _createPropertyMapping($data): HubSpotObjectMapping
    {
        return new HubSpotObjectMapping($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPropertyMapperById($id)
    {
        return $this->_allMappers()->firstWhere('id', $id);
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
            /** @var PropertyMapperInterface $propertyMapper */
            $propertyMapper = ComponentHelper::createComponent($config, PropertyMapperInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);
            $propertyMapper = null;
        }
        return $propertyMapper;
    }


    public function getMappingsForMapper($mapperId): array
    {
        return $this->_allMappings()->where('mapperId', $mapperId)->all();
    }

    /**
     * @param $mapperType
     * @return PropertyMapperPipeline
     * @throws \yii\base\InvalidConfigException
     */
    public function createPropertyMapperPipeline($mapperType): PropertyMapperPipeline
    {
        $mappers = $this->getPropertyMappersByType($mapperType);
        /** @var PropertyMapperPipeline $pipeline */
        $pipeline = \Craft::createObject(PropertyMapperPipeline::class);
        $pipeline->setPropertyMappers($mappers);
        return $pipeline;
    }


    /**
     * Gets all data type processors
     *
     * @return TypeProcessorInterface[]
     */
    public function getTypeProcessors(): array
    {
        $processors = [
            DateTimeProcessor::class,
            NumberProcessor::class,
            StringProcessor::class
        ];
        $indexedProcessors = [];
        /** @var TypeProcessorInterface $processor */
        foreach ($processors as $processor) {
            $type = $processor::getHandle();
            $indexedProcessors[$type] = $processor;
        }
        return $indexedProcessors;
    }

    /**
     * @return Query
     */
    protected function _createMappingQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'mapperId',
                'propertyId',
                'template',
                'datePublished',
            ])
            ->from([HubSpotObjectMappingRecord::tableName()]);
    }

    /**
     * @param $mapperType
     * @param null $sourceTypeId
     * @return Query
     */
    protected function _createMapperQuery($mapperType = null, $sourceTypeId = null): Query
    {
        $query = (new Query())
            ->select([
                'id',
                'type',
                'sourceTypeId'
            ])
            ->from([HubSpotObjectMapperRecord::tableName()]);

        if ($mapperType !== null) {
            $query->andWhere(['=', 'type', $mapperType]);
        }
        if ($sourceTypeId !== null) {
            $query->andWhere(['sourceTypeId' => $sourceTypeId]);
        }

        return $query;
    }

    /**
     * @return MemoizableArray
     * @throws \yii\base\InvalidConfigException
     */
    private function _allMappers(): MemoizableArray
    {
        if (!isset($this->_allMappers)) {
            $mappers = array_map(function ($row) {
                return $this->_createPropertyMapper($row);
            }, $this->_createMapperQuery()->all());
            $this->_allMappers = new MemoizableArray($mappers);
        }

        return $this->_allMappers;
    }

    /**
     * @return MemoizableArray
     */
    private function _allMappings(): MemoizableArray
    {
        if (!isset($this->_allMappings)) {
            $mappings = array_map(function ($row) {
                return new HubSpotObjectMapping($row);
            }, $this->_createMappingQuery()->all());
            $this->_allMappings = new MemoizableArray($mappings);
        }

        return $this->_allMappings;
    }
}
