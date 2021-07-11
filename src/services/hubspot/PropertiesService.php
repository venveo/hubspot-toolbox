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

use craft\base\Component;
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\helpers\ArrayHelper;
use venveo\hubspottoolbox\entities\ObjectProperty;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\models\HubSpotObjectProperty;
use venveo\hubspottoolbox\propertymappers\PropertyMapperInterface;
use venveo\hubspottoolbox\records\HubSpotObjectProperty as HubSpotObjectPropertyRecord;
use venveo\hubspottoolbox\traits\HubSpotTokenAuthorization;

class PropertiesService extends Component
{
    use HubSpotTokenAuthorization;

    /**
     * @var MemoizableArray|null
     * @see $_properties()
     */
    private $_properties;

    /**
     * @var MemoizableArray|null
     * @see $_propertiesFromApi()
     */
    private $_propertiesFromApi;

    /**
     * Gets all properties for a type of object from HubSpot
     *
     * @param $objectType
     * @param string[] $specificProperties
     * @return ObjectProperty[]
     */
    public function getPropertiesFromApi($objectType, $specificProperties = []): array
    {
        $data = $this->_propertiesFromApi($objectType);
        if (count($specificProperties)) {
            return ArrayHelper::index($data->whereIn('name', $specificProperties)->all(), 'name');
        }

        return ArrayHelper::index($data->all(), 'name');
    }

    /**
     * @param string $objectType
     * @param string $propertyName
     * @return ObjectProperty|null
     */
    public function getPropertyFromApi(string $objectType, string $propertyName): ?ObjectProperty
    {
        return $this->_propertiesFromApi($objectType)->firstWhere('name', $propertyName);
    }

    /**
     * @return Query
     */
    protected function _createPropertyQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'objectType',
                'name',
                'dataType',
            ])
            ->from([HubSpotObjectPropertyRecord::tableName()]);
    }

    /**
     * @param string $objectType
     * @param int $id
     * @return HubSpotObjectProperty|null
     */
    public function getPropertyById(string $objectType, int $id): ?HubSpotObjectProperty
    {
        return $this->_properties($objectType)->firstWhere('id', $id);
    }

    /**
     * @param $objectType
     * @param $propertyName
     * @param $dataType
     * @return HubSpotObjectProperty
     */
    public function getOrCreateProperty($objectType, $propertyName, $dataType): HubSpotObjectProperty
    {
        $propertyData = $this->_createPropertyQuery()->andWhere([
            'objectType' => $objectType,
            'name' => $propertyName,
            'dataType' => $dataType
        ])->one();
        if ($propertyData) {
            return new HubSpotObjectProperty($propertyData);
        }

        $propertyRecord = new HubSpotObjectPropertyRecord([
            'objectType' => $objectType,
            'name' => $propertyName,
            'dataType' => $dataType
        ]);
        $propertyRecord->save();
        $this->_properties = null;

        return new HubSpotObjectProperty([
            'id' => $propertyRecord->id,
            'objectType' => $propertyRecord->objectType,
            'name' => $propertyRecord->name,
            'dataType' => $propertyRecord->dataType
        ]);
    }

    /**
     * @param PropertyMapperInterface $mapper
     * @return array
     */
    public function getAllPropertiesForMapper(PropertyMapperInterface $mapper): array
    {
        return ArrayHelper::index($this->_properties($mapper::getHubSpotObjectType())->all(), 'name');
    }

    /**
     * @param string $mapperType
     * @return HubSpotObjectProperty[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getAllPropertiesForMapperType(string $mapperType): array
    {
        $mappers = HubSpotToolbox::$plugin->propertyMappings->getPropertyMappersByType($mapperType);
        $properties = [];
        foreach ($mappers as $mapper) {
            /** @var HubSpotObjectProperty $property */
            foreach ($mapper->getProperties() as $property) {
                if (!isset($properties[$property->name])) {
                    $properties[$property->name] = $property;
                }
            }
        }
        return $properties;
    }

    /**
     * Returns a memoizable array of all stored properties.
     *
     * @param string $objectType
     * @return MemoizableArray
     */
    private function _properties(string $objectType): MemoizableArray
    {
        if (!isset($this->_properties[$objectType])) {
            $properties = [];
            foreach ($this->_createPropertyQuery()->where(['objectType' => $objectType])->all() as $result) {
                $properties[] = new HubSpotObjectProperty($result);
            }
            $this->_properties[$objectType] = new MemoizableArray($properties);
        }

        return $this->_properties[$objectType];
    }

    /**
     * @param string $objectType
     * @return MemoizableArray
     * @throws \Exception
     */
    private function _propertiesFromApi(string $objectType): MemoizableArray
    {
        if (!isset($this->_propertiesFromApi[$objectType])) {
            $data = $this->getHubSpot()->objectProperties($objectType)->all()->getData();
            $properties = array_map(function ($item) {
                return new ObjectProperty($item);
            }, $data);
            $this->_propertiesFromApi[$objectType] = new MemoizableArray($properties);
        }

        return $this->_propertiesFromApi[$objectType];
    }

}
