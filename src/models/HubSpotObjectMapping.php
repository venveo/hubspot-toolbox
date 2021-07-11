<?php

namespace venveo\hubspottoolbox\models;

use craft\base\Model;
use venveo\hubspottoolbox\entities\ObjectProperty;
use venveo\hubspottoolbox\HubSpotToolbox;
use venveo\hubspottoolbox\propertymappers\PropertyMapperInterface;

/**
 *
 * @property-read \venveo\hubspottoolbox\models\HubSpotObjectProperty $property
 * @property-read \venveo\hubspottoolbox\propertymappers\PropertyMapperInterface $mapper
 * @property-read null|\venveo\hubspottoolbox\entities\ObjectProperty $propertyFromApi
 */
class HubSpotObjectMapping extends Model
{
    public $id;
    public int $mapperId;
    public int $propertyId;
    public $template;
    public $datePublished;

    public $uid;
    public $dateCreated;
    public $dateUpdated;

    protected $objectProperty;

    /**
     * @return ObjectProperty
     */
    public function getPropertyFromApi(): ?ObjectProperty
    {
        return HubSpotToolbox::$plugin->properties->getPropertyFromApi($this->getMapper()::getHubSpotObjectType(), $this->getProperty()->name);
    }

    /**
     * @return HubSpotObjectProperty
     */
    public function getProperty(): HubSpotObjectProperty
    {
        return HubSpotToolbox::$plugin->properties->getPropertyById($this->getMapper()::getHubSpotObjectType(), $this->propertyId);
    }

    /**
     * @return PropertyMapperInterface
     */
    public function getMapper(): PropertyMapperInterface
    {
        return HubSpotToolbox::$plugin->propertyMappings->getPropertyMapperById($this->mapperId);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [
            'propertyFromApi',
            'property'
        ];
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['mapperId', 'dataType', 'property'], 'required'];
        return $rules;
    }
}
